<?php
declare(strict_types=1);

/**
 * Endpoint público: devolve em JSON os horários com vagas para uma data (GET ?data=AAAA-MM-DD).
 */
require_once __DIR__ . '/funcoes.php';

$data = (string) ($_GET['data'] ?? '');
$obj  = DateTime::createFromFormat('Y-m-d', $data);
if (!$obj || $obj->format('Y-m-d') !== $data) {
    json_resposta(['horarios' => [], 'erro' => 'Data inválida.'], 422);
}

$hoje = new DateTime('today');
if ($obj < $hoje || $obj > (new DateTime('today'))->modify('+' . DIAS_ANTECEDENCIA_MAX . ' days')) {
    json_resposta(['horarios' => []]);
}

$limite = (new DateTime())->modify('+60 minutes');
$lista  = [];

try {
    $pdo = db();
    foreach (gerar_horarios($data) as $hora) {
        if (DateTime::createFromFormat('Y-m-d H:i', "$data $hora") < $limite) {
            continue;
        }
        $vagas = vagas_restantes($pdo, $data, $hora);
        if ($vagas > 0) {
            $lista[] = ['hora' => $hora, 'vagas' => $vagas];
        }
    }
} catch (Throwable $ex) {
    error_log('Erro ao consultar horários: ' . $ex->getMessage());
    json_resposta(['horarios' => [], 'erro' => 'Falha ao consultar horários.'], 500);
}

json_resposta(['horarios' => $lista]);
