<div class="page-heading">
    <div><p class="eyebrow">CATÁLOGO & ESTOQUE</p><h1>Acervo do sebo<span class="heading-dot">.</span></h1><p>Cada edição, cada marca do tempo, tudo no seu lugar.</p></div>
    <a class="btn btn-primary" href="<?= e(url(['page'=>'new'])) ?>"><span aria-hidden="true">＋</span> Cadastrar livro</a>
</div>
<section class="panel catalog-panel" aria-label="Consulta do acervo">
    <form method="get" class="catalog-search" role="search">
        <input type="hidden" name="page" value="books"><input type="hidden" name="status" value="<?= e($status) ?>">
        <div class="row g-3 align-items-end">
            <div class="col-lg-6"><label class="form-label" for="q">Encontre um livro</label><input class="form-control" type="search" id="q" name="q" value="<?= e($query) ?>" maxlength="200" placeholder="Título, autor, ISBN, código ou estante"></div>
            <div class="col-sm-8 col-lg-4"><label class="form-label" for="category">Categoria</label><select class="form-select" id="category" name="category"><option value="">Todas as categorias</option><?php foreach ($categories as $option): ?><option <?= $category===$option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
            <div class="col-sm-4 col-lg-2"><button class="btn btn-primary w-100">Buscar no acervo</button></div>
        </div>
        <details class="extra-filters" <?= $conditionFilter || $featureFilter ? 'open' : '' ?>>
            <summary>Filtrar por conservação e características</summary>
            <div class="row g-3 mt-1">
                <div class="col-md-6"><label class="form-label" for="condition-filter">Estado de conservação</label><select class="form-select" id="condition-filter" name="condition"><option value="">Todos os estados</option><?php foreach (\Sebook\Inventory::CONDITIONS as $option): ?><option <?= $conditionFilter===$option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label" for="feature-filter">Característica do exemplar</label><select class="form-select" id="feature-filter" name="feature"><option value="">Todas as características</option><?php foreach (\Sebook\Inventory::FEATURES as $key=>$label): ?><option value="<?= e($key) ?>" <?= $featureFilter===$key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
            </div>
        </details>
    </form>
    <?php $filterParams=['page'=>'books','q'=>$query,'category'=>$category,'condition'=>$conditionFilter,'feature'=>$featureFilter]; ?>
    <nav class="catalog-tabs" aria-label="Situação do estoque">
        <?php foreach ([''=>'Todos os ativos','available'=>'Disponíveis','low'=>'Para repor','zero'=>'Sem estoque','archived'=>'Arquivados'] as $value=>$label): ?><a class="<?= $status===$value ? 'active' : '' ?>" href="<?= e(url(array_merge($filterParams,['status'=>$value]))) ?>" <?= $status===$value ? 'aria-current="page"' : '' ?>><?= $label ?></a><?php endforeach; ?>
    </nav>
    <div class="result-heading"><p><?= $listing['total'] ?> <?= $listing['total'] === 1 ? 'cadastro encontrado' : 'cadastros encontrados' ?></p><?php if ($query || $status || $category || $conditionFilter || $featureFilter): ?><a href="/">Limpar filtros</a><?php endif; ?></div>
    <?php if (!$listing['total']): ?>
    <div class="empty-state">
        <div class="shelf-illustration" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
        <h2><?= $status || $query || $category || $conditionFilter || $featureFilter ? 'Nenhum livro nesta seleção' : 'Sua estante começa aqui' ?></h2>
        <p><?= $status || $query || $category || $conditionFilter || $featureFilter ? 'Ajuste a busca ou os filtros para encontrar outros exemplares.' : 'Cadastre o primeiro livro com sua edição, conservação e localização.' ?></p>
        <a class="btn btn-outline-primary" href="<?= e(url(['page'=>'new'])) ?>">Cadastrar um livro</a>
    </div>
    <?php else: ?>
    <div class="table-responsive"><table class="table catalog-table align-middle">
        <thead><tr><th scope="col">Livro / edição</th><th scope="col">Conservação</th><th scope="col">Preço</th><th scope="col">Estoque</th><th scope="col">Estante</th><th scope="col"><span class="visually-hidden">Ações</span></th></tr></thead>
        <tbody><?php foreach ($listing['books'] as $item): ?>
            <tr>
                <td><div class="catalog-book"><span class="mini-cover cover-<?= $item['id'] % 4 ?>" aria-hidden="true"><?= e(mb_substr($item['title'],0,1)) ?></span><div><a class="book-title" href="<?= e(url(['page'=>'book','id'=>$item['id']])) ?>"><?= e($item['title']) ?></a><small><?= e($item['author']) ?></small><span class="catalog-code">#<?= str_pad((string)$item['id'],4,'0',STR_PAD_LEFT) ?><?= $item['edition'] ? ' · '.e($item['edition']) : '' ?><?= $item['publication_year'] ? ' · '.$item['publication_year'] : '' ?></span></div></div></td>
                <td><span class="condition-tag"><?= e($item['condition']) ?></span><small class="d-block"><?= e($item['binding'] ?: 'Encadernação não informada') ?></small></td>
                <td class="text-nowrap price-cell"><?= money($item['price_cents']) ?></td>
                <td><span class="badge <?= $item['quantity'] <= $item['minimum_stock'] ? 'text-bg-warning' : 'stock-badge' ?>"><?= $item['quantity'] ?> un.</span><?php if ($item['quantity'] <= $item['minimum_stock'] && !$item['archived']): ?><small class="d-block">Repor estoque</small><?php endif; ?></td>
                <td><?= e($item['location'] ?: '—') ?></td>
                <td><a class="btn btn-sm btn-outline-primary" href="<?= e(url(['page'=>'book','id'=>$item['id']])) ?>">Ver ficha<span class="visually-hidden"> de <?= e($item['title']) ?></span> ↗</a></td>
            </tr>
        <?php endforeach; ?></tbody>
    </table></div>
    <?php $pagination=$listing; $paginationParams=array_merge($filterParams,['status'=>$status]); require __DIR__.'/pagination.php'; ?>
    <?php endif; ?>
</section>
