<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
use Sebook\Database;
use Sebook\Inventory;

$checks = 0;
function assertDetail(bool $value, string $message): void {
    global $checks;
    if (!$value) { throw new RuntimeException($message); }
    $checks++;
}
function rejectDetail(callable $operation): void {
    try { $operation(); } catch (DomainException) { assertDetail(true, 'Validação'); return; }
    throw new RuntimeException('Valor inválido aceito.');
}
$db = Database::connect(':memory:');
$inventory = new Inventory($db);
$data = ['title'=>'Livro antigo','author'=>'Autora','condition'=>'Regular','price'=>'35,90','cost'=>'12,50','quantity'=>'2','minimum_stock'=>'1','edition'=>'2ª edição','publication_year'=>'1967','binding'=>'Capa dura','language'=>'Português','provenance'=>'Troca','features'=>['yellowed','signed'],'condition_notes'=>'Lombada desgastada; miolo íntegro.'];
$id = $inventory->create($data);
$book = $inventory->find($id);
assertDetail($book['cost_cents'] === 1250 && $book['publication_year'] === 1967, 'Custo e ano');
assertDetail($book['edition'] === '2ª edição' && $book['provenance'] === 'Troca', 'Dados do sebo');
assertDetail(json_decode($book['features'],true) === ['yellowed','signed'], 'Características');
assertDetail($inventory->search('', '', '', 1, 'Regular', 'signed')['total'] === 1, 'Filtros combinados');
assertDetail($inventory->search('', '', '', 1, 'Bom', 'signed')['total'] === 0, 'Filtro conservação');
assertDetail($inventory->search('', '', '', 1, '', 'stamp')['total'] === 0, 'Filtro característica');
$inventory->update($id,array_replace($data,['features'=>[],'cost'=>'','publication_year'=>'']));
$book = $inventory->find($id);
assertDetail($book['features'] === '[]' && $book['cost_cents'] === null && $book['publication_year'] === null, 'Limpar campos opcionais');
assertDetail($book['quantity'] === 2 && $inventory->history($id)['total'] === 1, 'Edição preserva estoque');
foreach ([['features'=>['invalido']],['features'=>'signed'],['features'=>[['signed']]],['publication_year'=>'2101'],['publication_year'=>'-10'],['cost'=>'-1'],['binding'=>'Papel'],['provenance'=>'Inválida']] as $invalid) {
    rejectDetail(fn()=>$inventory->create(array_replace($data,$invalid)));
}
$path = dirname(__DIR__) . '/storage/migration-test-' . bin2hex(random_bytes(6)) . '.sqlite';
try {
    $legacy = new PDO('sqlite:'.$path);
    $legacy->exec(file_get_contents(dirname(__DIR__).'/database/schema.sql'));
    $legacy->exec("INSERT INTO books (title,author,condition,price_cents,quantity,created_at,updated_at) VALUES ('Livro preservado','Autor','Bom',2000,2,'2026-01-01','2026-01-01')");
    $legacy->exec("INSERT INTO movements (book_id,type,quantity,balance_after,reason,created_at) VALUES (1,'entrada',2,2,'Cadastro inicial','2026-01-01')");
    $legacy = null;
    $migrated = Database::connect($path);
    assertDetail((int)$migrated->query('PRAGMA user_version')->fetchColumn() === 2, 'Versão migrada');
    assertDetail($migrated->query('SELECT quantity FROM books WHERE id=1')->fetchColumn() === 2, 'Saldo preservado');
    assertDetail((int)$migrated->query('SELECT COUNT(*) FROM movements')->fetchColumn() === 1, 'Histórico preservado');
    assertDetail($migrated->query('SELECT features FROM books')->fetchColumn() === '[]', 'Novos campos inicializados');
    $backups = glob($path.'.before-v2-*.bak');
    assertDetail(count($backups) === 1, 'Backup criado');
    $backup = new PDO('sqlite:'.$backups[0]);
    assertDetail((int)$backup->query('PRAGMA user_version')->fetchColumn() === 1, 'Backup conserva esquema antigo');
    $backup = null;
    $migrated = null;
    $again = Database::connect($path);
    assertDetail(count(glob($path.'.before-v2-*.bak')) === 1, 'Migração não se repete');
} finally {
    $legacy = $migrated = $again = $backup = null;
    foreach (array_merge([$path], glob($path.'.before-v2-*.bak')) as $file) { if (is_file($file)) unlink($file); }
}
echo "OK: $checks verificações de detalhes do sebo e migração.\n";
