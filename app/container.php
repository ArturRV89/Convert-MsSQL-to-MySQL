<?php

$container = new Pimple\Container();

$dsn = getenv('DEFAULT_DSN');
$username = getenv('DEFAULT_USERNAME');
$password = getenv('DEFAULT_PASSWORD');

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}catch (PDOException $e) {
    print_r($e->getMessage());
}

$container['db'] = function () use ($pdo) {
    return $pdo;
};

return $container;
