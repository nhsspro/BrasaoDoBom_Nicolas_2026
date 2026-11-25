<?php
declare(strict_types=1);

/**
 * Endpoint público: recebe o formulário de reserva (POST) e responde em JSON.
 */
require_once __DIR__ . '/funcoes.php';
iniciar_sessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_resposta(['ok' => false, 'erros' => ['Método não permitido.']], 405);
}

if (!csrf_valido()) {
    json_resposta(['ok' => false, 'erros' => ['Sessão expirada. Recarregue a página e tente novamente.']], 403);
}

// Honeypot: campo escondido que só robôs preenchem. Finge sucesso e descarta.
if (!empty($_POST['site'])) {
    json_resposta(['ok' => true, 'mensagem' => 'Reserva recebida.']);
}

// Limite simples por sessão: no máximo 5 tentativas por hora.
$agora = time();
$_SESSION['tentativas_reserva'] = array_filter(
    $_SESSION['tentativas_reserva'] ?? [],
    fn (int $t): bool => $t > $agora - 3600
);
if (count($_SESSION['tentativas_reserva']) >= 5) {
    json_resposta(['ok' => false, 'erros' => ['Muitas tentativas. Tente novamente mais tarde ou ligue para o restaurante.']], 429);
}
$_SESSION['tentativas_reserva'][] = $agora;

[$dados, $erros] = validar_reserva($_POST);
if ($erros) {
    json_resposta(['ok' => false, 'erros' => $erros], 422);
}

try {
    [$id, $erros] = salvar_reserva(db(), $dados);
} catch (Throwable $ex) {
    error_log('Erro ao salvar reserva: ' . $ex->getMessage());
    json_resposta(['ok' => false, 'erros' => ['Não foi possível registrar a reserva agora. Tente novamente em instantes.']], 500);
}

if ($erros) {
    json_resposta(['ok' => false, 'erros' => $erros], 409);
}

json_resposta([
    'ok'       => true,
    'mensagem' => sprintf(
        'Reserva nº %d recebida para %s às %s (%d pessoas). Ela está pendente e o restaurante confirmará pelo telefone informado.',
        $id, formatar_data($dados['data']), $dados['horario'], $dados['pessoas']
    ),
]);
