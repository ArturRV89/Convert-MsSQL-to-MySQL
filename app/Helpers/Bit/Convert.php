<?php

namespace Helpers\Bit;

use Components\NDatabase\NDatabase;
use Helpers\Bit\Prepare\Breed;
use Helpers\Bit\Prepare\City;
use Helpers\Bit\Prepare\Client;
use Helpers\Bit\Prepare\Diagnos;
use Helpers\Bit\Prepare\Good;
use Helpers\Bit\Prepare\GoodGroup;
use Helpers\Bit\Prepare\Pet;
use Helpers\Bit\Prepare\PetColor;
use Helpers\Bit\Prepare\PetType;
use Helpers\Bit\Prepare\Service;
use Helpers\Bit\Prepare\Street;
use Helpers\Bit\Prepare\Unit;
use Helpers\Bit\Prepare\Vaccine;
use Helpers\Bit\Prepare\Visit;
use Helpers\Bit\Prepare\VisitDiagnose;
use Helpers\Import\CLILogger;
use PDO;

class Convert
{
    private string $bitDBName;
    private string $tablesDBName = 'bit_tables';
    private bool $recreateTables;
    private PDO $rootPDO;
    private CLILogger $logger;

    public function __construct(CLILogger $logger, string $bitDBName, bool $recreateTables = true)
    {
        $this->logger = $logger;
        $this->rootPDO = NDatabase::getRootPDO();
        $this->bitDBName = $bitDBName;
        $this->recreateTables = $recreateTables;
    }

    public function prepare()
    {
        $this->createDB();
        $this->prepareTables();
    }

    private function createDB()
    {
        if ($this->recreateTables) {
            $this->rootPDO->query("DROP DATABASE IF EXISTS bit_tables;");
            $this->rootPDO->query(
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

    private function prepareTables()
    {
//        $this->prepareByClass(Unit::class);
//        $this->prepareByClass(Breed::class);
//        $this->prepareByClass(PetType::class);
//        $this->prepareByClass(City::class);
//        $this->prepareByClass(Street::class);
//        $this->prepareByClass(Diagnos::class);
//        $this->prepareByClass(GoodGroup::class);
//        $this->prepareByClass(Good::class);
//        $this->prepareByClass(Vaccine::class);
//        $this->prepareByClass(Service::class);
//        $this->prepareByClass(Visit::class);
//        $this->prepareByClass(VisitDiagnose::class);
        $this->prepareByClass(Client::class);
//        $this->prepareByClass(PetColor::class);
//        $this->prepareByClass(Pet::class);
    }

    private function prepareByClass(string $class): void
    {
        (new $class($this->logger, $this->bitDBName, $this->tablesDBName, $this->recreateTables))->prepare();
    }
}
