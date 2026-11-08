<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5ecd9">
    <title><?= e($title) ?> · Sebook</title>
    <link rel="stylesheet" href="/assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/style.css">
    <script src="/assets/bootstrap/bootstrap.bundle.min.js" defer></script>
    <script src="/app.js" defer></script>
</head>
<body>
<a href="#main" class="visually-hidden-focusable skip-link">Ir para o conteúdo</a>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="/" aria-label="Sebook — acervo"><span class="brand-mark" aria-hidden="true">s</span><span>sebook<small>livros com história</small></span></a>
        <nav class="main-nav" aria-label="Menu principal">
            <a class="<?= $page !== 'history' ? 'active' : '' ?>" href="/" <?= $page !== 'history' ? 'aria-current="page"' : '' ?>>Acervo</a>
            <a class="<?= $page === 'history' ? 'active' : '' ?>" href="<?= e(url(['page'=>'history'])) ?>" <?= $page === 'history' ? 'aria-current="page"' : '' ?>>Movimentações</a>
        </nav>
        <div class="header-note"><span aria-hidden="true">●</span> Seu sebo, organizado.</div>
    </div>
</header>
<main id="main" class="content">
    <?php if ($flash): ?><div class="alert alert-success" role="status"><?= e($flash) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><strong>Confira os dados.</strong> <?= e($error) ?></div><?php endif; ?>
    <?php require __DIR__ . '/' . match ($page) { 'books'=>'books', 'new','edit'=>'form', 'book'=>'book', 'history'=>'history' } . '.php'; ?>
</main>
<footer class="footer"><span>Sebook · Gestão de acervo para sebos</span><span>Feito para guardar boas histórias.</span></footer>
</body>
</html>
