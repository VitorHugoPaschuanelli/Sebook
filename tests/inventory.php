<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
date_default_timezone_set('America/Sao_Paulo');

use Sebook\Database;
use Sebook\Inventory;

$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) { throw new RuntimeException($message); }
    $checks++;
}
function rejects(callable $action): void {
    try { $action(); } catch (DomainException) { check(true, 'Validação'); return; }
    throw new RuntimeException('Uma operação inválida foi aceita.');
}
$db = Database::connect(':memory:');
$inventory = new Inventory($db);
$input = ['title' => 'Ação entre livros', 'author' => 'Autora', 'category' => 'Literatura', 'condition' => 'Bom', 'price' => '25,90', 'minimum_stock' => '1', 'quantity' => '3'];
$id = $inventory->create($input);
check($inventory->find($id)['price_cents'] === 2590, 'Preço exato em centavos');
check($inventory->find($id)['quantity'] === 3, 'Saldo inicial');
check($inventory->history($id)['total'] === 1, 'Histórico inicial');
check($inventory->search('AÇÃO')['total'] === 1, 'Busca Unicode');
check($inventory->search("' OR 1=1 --")['total'] === 0, 'Busca parametrizada');
$inventory->move($id, ['type'=>'saida','quantity'=>'2','reason'=>'Venda']);
check($inventory->find($id)['quantity'] === 1, 'Saída');
check($inventory->search('', 'low')['total'] === 1, 'Filtro de estoque mínimo');
rejects(fn() => $inventory->move($id, ['type'=>'saida','quantity'=>'2','reason'=>'Venda']));
check($inventory->find($id)['quantity'] === 1 && $inventory->history($id)['total'] === 2, 'Rollback sem histórico falso');
rejects(fn() => $inventory->move($id, ['type'=>'entrada','quantity'=>'0','reason'=>'Compra']));
rejects(fn() => $inventory->move($id, ['type'=>'entrada','quantity'=>'1','reason'=>'Venda']));
rejects(fn() => $inventory->archive($id, true));
$inventory->update($id, array_replace($input, ['title'=>'Livro revisado','quantity'=>'999']));
check($inventory->find($id)['quantity'] === 1, 'Edição não altera saldo');
$inventory->move($id, ['type'=>'saida','quantity'=>'1','reason'=>'Venda']);
$inventory->archive($id, true);
check($inventory->search()['total'] === 0 && $inventory->search('', 'archived')['total'] === 1, 'Arquivo preserva cadastro');
rejects(fn() => $inventory->move($id, ['type'=>'entrada','quantity'=>'1','reason'=>'Compra']));
$inventory->archive($id, false);
$inventory->move($id, ['type'=>'entrada','quantity'=>'5','reason'=>'Compra']);
check($inventory->history($id)['movements'][0]['balance_after'] === 5, 'Saldo no histórico');
foreach (['-1', '1.5', '1000001', []] as $quantity) { rejects(fn() => $inventory->create(array_replace($input, ['quantity'=>$quantity]))); }
foreach (['-2', '1,234', 'abc'] as $price) { rejects(fn() => $inventory->create(array_replace($input, ['price'=>$price]))); }
rejects(fn() => $inventory->create(array_replace($input, ['title'=>''])));
rejects(fn() => $inventory->create(array_replace($input, ['condition'=>'Inválido'])));
check($inventory->search()['total'] === 1, 'Cadastros inválidos não persistidos');
$db->exec("CREATE TRIGGER fail_history BEFORE INSERT ON movements BEGIN SELECT RAISE(ABORT, 'falha simulada'); END");
try { $inventory->move($id, ['type'=>'entrada','quantity'=>'1','reason'=>'Compra']); } catch (PDOException) {}
check($inventory->find($id)['quantity'] === 5, 'Transação protege saldo de falha no histórico');
echo "OK: $checks verificações de estoque.\n";
