<div class="page-heading"><div><p class="eyebrow">SEU SEBO EM NÚMEROS</p><h1>Visão geral</h1><p>Acompanhe o acervo e os próximos cuidados com o estoque.</p></div><a class="btn btn-primary" href="<?= e(url(['page'=>'new'])) ?>">+ Cadastrar livro</a></div>
<div class="row g-3 mb-4">
    <?php foreach ([['Títulos ativos',number_format($summary['titles'],0,',','.'),'Cadastros no acervo'],['Exemplares',number_format($summary['units'],0,',','.'),'Unidades disponíveis'],['Valor do acervo',money((int)$summary['value']),'Pelo preço de venda atual'],['Estoque baixo',number_format($summary['low'],0,',','.'),'No mínimo ou abaixo dele']] as $i=>[$label,$value,$hint]): ?>
    <div class="col-6 col-xl-3"><section class="stat-card <?= $i === 3 ? 'stat-warning' : '' ?>"><p><?= e($label) ?></p><strong><?= e($value) ?></strong><small><?= e($hint) ?></small></section></div>
    <?php endforeach; ?>
</div>
<?php if (!$summary['titles']): ?>
<section class="welcome-panel mb-4"><div><p class="eyebrow">CADA LIVRO TEM UMA HISTÓRIA</p><h2>Vamos organizar a próxima?</h2><p>Cadastre o primeiro livro para começar a acompanhar<br class="d-none d-md-block"> entradas, saídas e disponibilidade em um só lugar.</p><a class="btn btn-primary" href="<?= e(url(['page'=>'new'])) ?>">Cadastrar primeiro livro</a></div><div class="book-art" aria-hidden="true"><span>S</span><span>E</span><span>B</span></div></section>
<?php endif; ?>
<div class="row g-4">
<div class="col-xl-6"><section class="panel h-100"><div class="panel-heading"><h2>Precisam de atenção</h2><a href="<?= e(url(['page'=>'books','status'=>'low'])) ?>">Ver todos</a></div><p class="section-description">Livros no estoque mínimo ou sem exemplares.</p>
<?php if (!$low['total']): ?><div class="empty-state compact"><span class="empty-symbol" aria-hidden="true">✓</span><h3>Tudo em dia por aqui</h3><p>Os alertas de estoque aparecerão neste espaço.</p></div><?php else: ?>
<ul class="activity-list"><?php foreach (array_slice($low['books'],0,5) as $item): ?><li><div><a href="<?= e(url(['page'=>'book','id'=>$item['id']])) ?>"><?= e($item['title']) ?></a><small><?= e($item['author']) ?></small></div><span class="badge text-bg-warning"><?= $item['quantity'] ?> un.</span></li><?php endforeach; ?></ul><?php endif; ?></section></div>
<div class="col-xl-6"><section class="panel h-100"><div class="panel-heading"><h2>Últimas movimentações</h2><a href="<?= e(url(['page'=>'history'])) ?>">Ver histórico</a></div><p class="section-description">As entradas e saídas mais recentes do acervo.</p>
<?php if (!$recent['total']): ?><div class="empty-state compact"><span class="empty-symbol" aria-hidden="true">↔</span><h3>Nenhuma movimentação ainda</h3><p>O histórico será registrado a cada entrada ou saída.</p></div><?php else: ?>
<ul class="activity-list"><?php foreach (array_slice($recent['movements'],0,5) as $item): ?><li><div><a href="<?= e(url(['page'=>'book','id'=>$item['book_id']])) ?>"><?= e($item['title']) ?></a><small><?= e($item['reason']) ?> · <?= date('d/m H:i',strtotime($item['created_at'])) ?></small></div><span class="badge <?= $item['type']==='entrada' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $item['type']==='entrada' ? '+' : '−' ?><?= $item['quantity'] ?></span></li><?php endforeach; ?></ul><?php endif; ?></section></div>
</div>
