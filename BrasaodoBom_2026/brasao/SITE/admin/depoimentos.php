<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/funcoes.php';
exigir_login();
$pdo = db();
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        flash('danger', 'Sessão expirada. Tente novamente.');
        redirecionar('depoimentos.php');
    }
    $acao = (string) ($_POST['acao'] ?? '');
    $id   = (int) ($_POST['id'] ?? 0);

    if ($acao === 'salvar') {
        $autor   = trim((string) ($_POST['autor'] ?? ''));
        $texto  = trim((string) ($_POST['texto'] ?? ''));
        $aprovado = isset($_POST['aprovado']) ? 1 : 0;
        if (tamanho_texto($autor) < 2 || tamanho_texto($autor) > 60) {
            $erros[] = 'O nome do autor deve ter de 2 a 60 caracteres.';
        }
        if (tamanho_texto($texto) < 5 || tamanho_texto($texto) > 255) {
            $erros[] = 'O texto deve ter de 5 a 255 caracteres.';
        }
        if (!$erros) {
            if ($id > 0) {
                $pdo->prepare('UPDATE depoimentos SET autor = ?, texto = ?, aprovado = ? WHERE id = ?')->execute([$autor, $texto, $aprovado, $id]);
                flash('success', 'Depoimento atualizado.');
            } else {
                $pdo->prepare('INSERT INTO depoimentos (autor, texto, aprovado) VALUES (?, ?, ?)')->execute([$autor, $texto, $aprovado]);
                flash('success', 'Depoimento criado.');
            }
            redirecionar('depoimentos.php');
        }
        $edicao = ['id' => $id, 'autor' => $autor, 'texto' => $texto, 'aprovado' => $aprovado];
    } elseif ($acao === 'alternar') {
        $pdo->prepare('UPDATE depoimentos SET aprovado = 1 - aprovado WHERE id = ?')->execute([$id]);
        redirecionar('depoimentos.php');
    } elseif ($acao === 'excluir') {
        $pdo->prepare('DELETE FROM depoimentos WHERE id = ?')->execute([$id]);
        flash('success', 'Depoimento excluído.');
        redirecionar('depoimentos.php');
    }
}

if (!isset($edicao)) {
    $edicao = ['id' => 0, 'autor' => '', 'texto' => '', 'aprovado' => 1];
    if (isset($_GET['editar'])) {
        $stmt = $pdo->prepare('SELECT * FROM depoimentos WHERE id = ?');
        $stmt->execute([(int) $_GET['editar']]);
        $edicao = $stmt->fetch() ?: $edicao;
    }
}
$lista = $pdo->query('SELECT * FROM depoimentos ORDER BY id DESC')->fetchAll();

$titulo = 'Depoimentos';
require __DIR__ . '/_topo.php';
?>
<h1 class="h3 mb-3">Depoimentos</h1>
<?php if ($erros): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erros as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="card card-body mb-4 row g-3 mx-0">
  <?= csrf_campo() ?>
  <input type="hidden" name="acao" value="salvar">
  <input type="hidden" name="id" value="<?= (int) $edicao['id'] ?>">
  <h2 class="h5"><?= $edicao['id'] ? 'Editar depoimento #' . (int) $edicao['id'] : 'Novo depoimento' ?></h2>
  <div class="col-md-4"><label class="form-label" for="autor">Autor</label>
    <input class="form-control" id="autor" name="autor" maxlength="60" value="<?= e($edicao['autor']) ?>" required></div>
  <div class="col-md-8"><label class="form-label" for="texto">Depoimento</label>
    <input class="form-control" id="texto" name="texto" maxlength="255" value="<?= e($edicao['texto']) ?>" required></div>
  <div class="col-12 d-flex align-items-center gap-3">
    <div class="form-check"><input class="form-check-input" type="checkbox" id="aprovado" name="aprovado" <?= $edicao['aprovado'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="aprovado">Exibir no site</label></div>
    <button class="btn btn-gold">Salvar</button>
    <?php if ($edicao['id']): ?><a href="depoimentos.php" class="btn btn-link text-secondary">Cancelar edição</a><?php endif; ?>
  </div>
</form>

<div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Autor</th><th>Depoimento</th><th>Situação</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($lista as $p): ?>
      <tr>
        <td><?= e($p['autor']) ?></td>
        <td class="small"><?= e($p['texto']) ?></td>
        <td><?= $p['aprovado'] ? '<span class="badge bg-success">No site</span>' : '<span class="badge bg-secondary">Oculta</span>' ?></td>
        <td class="text-end text-nowrap tabela-acoes">
          <form method="post"><?= csrf_campo() ?><input type="hidden" name="acao" value="alternar"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button class="btn btn-sm btn-outline-light"><?= $p['aprovado'] ? 'Ocultar' : 'Exibir' ?></button></form>
          <a class="btn btn-sm btn-outline-light" href="?editar=<?= (int) $p['id'] ?>">Editar</a>
          <form method="post" onsubmit="return confirm('Excluir este depoimento?');"><?= csrf_campo() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Excluir</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="4" class="text-center text-secondary py-4">Nenhum depoimento cadastrado.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
