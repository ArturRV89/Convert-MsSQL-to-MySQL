<?php

namespace Helpers\Bit\Convert;

use Components\MsDatabase\MsDatabase;
use Components\NDatabase\NDatabase;
use Helpers\Import\CLILogger;
use PDO;

abstract class APrepare
{
    protected string $fromDBName;
    protected CLILogger $logger;
    protected string $toDBName;
    protected PDO $rootSqlPDO;
    protected $rootMsSqlPDO;
    protected string $tableName = 'bit_table_name';
    protected bool $recreate;

    public function __construct(CLILogger $logger, string $fromDBName, string $toDBName, bool $recreate = true)
    {
        $this->logger = $logger;
        $this->fromDBName = $fromDBName;
        $this->toDBName = $toDBName;
        $this->recreate = $recreate;
        $this->rootSqlPDO = NDatabase::getRootPDO();
        $this->rootMsSqlPDO = (new MsDatabase)->getRootPDOContainer();
    }

    public function tableExists(): bool
    {
        $sql = "SHOW TABLES FROM `{$this->toDBName}` LIKE '{$this->tableName}'";
        $stmt = $this->rootSqlPDO->prepare($sql);
        $stmt->execute();
        $existingTable = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $existingTable === $this->tableName;
    }

    public function prepare(): void
    {
        $this->createTable();
        $this->migrateData();
    }

    protected function createTable(): void
    {
        if ($this->recreate || !$this->tableExists()) {
            $this->logger->setSuccess()
                ->simpleMessage("CREATE TABLE `{$this->toDBName}`.`{$this->tableName}`")
                ->setNormal();
            $this->rootSqlPDO->query("DROP TABLE IF EXISTS `{$this->toDBName}`.`{$this->tableName}`");
            $this->rootSqlPDO->query(
                $this->getCreateTableSQL()
            );
        } else {
            $this->logger->setWarning()
                ->simpleMessage("Table exists `{$this->toDBName}`.`{$this->tableName}`")
                ->setNormal();
        }
    }

    protected function migrateData(): void
    {
        $this->migrateData();
    }

    abstract protected function getFromMSSQL(): string;

    abstract protected function getCreateTableSQL(): string;
}
