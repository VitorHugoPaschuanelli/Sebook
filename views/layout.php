<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Sebook</title>
    <link rel="stylesheet" href="/assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/style.css">
    <script src="/assets/bootstrap/bootstrap.bundle.min.js" defer></script>
    <script src="/app.js" defer></script>
</head>
<body>
<a href="#main" class="visually-hidden-focusable skip-link">Ir para o conteúdo</a>
<aside class="sidebar">
    <a class="brand" href="/"><span class="brand-mark" aria-hidden="true">S</span> sebook<span class="brand-dot">.</span></a>
    <p class="brand-subtitle">Seu acervo, em ordem.</p>
    <nav aria-label="Menu principal">
        <?php foreach (['home'=>['Visão geral','01'], 'books'=>['Acervo','02'], 'history'=>['Movimentações','03']] as $nav => [$label, $symbol]): ?>
        <?php $active = $page === $nav || ($nav === 'books' && in_array($page, ['new','edit','book'], true)); ?>
        <a class="nav-item <?= $active ? 'active' : '' ?>" href="<?= e(url(['page'=>$nav])) ?>" <?= $active ? 'aria-current="page"' : '' ?>><span class="nav-number"><?= $symbol ?></span><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-note"><span class="online-dot"></span> Ambiente local<p>Feito para o dia a dia do sebo.</p></div>
</aside>
<div class="workspace">
    <header class="topbar"><span>CONTROLE DE ESTOQUE</span><span class="date-label"><?= date('d/m/Y') ?></span></header>
    <main id="main" class="content">
        <?php if ($flash): ?><div class="alert alert-success" role="status"><?= e($flash) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger" role="alert"><strong>Confira os dados.</strong> <?= e($error) ?></div><?php endif; ?>
        <?php require __DIR__ . '/' . match ($page) { 'home'=>'home', 'books'=>'books', 'new','edit'=>'form', 'book'=>'book', 'history'=>'history' } . '.php'; ?>
    </main>
    <footer class="footer">Sebook · Trabalho de Graduação em ADS <span>Um novo capítulo para a organização do seu sebo.</span></footer>
</div>
</body>
</html>
