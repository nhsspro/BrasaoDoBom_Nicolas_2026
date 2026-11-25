<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/funcoes.php';
iniciar_sessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $_SESSION = [];
    session_destroy();
}
redirecionar('login.php');
