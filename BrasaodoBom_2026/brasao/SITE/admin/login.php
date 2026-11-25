<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/funcoes.php';
iniciar_sessao();

if (usuario_logado() !== null) {
    redirecionar('index.php');
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $falhas = (int) ($_SESSION['falhas_login'] ?? 0);
    if (!csrf_valido()) {
        $erro = 'Sessão expirada. Tente novamente.';
    } elseif ($falhas >= 5 && time() < (int) ($_SESSION['bloqueio_ate'] ?? 0)) {
        $erro = 'Muitas tentativas. Aguarde alguns minutos.';
    } else {
        try {
            $usuario = autenticar(trim((string) ($_POST['email'] ?? '')), (string) ($_POST['senha'] ?? ''));
        } catch (PDOException $e) {
            $erro = 'Não foi possível conectar ao banco de dados. Confira se o banco brasao_do_bom foi importado e se o MySQL está iniciado.';
            $usuario = null;
        }
        if ($usuario) {
            session_regenerate_id(true);    // evita fixação de sessão
            $_SESSION['usuario'] = $usuario;
            unset($_SESSION['falhas_login'], $_SESSION['bloqueio_ate']);
            redirecionar('index.php');
        }
        if (!$erro) $_SESSION['falhas_login'] = $falhas + 1;
        if (!$erro && $_SESSION['falhas_login'] >= 5) {
            $_SESSION['bloqueio_ate'] = time() + 300;
        }
        if (!$erro) $erro = 'E-mail ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Entrar | Admin Brasão do Bom</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="admin.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center" style="min-height:100vh;">
  <div class="container" style="max-width: 400px;">
    <h1 class="h3 text-gold text-center mb-4">Brasão do Bom · Admin</h1>
    <?php if ($erro): ?><div class="alert alert-danger"><?= e($erro) ?></div><?php endif; ?>
    <form method="post" class="card card-body gap-3">
      <?= csrf_campo() ?>
      <div>
        <label for="email" class="form-label">E-mail</label>
        <input type="email" id="email" name="email" class="form-control" required autofocus>
      </div>
      <div>
        <label for="senha" class="form-label">Senha</label>
        <input type="password" id="senha" name="senha" class="form-control" required>
      </div>
      <button class="btn btn-gold">Entrar</button>
    </form>
    <p class="text-center mt-3"><a class="text-secondary small" href="../index.php">Voltar ao site</a></p>
  </div>
</body>
</html>
