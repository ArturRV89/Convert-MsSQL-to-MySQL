<?php

namespace Helpers\Bit;

use Helpers\Import\ADBEntity;
use Helpers\Import\CLILogger;
use Helpers\VetDesk\DBEntity\Breed;
use Helpers\VetDesk\DBEntity\City;
use Helpers\VetDesk\DBEntity\Client;
use Helpers\VetDesk\DBEntity\Diagnose;
use Helpers\VetDesk\DBEntity\Good;
use Helpers\VetDesk\DBEntity\GoodGroup;
use Helpers\VetDesk\DBEntity\GoodService;
use Helpers\VetDesk\DBEntity\MedCard;
use Helpers\VetDesk\DBEntity\Pet;
use Helpers\VetDesk\DBEntity\PetColor;
use Helpers\VetDesk\DBEntity\PetType;
use Helpers\VetDesk\DBEntity\Street;
use Helpers\VetDesk\DBEntity\Unit;
use Helpers\VetDesk\DBEntity\Vaccination;
use Helpers\VetDesk\DBEntity\Vaccine;

class DBImporter
{
    private CLILogger $logger;

    public function __construct(CLILogger $logger)
    {
        $this->logger = $logger;
    }

    public function run()
    {
        $this->importEntity(new Diagnose());
        $this->importEntity(new PetType());
        $this->importEntity(new PetColor());
        $this->importEntity(new Breed());
        $this->importEntity(new Unit());
        $this->importEntity(new GoodGroup());
        $this->importEntity(new GoodService());
        $this->importEntity(new Good());
        $this->importEntity(new Vaccine());
        $this->importEntity(new City());
        $this->importEntity(new Street());
        $this->importEntity(new Client());
        $this->importEntity(new Pet());
        $this->importEntity(new MedCard());
        $this->importEntity(new Vaccination());
    }

    private function importEntity(ADBEntity $entity)
    {
        $entity->setLogger($this->logger);
        $entity->import();
    }
}
