<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
$base = $argv[1] ?? '';
$dbPath = getenv('SEBOOK_DB_PATH') ?: '';
if (!preg_match('~^http://127\.0\.0\.1:\d+$~D', $base) || !str_starts_with(basename($dbPath), 'http-test-')) {
    throw new RuntimeException('Use tests/run-http.php para isolar o banco de testes.');
}
$cookie = '';
$checks = 0;
function verify(bool $condition, string $message): void {
    global $checks;
    if (!$condition) { throw new RuntimeException($message); }
    $checks++;
}
function request(string $path, ?array $data = null): array {
    global $base, $cookie;
    $headers = ['Connection: close'];
    if ($cookie !== '') { $headers[] = 'Cookie: ' . $cookie; }
    if ($data !== null) { $headers[] = 'Content-Type: application/x-www-form-urlencoded'; }
    $context = stream_context_create(['http' => [
        'method' => $data === null ? 'GET' : 'POST', 'header' => implode("\r\n", $headers),
        'content' => $data === null ? '' : http_build_query($data),
        'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 5,
    ]]);
    $body = file_get_contents($base . $path, false, $context);
    if ($body === false) { throw new RuntimeException('Servidor de testes indisponível.'); }
    $responseHeaders = $http_response_header;
    preg_match('/\s(\d{3})\s/', $responseHeaders[0], $status);
    $location = '';
    foreach ($responseHeaders as $header) {
        if (preg_match('/^Set-Cookie: (PHPSESSID=[^;]+)/i', $header, $match)) { $cookie = $match[1]; }
        if (str_starts_with($header, 'Location: ')) { $location = substr($header, 10); }
    }
    return ['status'=>(int)$status[1], 'body'=>$body, 'location'=>$location];
}
$home = request('/');
verify($home['status'] === 200 && str_contains($home['body'], 'Acervo do sebo'), 'Abertura direta no acervo');
verify(!str_contains($home['body'], 'Visão geral'), 'Página inicial antiga removida');
verify(request('/?page=home')['status'] === 302, 'Atalho antigo redireciona ao acervo');
foreach (['/assets/bootstrap/bootstrap.min.css', '/style.css', '/app.js'] as $asset) {
    verify(request($asset)['status'] === 200, 'Arquivo público: ' . $asset);
}
foreach (['/composer.json', '/database/sebook.sqlite', '/.git/config', '/?page=inexistente', '/?page=book&id=9999'] as $path) {
    verify(request($path)['status'] === 404, 'Rota privada ou inexistente: ' . $path);
}
$form = request('/?page=new');
preg_match('/name="csrf" value="([a-f0-9]+)"/', $form['body'], $matches);
$token = $matches[1] ?? throw new RuntimeException('Token ausente');
$data = ['csrf'=>$token, 'action'=>'create', 'title'=>'Livro <script>alert(1)</script>', 'author'=>'Autora de teste', 'price'=>'29,90', 'quantity'=>'3', 'minimum_stock'=>'1', 'condition'=>'Bom', 'category'=>'Literatura', 'location'=>'A1', 'edition'=>'1ª edição', 'publication_year'=>'1950','binding'=>'Capa dura','provenance'=>'Troca','cost'=>'10,90','features'=>['signed','yellowed'],'condition_notes'=>'Miolo preservado <teste>'];
verify(request('/?page=new', array_replace($data, ['csrf'=>'incorreto']))['status'] === 403, 'Bloqueio CSRF');
$invalid = request('/?page=new', array_replace($data, ['title'=>'']));
verify($invalid['status'] === 422 && str_contains($invalid['body'],'Autora de teste'), 'Validação preserva formulário');
$created = request('/?page=new', $data);
verify($created['status'] === 303 && str_contains($created['location'], 'id=1'), 'Cadastro e redirecionamento');
$detail = request($created['location']);
verify($detail['status'] === 200 && str_contains($detail['body'], '&lt;script&gt;') && !str_contains($detail['body'], '<script>alert(1)</script>'), 'Escape HTML');
verify(str_contains($detail['body'], 'R$ 29,90') && str_contains($detail['body'], 'Cadastro inicial'), 'Preço e histórico inicial');
verify(str_contains($detail['body'], 'Autografado pelo autor') && str_contains($detail['body'], 'Miolo preservado &lt;teste&gt;'), 'Características e conservação na ficha');
verify(str_contains(request('/?page=edit&id=1')['body'], 'value="signed" checked'), 'Características preservadas na edição');
verify(str_contains(request('/?page=books&condition=Bom&feature=signed')['body'], 'class="book-title"'), 'Filtro de conservação e autógrafo');
verify(!str_contains(request('/?page=books&feature=stamp')['body'], 'class="book-title"'), 'Filtro de característica sem resultado');
verify(request('/?page=edit&id=1')['status'] === 200, 'Formulário de edição');
$updated = request('/?page=edit&id=1', array_replace($data, ['action'=>'update','title'=>'Livro revisado','quantity'=>'99']));
verify($updated['status'] === 303, 'Edição');
$move = ['csrf'=>$token,'action'=>'move','type'=>'saida','quantity'=>'2','reason'=>'Venda'];
verify(request('/?page=book&id=1', $move)['status'] === 303, 'Saída válida');
$denied = request('/?page=book&id=1', $move);
verify($denied['status'] === 422 && str_contains($denied['body'],'Saldo insuficiente'), 'Saída acima do saldo');
$db = Sebook\Database::connect($dbPath);
$inventory = new Sebook\Inventory($db);
verify($inventory->find(1)['quantity'] === 1 && $inventory->history(1)['total'] === 2, 'Persistência, edição sem alteração de saldo e rollback');
verify(request('/?page=book&id=1', ['csrf'=>$token,'action'=>'archive'])['status'] === 422, 'Arquivo com estoque bloqueado');
verify(request('/?page=book&id=1', array_replace($move,['quantity'=>'1']))['status'] === 303, 'Zerar estoque');
verify(request('/?page=book&id=1', ['csrf'=>$token,'action'=>'archive'])['status'] === 303, 'Arquivar');
verify(str_contains(request('/?page=books&status=archived')['body'],'Livro revisado'), 'Lista de arquivados');
verify(request('/?page=book&id=1', ['csrf'=>$token,'action'=>'restore'])['status'] === 303, 'Reativar');
verify(request('/?page=book&id=1', array_replace($move,['type'=>'entrada','quantity'=>'4','reason'=>'Compra']))['status'] === 303, 'Entrada');
verify(str_contains(request('/?page=books&q=revisado&category=Literatura')['body'],'Livro revisado'), 'Busca e filtro');
verify(!str_contains(request('/?page=books&q=ausente')['body'],'class="book-title"'), 'Busca sem resultados');
verify(str_contains(request('/?page=history')['body'],'Compra'), 'Histórico geral');
echo "OK: $checks verificações HTTP, em banco separado.\n";
