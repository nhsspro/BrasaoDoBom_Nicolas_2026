<?php
declare(strict_types=1);
$titulo = 'Painel';
require __DIR__ . '/_topo.php';

$pdo = db();
$hoje = date('Y-m-d');
$em7  = date('Y-m-d', strtotime('+7 days'));

$pendentes = (int) $pdo->query("SELECT COUNT(*) FROM reservas WHERE status = 'pendente' AND data_reserva >= CURDATE()")->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) AS reservas, COALESCE(SUM(pessoas),0) AS pessoas
                       FROM reservas WHERE data_reserva = ? AND status <> 'cancelada'");
$stmt->execute([$hoje]);
$hojeInfo = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE data_reserva BETWEEN ? AND ? AND status = 'confirmada'");
$stmt->execute([$hoje, $em7]);
$proximas = (int) $stmt->fetchColumn();

$lista = $pdo->query("SELECT * FROM reservas WHERE status = 'pendente' AND data_reserva >= CURDATE()
                      ORDER BY data_reserva, horario LIMIT 10")->fetchAll();
?>
<h1 class="h3 mb-4">Painel</h1>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card card-body"><div class="indicador text-warning"><?= $pendentes ?></div><div class="text-secondary">reservas aguardando confirmação</div></div></div>
  <div class="col-md-4"><div class="card card-body"><div class="indicador"><?= (int) $hojeInfo['reservas'] ?> <small class="fs-6 text-secondary">(<?= (int) $hojeInfo['pessoas'] ?> pessoas)</small></div><div class="text-secondary">reservas para hoje</div></div></div>
  <div class="col-md-4"><div class="card card-body"><div class="indicador text-success"><?= $proximas ?></div><div class="text-secondary">confirmadas nos próximos 7 dias</div></div></div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="h5 mb-0">Pendentes mais próximas</h2>
  <a href="reservas.php" class="btn btn-sm btn-outline-light">Ver todas</a>
</div>

<div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Data</th><th>Hora</th><th>Nome</th><th>Telefone</th><th>Pessoas</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($lista as $r): ?>
      <tr>
        <td><?= e(formatar_data($r['data_reserva'])) ?></td>
        <td><?= e(substr($r['horario'], 0, 5)) ?></td>
        <td><?= e($r['nome']) ?></td>
        <td><?= e(formatar_telefone($r['telefone'])) ?></td>
        <td><?= (int) $r['pessoas'] ?></td>
        <td class="text-end tabela-acoes">
          <?php foreach (['confirmada' => ['Confirmar', 'success'], 'cancelada' => ['Cancelar', 'outline-danger']] as $novo => [$rotulo, $cor]): ?>
            <form method="post" action="reservas.php">
              <?= csrf_campo() ?>
              <input type="hidden" name="acao" value="status">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <input type="hidden" name="status" value="<?= $novo ?>">
              <input type="hidden" name="voltar" value="painel">
              <button class="btn btn-sm btn-<?= $cor ?>"><?= $rotulo ?></button>
            </form>
          <?php endforeach; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?>
      <tr><td colspan="6" class="text-center text-secondary py-4">Nenhuma reserva pendente.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
