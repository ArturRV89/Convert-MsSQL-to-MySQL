<?php

namespace Helpers\Bit\Prepare;

use Components\NDatabase\NDatabase;
use Helpers\Import\CLILogger;
use PDO;

abstract class APrepare
{
    protected string $fromDBName;
    protected CLILogger $logger;
    protected string $toDBName;
    protected PDO $rootPDO;
    protected string $tableName = 'bit_table_name';
    protected bool $recreate;

    public function __construct(CLILogger $logger, string $fromDBName, string $toDBName, bool $recreate = true)
    {
        $this->logger = $logger;
        $this->fromDBName = $fromDBName;
        $this->toDBName = $toDBName;
        $this->recreate = $recreate;
        $this->rootPDO = NDatabase::getRootPDO();
    }

    public function existsData(): bool
    {
        $stmt = $this->rootPDO->query($this->getFromSQL() . " LIMIT 1");
        $stmt->execute();
        $rowsCount = $stmt->rowCount();
        $stmt->closeCursor();
        return $rowsCount > 0;
    }

    public function tableExists(): bool
    {
        $sql = "SHOW TABLES FROM `{$this->toDBName}` LIKE '{$this->tableName}'";
        $stmt = $this->rootPDO->prepare($sql);
        $stmt->execute();
        $existingTable = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $existingTable === $this->tableName;
    }

    public function prepare(): void
    {
        $this->createTable();
        $this->fillTable();
    }

    protected function createTable(): void
    {
        if ($this->recreate || !$this->tableExists()) {
            $this->logger->setSuccess()
                ->simpleMessage("Create table `{$this->toDBName}`.`{$this->tableName}`")
                ->setNormal();
            $this->rootPDO->query("DROP TABLE IF EXISTS `{$this->toDBName}`.`{$this->tableName}`");
            $this->rootPDO->query(
                $this->getCreateTableSQL()
            );
        } else {
            $this->logger->setWarning()
                ->simpleMessage("Table exists `{$this->toDBName}`.`{$this->tableName}`")
                ->setNormal();
        }
    }

    protected function fillTable(): void
    {
        $stmt = $this->rootPDO->query(
            "INSERT INTO `{$this->toDBName}`.`{$this->tableName}`" .
            $this->getFromSQL()
        );

        $this->logger->setSuccess()
            ->simpleMessage("Rows inserted " . $stmt->rowCount())
            ->setNormal();
    }

    abstract protected function getFromSQL(): string;

    abstract protected function getCreateTableSQL(): string;
}
