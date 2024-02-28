<?php

namespace Helpers\Bit\Convert;

use PDO;

class Diagnose extends APrepare
{
    protected string $tableName = 'bit_diagnoses';

    protected function getFromMSSQL(): string
    {
        return
            <<<SQL
            SELECT
                _Description as title
            FROM {$this->fromDBName}.dbo._Reference78
        SQL;
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                id INT auto_increment PRIMARY KEY,
                title  VARCHAR(250)
            ) DEFAULT CHARSET=utf8;
        ";
    }

    protected function migrateData(): void
    {
        $mysqlQuery = $this->rootSqlPDO->prepare(
            "INSERT INTO `{$this->toDBName}`.`{$this->tableName}` (
                title
            ) VALUES (
                :value1
            )"
        );

        $mssqlQuery = $this->rootMsSqlPDO->query($this->getFromMSSQL());
        $count = null;

        while ($item = $mssqlQuery->fetch(PDO::FETCH_ASSOC)) {
            $mysqlQuery->bindParam(':value1', $item['title']);
            $mysqlQuery->execute();

            $count++;
            $this->logger->setSuccess()
                ->simpleMessage("[{$count}] Added in \"{$this->tableName}\": {$item['title']}")
                ->setNormal();
        }
    }
}
