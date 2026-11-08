<div class="specimen-details">
    <h2>Ficha do exemplar</h2>
    <dl class="detail-grid">
        <?php foreach (['edition'=>'Edição / volume','publication_year'=>'Ano de publicação','language'=>'Idioma','binding'=>'Encadernação','provenance'=>'Origem'] as $key=>$label): ?><div><dt><?= $label ?></dt><dd><?= e($book[$key] ?: 'Não informado') ?></dd></div><?php endforeach; ?>
        <div><dt>Custo de aquisição / unidade</dt><dd><?= $book['cost_cents'] === null ? 'Não informado' : money($book['cost_cents']) ?></dd></div>
    </dl>
    <h2 class="mt-4">Conservação & características</h2>
    <p class="text-secondary small"><?= e(\Sebook\Inventory::CONDITION_GUIDE[$book['condition']] ?? '') ?></p>
    <?php $bookFeatures=json_decode($book['features'],true); if ($bookFeatures): ?><ul class="feature-tags"><?php foreach ($bookFeatures as $key): ?><li><?= e(\Sebook\Inventory::FEATURES[$key]) ?></li><?php endforeach; ?></ul><?php else: ?><p class="text-secondary small">Nenhuma característica adicional registrada.</p><?php endif; ?>
    <?php if ($book['condition_notes']): ?><p class="condition-note preserve-lines"><?= e($book['condition_notes']) ?></p><?php endif; ?>
</div>
