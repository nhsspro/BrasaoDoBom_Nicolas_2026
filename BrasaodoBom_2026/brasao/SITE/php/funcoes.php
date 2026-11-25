<?php
require_once __DIR__ . '/conexao.php';

// Funções comuns do site e do painel.
function iniciar_sessao(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
        session_start();
    }
}
function e(?string $texto): string { return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8'); }
function tamanho_texto(string $texto): int { return function_exists('mb_strlen') ? mb_strlen($texto) : strlen($texto); }
function redirecionar(string $url): never { header('Location: '.$url); exit; }
function json_resposta(array $dados, int $status = 200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE); exit;
}

// Proteção simples para formulários: token CSRF armazenado na sessão.
function csrf_token(): string {
    iniciar_sessao();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_campo(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
function csrf_valido(): bool {
    iniciar_sessao(); $token = $_POST['csrf'] ?? '';
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
function flash(string $tipo, string $mensagem): void { iniciar_sessao(); $_SESSION['flash'][] = ['tipo'=>$tipo, 'msg'=>$mensagem]; }
function flash_obter(): array { iniciar_sessao(); $lista = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $lista; }

// Login do administrador.
function usuario_logado(): ?array { iniciar_sessao(); return $_SESSION['usuario'] ?? null; }
function exigir_login(): void { if (!usuario_logado()) redirecionar('login.php'); }
function autenticar(string $email, string $senha): ?array {
    $stmt = db()->prepare('SELECT id, nome, email, senha_hash FROM usuarios WHERE email = ?');
    $stmt->execute([$email]); $usuario = $stmt->fetch();
    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) return null;
    unset($usuario['senha_hash']); return $usuario;
}

// Horários: durante a semana abre 11h; sábado e domingo abre 10h. Intervalos de 30 min.
const STATUS_RESERVA = ['pendente', 'confirmada', 'cancelada'];
function horario_funcionamento(DateTimeInterface $data): array {
    return ((int)$data->format('N') >= 6) ? ['10:00','23:00'] : ['11:00','23:00'];
}
function gerar_horarios(string $data): array {
    $dia = DateTime::createFromFormat('Y-m-d', $data);
    if (!$dia) return [];
    [$abre, $fecha] = horario_funcionamento($dia);
    $hora = DateTime::createFromFormat('Y-m-d H:i', "$data $abre");
    $fim = DateTime::createFromFormat('Y-m-d H:i', "$data $fecha")->modify('-90 minutes');
    $lista = [];
    while ($hora <= $fim) { $lista[] = $hora->format('H:i'); $hora->modify('+30 minutes'); }
    return $lista;
}
function vagas_restantes(PDO $pdo, string $data, string $horario, ?int $ignorarId = null, bool $travar = false): int {
    $sql = "SELECT COALESCE(SUM(pessoas),0) FROM reservas WHERE data_reserva=? AND horario=? AND status <> 'cancelada'";
    $params = [$data, $horario.':00'];
    if ($ignorarId) { $sql .= ' AND id <> ?'; $params[] = $ignorarId; }
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    return CAPACIDADE_POR_HORARIO - (int)$stmt->fetchColumn();
}
function validar_reserva(array $entrada, bool $admin = false): array {
    $dados = [
        'nome'=>trim((string)($entrada['nome'] ?? '')),
        'telefone'=>preg_replace('/\\D/', '', (string)($entrada['telefone'] ?? '')),
        'email'=>trim((string)($entrada['email'] ?? '')),
        'data'=>trim((string)($entrada['data'] ?? '')),
        'horario'=>trim((string)($entrada['horario'] ?? '')),
        'pessoas'=>(int)($entrada['pessoas'] ?? 0),
        'observacoes'=>trim((string)($entrada['observacoes'] ?? '')),
        'status'=>'pendente'
    ];
    $erros = [];
    if (tamanho_texto($dados['nome']) < 3 || tamanho_texto($dados['nome']) > 100) $erros[] = 'Nome deve ter entre 3 e 100 caracteres.';
    if (!preg_match('/^\\d{10,11}$/', $dados['telefone'])) $erros[] = 'Telefone inválido. Informe DDD e número.';
    if ($dados['email'] !== '' && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if (tamanho_texto($dados['observacoes']) > 300) $erros[] = 'Observações limitadas a 300 caracteres.';
    if ($dados['pessoas'] < 1 || $dados['pessoas'] > MAX_PESSOAS_RESERVA) $erros[] = 'A reserva deve ter de 1 a '.MAX_PESSOAS_RESERVA.' pessoas.';

    $dia = DateTime::createFromFormat('Y-m-d', $dados['data']);
    if (!$dia || $dia->format('Y-m-d') !== $dados['data']) $erros[] = 'Data inválida.';
    elseif (!$admin) {
        if ($dia < new DateTime('today')) $erros[] = 'A data não pode estar no passado.';
        if ($dia > (new DateTime('today'))->modify('+'.DIAS_ANTECEDENCIA_MAX.' days')) $erros[] = 'A reserva pode ser feita com até '.DIAS_ANTECEDENCIA_MAX.' dias de antecedência.';
    }
    if ($dia && $dia->format('Y-m-d') === $dados['data']) {
        if (!in_array($dados['horario'], gerar_horarios($dados['data']), true)) $erros[] = 'Horário inválido para a data.';
        elseif (!$admin && DateTime::createFromFormat('Y-m-d H:i', $dados['data'].' '.$dados['horario']) < (new DateTime())->modify('+60 minutes')) $erros[] = 'Reserve com pelo menos 1 hora de antecedência.';
    } else $erros[] = 'Escolha um horário válido.';
    if ($admin) {
        $status = (string)($entrada['status'] ?? 'pendente');
        if (!in_array($status, STATUS_RESERVA, true)) $erros[] = 'Status inválido.'; else $dados['status'] = $status;
    }
    return [$dados, $erros];
}
// Salva a reserva. A capacidade é conferida antes de inserir ou atualizar.
function salvar_reserva(PDO $pdo, array $dados, ?int $id = null): array {
    if ($dados['status'] !== 'cancelada') {
        $vagas = vagas_restantes($pdo, $dados['data'], $dados['horario'], $id);
        if ($dados['pessoas'] > $vagas) return [null, ['Só há '.$vagas.' vaga(s) disponíveis nesse horário.']];
    }
    $valores = [$dados['nome'], $dados['telefone'], $dados['email'] ?: null, $dados['data'], $dados['horario'].':00', $dados['pessoas'], $dados['observacoes'] ?: null, $dados['status']];
    if ($id === null) {
        $sql = 'INSERT INTO reservas (nome,telefone,email,data_reserva,horario,pessoas,observacoes,status) VALUES (?,?,?,?,?,?,?,?)';
        $pdo->prepare($sql)->execute($valores); $id = (int)$pdo->lastInsertId();
    } else {
        $sql = 'UPDATE reservas SET nome=?,telefone=?,email=?,data_reserva=?,horario=?,pessoas=?,observacoes=?,status=? WHERE id=?';
        $valores[] = $id; $pdo->prepare($sql)->execute($valores);
    }
    return [$id, []];
}
function formatar_telefone(string $tel): string {
    if (strlen($tel) === 11) return sprintf('(%s) %s-%s', substr($tel,0,2), substr($tel,2,5), substr($tel,7));
    return sprintf('(%s) %s-%s', substr($tel,0,2), substr($tel,2,4), substr($tel,6));
}
function formatar_data(string $data): string { $d = DateTime::createFromFormat('Y-m-d', $data); return $d ? $d->format('d/m/Y') : $data; }
