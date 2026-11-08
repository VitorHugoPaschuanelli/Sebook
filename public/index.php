<?php

declare(strict_types=1);

use Sebook\Database;
use Sebook\Inventory;

require dirname(__DIR__) . '/vendor/autoload.php';
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
header('Cache-Control: no-store');
if (!in_array(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), ['/', '/index.php'], true)) {
    http_response_code(404);
    exit('Página não encontrada.');
}
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict', 'use_strict_mode' => true]);
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money(int $cents): string { return 'R$ ' . number_format($cents / 100, 2, ',', '.'); }
function url(array $params = []): string { return '/?' . http_build_query($params); }
function field(array $data, string $key, string $default = ''): string { return isset($data[$key]) && is_scalar($data[$key]) ? (string) $data[$key] : $default; }
function csrf(): void { echo '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">'; }
function redirect(array $params, string $message): never {
    $_SESSION['flash'] = $message;
    header('Location: ' . url($params), true, 303);
    exit;
}

$page = field($_GET, 'page', 'books');
if ($page === 'home') { header('Location: /?page=books', true, 302); exit; }
if (!in_array($page, ['books', 'new', 'edit', 'book', 'history'], true)) {
    http_response_code(404);
    exit('Página não encontrada.');
}
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$number = filter_var($_GET['p'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$error = null;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$input = [];
$book = null;
try {
    $inventory = new Inventory(Database::connect());
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'], field($_POST, 'csrf'))) {
            http_response_code(403);
            exit('Formulário expirado ou inválido. Volte à página anterior e atualize-a.');
        }
        $input = $_POST;
        try {
            $action = field($_POST, 'action');
            if ($action === 'create' && $page === 'new') {
                $newId = $inventory->create($input);
                redirect(['page' => 'book', 'id' => $newId], 'Livro cadastrado com sucesso.');
            } elseif ($action === 'update' && $page === 'edit') {
                $inventory->update($id, $input);
                redirect(['page' => 'book', 'id' => $id], 'Cadastro atualizado.');
            } elseif ($action === 'move' && $page === 'book') {
                $inventory->move($id, $input);
                redirect(['page' => 'book', 'id' => $id], 'Movimentação registrada.');
            } elseif (in_array($action, ['archive', 'restore'], true) && $page === 'book') {
                $inventory->archive($id, $action === 'archive');
                redirect(['page' => 'book', 'id' => $id], $action === 'archive' ? 'Livro arquivado. O histórico foi preservado.' : 'Livro reativado.');
            } else {
                throw new DomainException('Operação inválida.');
            }
        } catch (DomainException $exception) {
            http_response_code(422);
            $error = $exception->getMessage();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        header('Allow: GET, POST');
        exit('Método não permitido.');
    }
    if (in_array($page, ['edit', 'book'], true)) {
        try { $book = $inventory->find($id); } catch (DomainException) { http_response_code(404); exit('Livro não encontrado.'); }
        if ($page === 'edit' && $book['archived']) { redirect(['page'=>'book','id'=>$id], 'Reative o livro antes de editar.'); }
    }
    $summary = $inventory->summary();
    $categories = $inventory->categories();
    $query = mb_substr(trim(field($_GET, 'q')), 0, 200);
    $status = field($_GET, 'status');
    $category = field($_GET, 'category');
    $conditionFilter = field($_GET, 'condition');
    $featureFilter = field($_GET, 'feature');
    if ($page === 'books') { $listing = $inventory->search($query, $status, $category, $number, $conditionFilter, $featureFilter); }
    if ($page === 'book' || $page === 'history') { $history = $inventory->history($page === 'book' ? $id : null, $number); }
    if ($page === 'new' || $page === 'edit') {
        $values = $input ?: ($book ? array_merge($book, [
            'price'=>number_format($book['price_cents']/100, 2, ',', ''),
            'cost'=>$book['cost_cents'] === null ? '' : number_format($book['cost_cents']/100, 2, ',', ''),
            'features'=>json_decode($book['features'], true),
        ]) : ['condition'=>'Bom','quantity'=>'0','minimum_stock'=>'1','price'=>'','language'=>'Português']);
    }
    $titles = ['books'=>'Acervo do sebo', 'new'=>'Cadastrar livro', 'edit'=>'Editar livro', 'book'=>$book['title'] ?? 'Livro', 'history'=>'Movimentações'];
    $title = $titles[$page];
    require dirname(__DIR__) . '/views/layout.php';
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);
    echo '<h1>Não foi possível abrir o Sebook</h1><p>Consulte o terminal do servidor e confira se a pasta database permite gravação.</p>';
}
