<?php

use Components\NSession\NSession;
use Components\NDatabase\NDatabase;
use Helpers\Bit\DBImporter;
use Helpers\Bit\Prepare;
use Helpers\Import\CLILogger;
use Helpers\Import\CLILoggerProgress;


error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'vendor/autoload.php';
$container = require 'container.php';
$pdo = $container['db'];

const DOMAIN_NAME = 'one';

$opts = getopt('m:d::', ['mode:', 'db::']);

$mode = $opts['m'] ?? $opts['mode'] ?? 'none';
$bitDBName = $opts['d'] ?? $opts['db'] ?? null;

NSession::set('clinic', ['id' => 1,'title' => 'some clinic']);
$_GET['clinics'] = 'current';

NDatabase::disableSlaveConnection();

$logger = new CLILogger();
$logger->enable();
$logger->setProgress(new CLILoggerProgress());

if (empty($bitDBName) && $mode == 'prepare')
{
    $logger->setError()->simpleMessage("Не указан параметр db")->setNormal();
    exit(1);
}

switch($mode) {
    case 'convert' :

        break;
    case 'prepare':
        if (DOMAIN_NAME == 'one') {
            (new Prepare($logger, $bitDBName, true))->prepare();
        } else {
            $logger->setError()
                ->simpleMessage("Подготовку импорта необходимо запускать только локально")
                ->setNormal();
        }
        break;
    case 'import':
        $importer = new DBImporter($logger);
        $importer->run();
        break;
    default:
        $logger->setError()->simpleMessage("Unknown mode '{$mode}'")->setNormal();
        break;
}





//function test(PDO $pdo)
//{
//    $sql = <<<SQL
//    SELECT _Fld5424RRef FROM TestDB.dbo._InfoRg5423 GROUP BY _Fld5424RRef;
//    SQL;
//
//    $stmt = $pdo->query($sql);
//    return $stmt->fetchAll(PDO::FETCH_ASSOC);
//}
//
//print_r(test($pdo));