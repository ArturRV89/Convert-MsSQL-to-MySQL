<?php

require __DIR__. '/../vendor/autoload.php';
$container = require __DIR__. '/container.php';
$pdo = $container['db'];

function test(PDO $pdo)
{
    $sql = <<<SQL
    SELECT _Fld5424RRef FROM TestDB.dbo._InfoRg5423 GROUP BY _Fld5424RRef;
    SQL;

    $stmt = $pdo->query($sql);
    $results[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}



