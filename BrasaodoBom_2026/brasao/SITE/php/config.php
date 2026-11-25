<?php
declare(strict_types=1);

/**
 * Configurações do sistema.
 * Os valores podem ser sobrescritos por variáveis de ambiente (DB_HOST, DB_NAME, DB_USER, DB_PASS),
 * o que evita gravar senhas no código ao publicar o projeto no GitHub.
 */
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'brasao_do_bom');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '');

// Regras de negócio das reservas
define('CAPACIDADE_POR_HORARIO', 60);   // máximo de pessoas por horário
define('MAX_PESSOAS_RESERVA', 30);      // máximo de pessoas em uma única reserva
define('DIAS_ANTECEDENCIA_MAX', 60);    // até quantos dias à frente é possível reservar
define('INTERVALO_MINUTOS', 30);        // intervalo entre horários disponíveis

date_default_timezone_set('America/Sao_Paulo');
