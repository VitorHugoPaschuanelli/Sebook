<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
header('Content-Type: text/html; charset=UTF-8');
$checks = null;
try {
    $checks = \Sebook\Environment::check();
} catch (\Throwable $error) {
    http_response_code(500);
    error_log((string) $error);
}
function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sebook — Ambiente local</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<main>
    <p>Trabalho de Graduação · Análise e Desenvolvimento de Sistemas</p>
    <h1>Sebook</h1>
    <p>Sistema local de controle de estoque para sebos.</p>
    <?php if ($checks !== null): ?>
        <h2>Ambiente funcionando</h2>
        <ul>
            <li>PHP <?= escape($checks['php']) ?> executado com sucesso.</li>
            <li>SQLite <?= escape($checks['sqlite']) ?> conectado em memória via PDO.</li>
            <li>Consulta SELECT 1 + 1: <strong><?= $checks['result'] ?></strong>.</li>
            <li>mbstring: ação → <?= escape($checks['text']) ?>.</li>
            <li>Autoload PSR-4 do namespace Sebook funcionando.</li>
        </ul>
    <?php else: ?>
        <h2>Verifique o ambiente</h2>
        <p>Execute composer test e consulte o terminal do servidor.</p>
    <?php endif; ?>
    <p>Estrutura inicial pronta. As funcionalidades de estoque serão desenvolvidas depois.</p>
</main>
</body>
</html>
