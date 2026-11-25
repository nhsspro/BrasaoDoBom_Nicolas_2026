<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/funcoes.php';
exigir_login();
$pdo = db();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$erros = [];
$f = ['nome' => '', 'telefone' => '', 'email' => '', 'data' => date('Y-m-d'), 'horario' => '',
      'pessoas' => '2', 'observacoes' => '', 'status' => 'confirmada'];

if ($id !== null) {
    $stmt = $pdo->prepare('SELECT * FROM reservas WHERE id = ?');
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r) {
        flash('danger', 'Reserva não encontrada.');
        redirecionar('reservas.php');
    }
    $f = ['nome' => $r['nome'], 'telefone' => $r['telefone'], 'email' => (string) $r['email'],
          'data' => $r['data_reserva'], 'horario' => substr($r['horario'], 0, 5), 'pessoas' => (string) $r['pessoas'],
          'observacoes' => (string) $r['observacoes'], 'status' => $r['status']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $erros[] = 'Sessão expirada. Tente novamente.';
        $f = array_merge($f, array_intersect_key($_POST, $f));
    } else {
        [$dados, $erros] = validar_reserva($_POST, true);   // modo admin
        $f = array_merge($f, array_intersect_key($_POST, $f));
        if (!$erros) {
            [$salvoId, $erros] = salvar_reserva($pdo, $dados, $id);
            if (!$erros) {
                flash('success', $id ? "Reserva #$id atualizada." : "Reserva #$salvoId criada.");
                redirecionar('reservas.php');
            }
        }
    }
}

$titulo = $id ? "Editar reserva #$id" : 'Nova reserva';
require __DIR__ . '/_topo.php';
$horarios = gerar_horarios($f['data']);
?>
<h1 class="h3 mb-3"><?= e($titulo) ?></h1>
<?php if ($erros): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erros as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="row g-3" style="max-width: 760px;">
  <?= csrf_campo() ?>
  <div class="col-md-8"><label class="form-label" for="nome">Nome</label>
    <input class="form-control" id="nome" name="nome" maxlength="100" value="<?= e($f['nome']) ?>" required></div>
  <div class="col-md-4"><label class="form-label" for="telefone">Telefone (com DDD)</label>
    <input class="form-control" id="telefone" name="telefone" maxlength="15" value="<?= e($f['telefone']) ?>" required></div>
  <div class="col-12"><label class="form-label" for="email">E-mail (opcional)</label>
    <input type="email" class="form-control" id="email" name="email" maxlength="150" value="<?= e($f['email']) ?>"></div>
  <div class="col-md-3"><label class="form-label" for="data">Data</label>
    <input type="date" class="form-control" id="data" name="data" value="<?= e($f['data']) ?>" required></div>
  <div class="col-md-3"><label class="form-label" for="horario">Horário</label>
    <select class="form-select" id="horario" name="horario" required>
      <?php foreach ($horarios as $h): ?>
        <option value="<?= e($h) ?>" <?= $f['horario'] === $h ? 'selected' : '' ?>><?= e($h) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="form-text">Mudou a data? Salve e reabra para atualizar os horários.</div></div>
  <div class="col-md-3"><label class="form-label" for="pessoas">Pessoas</label>
    <input type="number" class="form-control" id="pessoas" name="pessoas" min="1" max="<?= MAX_PESSOAS_RESERVA ?>" value="<?= e($f['pessoas']) ?>" required></div>
  <div class="col-md-3"><label class="form-label" for="status">Status</label>
    <select class="form-select" id="status" name="status">
      <?php foreach (STATUS_RESERVA as $s): ?>
        <option value="<?= $s ?>" <?= $f['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select></div>
  <div class="col-12"><label class="form-label" for="observacoes">Observações</label>
    <textarea class="form-control" id="observacoes" name="observacoes" rows="2" maxlength="300"><?= e($f['observacoes']) ?></textarea></div>
  <div class="col-12">
    <button class="btn btn-gold">Salvar</button>
    <a href="reservas.php" class="btn btn-link text-secondary">Voltar</a>
  </div>
</form>
<?php require __DIR__ . '/_rodape.php'; ?>
