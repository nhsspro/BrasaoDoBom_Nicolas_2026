<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/funcoes.php';
exigir_login();

$titulo ??= 'Painel';
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);

/** Selo colorido para o status da reserva. */
function selo_status(string $status): string
{
    $cores = ['pendente' => 'warning text-dark', 'confirmada' => 'success', 'cancelada' => 'secondary'];
    return '<span class="badge bg-' . ($cores[$status] ?? 'secondary') . '">' . e(ucfirst($status)) . '</span>';
}

$menu = [
    'index.php'      => 'Painel',
    'reservas.php'   => 'Reservas',
    'promocoes.php'  => 'Promoções',
    'depoimentos.php' => 'Depoimentos',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> | Admin Brasão do Bom</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="admin.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary">
  <div class="container">
    <a class="navbar-brand text-gold fw-bold" href="index.php">Brasão do Bom · Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuAdmin" aria-label="Abrir menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="menuAdmin" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <?php foreach ($menu as $arquivo => $rotulo): ?>
          <li class="nav-item">
            <a class="nav-link <?= $paginaAtual === $arquivo ? 'active' : '' ?>" href="<?= e($arquivo) ?>"><?= e($rotulo) ?></a>
          </li>
        <?php endforeach; ?>
        <li class="nav-item"><a class="nav-link" href="../index.php" target="_blank" rel="noopener">Ver site</a></li>
      </ul>
      <form method="post" action="logout.php" class="d-flex align-items-center gap-2">
        <span class="text-secondary small"><?= e(usuario_logado()['nome'] ?? '') ?></span>
        <?= csrf_campo() ?>
        <button class="btn btn-sm btn-outline-light">Sair</button>
      </form>
    </div>
  </div>
</nav>

<main class="container py-4">
<?php foreach (flash_obter() as $aviso): ?>
  <div class="alert alert-<?= e($aviso['tipo']) ?> alert-dismissible fade show" role="alert">
    <?= e($aviso['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
<?php endforeach; ?>
