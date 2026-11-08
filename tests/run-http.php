<?php

declare(strict_types=1);

$port = filter_var($argv[1] ?? 8001, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1024,'max_range'=>65535]]);
if (!$port) { throw new RuntimeException('Informe uma porta entre 1024 e 65535.'); }
$probe = @stream_socket_server('tcp://127.0.0.1:' . $port, $errorCode, $errorMessage);
if (!$probe) { throw new RuntimeException("Porta $port ocupada. Informe outra porta como argumento."); }
fclose($probe);
$project = dirname(__DIR__);
$prefix = $project . '/storage/http-test-' . bin2hex(random_bytes(8));
$testDatabase = $prefix . '.sqlite';
$previousDatabase = getenv('SEBOOK_DB_PATH');
$process = null;
try {
    putenv('SEBOOK_DB_PATH=' . $testDatabase);
    $process = proc_open(
        [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', $project . '/public'],
        [0=>['pipe','r'], 1=>['file',$prefix.'.log','a'], 2=>['file',$prefix.'-error.log','a']],
        $pipes, $project, null, ['bypass_shell'=>true, 'create_new_console'=>false]
    );
    if (!is_resource($process)) { throw new RuntimeException('Não foi possível iniciar o servidor de teste.'); }
    fclose($pipes[0]);
    $testUrl = 'http://127.0.0.1:' . $port;
    $ready = false;
    for ($attempt = 0; $attempt < 30; $attempt++) {
        usleep(100000);
        if (!proc_get_status($process)['running']) { throw new RuntimeException('Servidor de teste encerrou inesperadamente.'); }
        $connection = @stream_socket_client('tcp://127.0.0.1:' . $port, $errorCode, $errorMessage, 0.2);
        if ($connection) { fclose($connection); $ready = true; break; }
    }
    if (!$ready) { throw new RuntimeException('Servidor de testes indisponível.'); }
    $argv[1] = $testUrl;
    require __DIR__ . '/http.php';
} finally {
    putenv($previousDatabase === false ? 'SEBOOK_DB_PATH' : 'SEBOOK_DB_PATH=' . $previousDatabase);
    unset($inventory, $db);
    if (is_resource($process)) { proc_terminate($process); proc_close($process); }
    foreach ([$testDatabase, $testDatabase.'-journal', $prefix.'.log', $prefix.'-error.log'] as $file) {
        if (is_file($file)) { unlink($file); }
    }
}
