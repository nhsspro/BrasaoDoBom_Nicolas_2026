<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/funcoes.php';
exigir_login();
$pdo = db();

// Filtros (GET) preservados após cada ação
$filtroData   = (string) ($_GET['data'] ?? '');
$filtroStatus = (string) ($_GET['status'] ?? '');
if ($filtroData !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtroData)) {
    $filtroData = '';
}
if (!in_array($filtroStatus, STATUS_RESERVA, true)) {
    $filtroStatus = '';
}
$consulta = http_build_query(array_filter(['data' => $filtroData, 'status' => $filtroStatus]));

/* ---------- Ações (POST): alterar status e excluir ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destino = (($_POST['voltar'] ?? '') === 'painel') ? 'index.php' : 'reservas.php' . ($consulta ? "?$consulta" : '');

    if (!csrf_valido()) {
        flash('danger', 'Sessão expirada. Tente novamente.');
        redirecionar($destino);
    }

    $id   = (int) ($_POST['id'] ?? 0);
    $acao = (string) ($_POST['acao'] ?? '');
    $stmt = $pdo->prepare('SELECT * FROM reservas WHERE id = ?');
    $stmt->execute([$id]);
    $r = $stmt->fetch();

    if (!$r) {
        flash('danger', 'Reserva não encontrada.');
    } elseif ($acao === 'status') {
        $novo = (string) ($_POST['status'] ?? '');
        if (!in_array($novo, STATUS_RESERVA, true)) {
            flash('danger', 'Status inválido.');
        } else {
            // Reutiliza salvar_reserva: se uma reserva cancelada for reativada, a capacidade é conferida de novo.
            $dados = [
                'nome' => $r['nome'], 'telefone' => $r['telefone'], 'email' => (string) $r['email'],
                'data' => $r['data_reserva'], 'horario' => substr($r['horario'], 0, 5),
                'pessoas' => (int) $r['pessoas'], 'observacoes' => (string) $r['observacoes'], 'status' => $novo,
            ];
            [, $erros] = salvar_reserva($pdo, $dados, $id);
            $erros ? flash('danger', implode(' ', $erros)) : flash('success', "Reserva #$id marcada como $novo.");
        }
    } elseif ($acao === 'excluir') {
        $pdo->prepare('DELETE FROM reservas WHERE id = ?')->execute([$id]);
        flash('success', "Reserva #$id excluída.");
    }
    redirecionar($destino);
}

/* ---------- Listagem (GET) ---------- */
$sql = 'SELECT * FROM reservas WHERE 1=1';
$params = [];
if ($filtroData !== '') {
    $sql .= ' AND data_reserva = ?';
    $params[] = $filtroData;
}
if ($filtroStatus !== '') {
    $sql .= ' AND status = ?';
    $params[] = $filtroStatus;
}
$sql .= ' ORDER BY data_reserva DESC, horario DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

$titulo = 'Reservas';
require __DIR__ . '/_topo.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0">Reservas</h1>
  <a href="reserva_form.php" class="btn btn-gold">Nova reserva</a>
</div>

<form method="get" class="row g-2 mb-3">
  <div class="col-sm-4 col-md-3"><input type="date" name="data" value="<?= e($filtroData) ?>" class="form-control" aria-label="Filtrar por data"></div>
  <div class="col-sm-4 col-md-3">
    <select name="status" class="form-select" aria-label="Filtrar por status">
      <option value="">Todos os status</option>
      <?php foreach (STATUS_RESERVA as $s): ?>
        <option value="<?= $s ?>" <?= $filtroStatus === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto"><button class="btn btn-outline-light">Filtrar</button> <a href="reservas.php" class="btn btn-link text-secondary">Limpar</a></div>
</form>

<div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>#</th><th>Data</th><th>Hora</th><th>Nome</th><th>Telefone</th><th>Pessoas</th><th>Obs.</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($reservas as $r): ?>
      <tr>
        <td><?= (int) $r['id'] ?></td>
        <td><?= e(formatar_data($r['data_reserva'])) ?></td>
        <td><?= e(substr($r['horario'], 0, 5)) ?></td>
        <td><?= e($r['nome']) ?></td>
        <td><?= e(formatar_telefone($r['telefone'])) ?></td>
        <td><?= (int) $r['pessoas'] ?></td>
        <td class="small text-secondary" style="max-width:200px;"><?= e($r['observacoes']) ?></td>
        <td><?= selo_status($r['status']) ?></td>
        <td class="text-end text-nowrap tabela-acoes">
          <?php if ($r['status'] !== 'confirmada'): ?>
            <form method="post" action="reservas.php?<?= e($consulta) ?>">
              <?= csrf_campo() ?><input type="hidden" name="acao" value="status"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="status" value="confirmada">
              <button class="btn btn-sm btn-success">Confirmar</button>
            </form>
          <?php endif; ?>
          <?php if ($r['status'] !== 'cancelada'): ?>
            <form method="post" action="reservas.php?<?= e($consulta) ?>">
              <?= csrf_campo() ?><input type="hidden" name="acao" value="status"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="status" value="cancelada">
              <button class="btn btn-sm btn-outline-warning">Cancelar</button>
            </form>
          <?php endif; ?>
          <a class="btn btn-sm btn-outline-light" href="reserva_form.php?id=<?= (int) $r['id'] ?>">Editar</a>
          <form method="post" action="reservas.php?<?= e($consulta) ?>" onsubmit="return confirm('Excluir a reserva #<?= (int) $r['id'] ?> definitivamente?');">
            <?= csrf_campo() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$reservas): ?>
      <tr><td colspan="9" class="text-center text-secondary py-4">Nenhuma reserva encontrada.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
