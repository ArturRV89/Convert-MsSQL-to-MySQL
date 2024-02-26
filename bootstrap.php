<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'vendor/autoload.php';

use Components\NSession\NSession;
use Components\NDatabase\NDatabase;
use Helpers\Bit\Convert;
use Helpers\Bit\DBImporter;
use Helpers\Import\CLILogger;
use Helpers\Import\CLILoggerProgress;

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

if (empty($bitDBName) && $mode == 'convert')
{
    $logger->setError()->simpleMessage("Не указан параметр db")->setNormal();
    exit(1);
}

switch($mode) {
    case 'convert' :
        (new Convert($logger, $bitDBName, true))->prepare();
        break;

    case 'import':
        $importer = new DBImporter($logger);
        $importer->run();
        break;

    default:
        $logger->setError()->simpleMessage("Unknown mode '{$mode}'")->setNormal();
        break;
}
