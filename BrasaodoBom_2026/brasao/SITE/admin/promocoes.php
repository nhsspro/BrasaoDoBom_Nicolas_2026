<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/funcoes.php';
exigir_login();
$pdo = db();
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        flash('danger', 'Sessão expirada. Tente novamente.');
        redirecionar('promocoes.php');
    }
    $acao = (string) ($_POST['acao'] ?? '');
    $id   = (int) ($_POST['id'] ?? 0);

    if ($acao === 'salvar') {
        $tit   = trim((string) ($_POST['titulo'] ?? ''));
        $desc  = trim((string) ($_POST['descricao'] ?? ''));
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        if (tamanho_texto($tit) < 3 || tamanho_texto($tit) > 80) {
            $erros[] = 'O título deve ter de 3 a 80 caracteres.';
        }
        if (tamanho_texto($desc) < 5 || tamanho_texto($desc) > 255) {
            $erros[] = 'A descrição deve ter de 5 a 255 caracteres.';
        }
        if (!$erros) {
            if ($id > 0) {
                $pdo->prepare('UPDATE promocoes SET titulo = ?, descricao = ?, ativo = ? WHERE id = ?')->execute([$tit, $desc, $ativo, $id]);
                flash('success', 'Promoção atualizada.');
            } else {
                $pdo->prepare('INSERT INTO promocoes (titulo, descricao, ativo) VALUES (?, ?, ?)')->execute([$tit, $desc, $ativo]);
                flash('success', 'Promoção criada.');
            }
            redirecionar('promocoes.php');
        }
        $edicao = ['id' => $id, 'titulo' => $tit, 'descricao' => $desc, 'ativo' => $ativo];
    } elseif ($acao === 'alternar') {
        $pdo->prepare('UPDATE promocoes SET ativo = 1 - ativo WHERE id = ?')->execute([$id]);
        redirecionar('promocoes.php');
    } elseif ($acao === 'excluir') {
        $pdo->prepare('DELETE FROM promocoes WHERE id = ?')->execute([$id]);
        flash('success', 'Promoção excluída.');
        redirecionar('promocoes.php');
    }
}

if (!isset($edicao)) {
    $edicao = ['id' => 0, 'titulo' => '', 'descricao' => '', 'ativo' => 1];
    if (isset($_GET['editar'])) {
        $stmt = $pdo->prepare('SELECT * FROM promocoes WHERE id = ?');
        $stmt->execute([(int) $_GET['editar']]);
        $edicao = $stmt->fetch() ?: $edicao;
    }
}
$lista = $pdo->query('SELECT * FROM promocoes ORDER BY id DESC')->fetchAll();

$titulo = 'Promoções';
require __DIR__ . '/_topo.php';
?>
<h1 class="h3 mb-3">Promoções</h1>
<?php if ($erros): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erros as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="card card-body mb-4 row g-3 mx-0">
  <?= csrf_campo() ?>
  <input type="hidden" name="acao" value="salvar">
  <input type="hidden" name="id" value="<?= (int) $edicao['id'] ?>">
  <h2 class="h5"><?= $edicao['id'] ? 'Editar promoção #' . (int) $edicao['id'] : 'Nova promoção' ?></h2>
  <div class="col-md-4"><label class="form-label" for="titulo">Título</label>
    <input class="form-control" id="titulo" name="titulo" maxlength="80" value="<?= e($edicao['titulo']) ?>" required></div>
  <div class="col-md-8"><label class="form-label" for="descricao">Descrição</label>
    <input class="form-control" id="descricao" name="descricao" maxlength="255" value="<?= e($edicao['descricao']) ?>" required></div>
  <div class="col-12 d-flex align-items-center gap-3">
    <div class="form-check"><input class="form-check-input" type="checkbox" id="ativo" name="ativo" <?= $edicao['ativo'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="ativo">Exibir no site</label></div>
    <button class="btn btn-gold">Salvar</button>
    <?php if ($edicao['id']): ?><a href="promocoes.php" class="btn btn-link text-secondary">Cancelar edição</a><?php endif; ?>
  </div>
</form>

<div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Título</th><th>Descrição</th><th>Situação</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($lista as $p): ?>
      <tr>
        <td><?= e($p['titulo']) ?></td>
        <td class="small"><?= e($p['descricao']) ?></td>
        <td><?= $p['ativo'] ? '<span class="badge bg-success">No site</span>' : '<span class="badge bg-secondary">Oculta</span>' ?></td>
        <td class="text-end text-nowrap tabela-acoes">
          <form method="post"><?= csrf_campo() ?><input type="hidden" name="acao" value="alternar"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button class="btn btn-sm btn-outline-light"><?= $p['ativo'] ? 'Ocultar' : 'Exibir' ?></button></form>
          <a class="btn btn-sm btn-outline-light" href="?editar=<?= (int) $p['id'] ?>">Editar</a>
          <form method="post" onsubmit="return confirm('Excluir esta promoção?');"><?= csrf_campo() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Excluir</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="4" class="text-center text-secondary py-4">Nenhuma promoção cadastrada.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
