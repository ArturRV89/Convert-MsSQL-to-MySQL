<?php

namespace Helpers\Bit;

use Components\MsDatabase\MsDatabase;
use Components\NDatabase\NDatabase;
use Helpers\Bit\Convert\Client;
use Helpers\Bit\Convert\Breed;
use Helpers\Bit\Convert\Diagnose;
use Helpers\Bit\Convert\Good;
use Helpers\Bit\Convert\Pet;
use Helpers\Bit\Convert\MedicalCard;
use Helpers\Import\CLILogger;
use PDO;
use Pimple\Container as PimpleContainer;

class Convert
{
    private string $bitDBName;
    private string $tablesDBName = 'bit_tables';
    private string $tableNames = '';
    private bool $recreateTables;

    private $rootSqlPDO;
    private CLILogger $logger;

    public function __construct(CLILogger $logger, string $bitDBName, bool $recreateTables = true)
    {
        $this->logger = $logger;
        $this->rootSqlPDO = NDatabase::getRootPDO();
        $this->bitDBName = $bitDBName;
        $this->recreateTables = $recreateTables;
    }

    public function prepare(): void
    {
        $this->createDB();
        $this->prepareTables();
    }

    private function createDB(): void
    {
        if ($this->recreateTables) {
            $this->rootSqlPDO->query("DROP DATABASE IF EXISTS bit_tables;");
            $this->rootSqlPDO->query(
                "
                    CREATE DATABASE`{$this->tablesDBName}`
                    DEFAULT CHARACTER SET = utf8 DEFAULT COLLATE = utf8_general_ci
                "
            );
            $this->logger->setSuccess()
                ->simpleMessage("CREATE DATABASE {$this->tablesDBName}")
                ->setNormal();
        } else {
            $this->logger->setWarning()
                ->simpleMessage("SKIP CREATE DATABASE {$this->tablesDBName}")
                ->setNormal();
        }
    }

    private function tableNames()
    {
        return [
            "_Reference78",    //справочник диагнозов
            "_Reference122",   //справочник пород питомцев
            "_Reference118",   //питомцы
            "_Reference99",    //клиенты
            "_InfoRg5562",     //телефоны
            "_InfoRg5423",     //тип питомца
            "_InfoRg6977X1",   //прием осмотр
            "_Document206",    //симптомы
            "_Document227",    //прием
            "_Document6972X1", //цель приема
            "_Reference151",   //цель приема
            "_Reference186_VT4841", //цель приема
            "_Document283",    //пользователи
            "_Document283_VT3391", //пользователи
            "_Reference113",   //услуги и товары
            "_InfoRg5139",     //цена
        ];
    }

    private function prepareTables()
    {
//        $this->prepareByClass(Good::class);

//        $this->prepareByClass(Breed::class);
//        $this->prepareByClass(Client::class);
//        $this->prepareByClass(Pet::class);
        $this->prepareByClass(MedicalCard::class);
    }

    private function prepareByClass(string $class): void
    {
        (new $class($this->logger, $this->bitDBName, $this->tablesDBName, $this->recreateTables))->prepare();
    }
}
