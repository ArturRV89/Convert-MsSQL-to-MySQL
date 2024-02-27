<?php

namespace Helpers\Bit\Convert;

use PDO;

class Pet extends APrepare
{
    protected string $tableName = 'bit_pets';

    protected function getFromMSSQL(): string
    {
        return
            <<<SQL
            SELECT
                c._IDRRef as relation_col_owner,
                p._IDRRef as relation_col_for_medcard,
                p._Fld4282 as alias,
                p._Description as note
            FROM {$this->fromDBName}.dbo._Reference118 p
            JOIN {$this->fromDBName}.dbo._Reference99 c
                ON p._Fld4291RRef = c._IDRRef
            SQL;
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                relation_col_owner binary(16),
                relation_col_for_medcard binary(16),
                alias varchar(255),
                note text
            ) DEFAULT CHARSET=utf8;
        ";
    }

    protected function migrateData(): void
    {
        $mysqlQuery = $this->rootSqlPDO->prepare(
            "INSERT INTO `{$this->toDBName}`.`{$this->tableName}` (
                relation_col_owner,
                relation_col_for_medcard,
                alias,
                note
            ) VALUES (
                :value1, :value2, :value3, :value4
            )"
        );

        $mssqlQuery = $this->rootMsSqlPDO->query($this->getFromMSSQL());
        $count = null;

        while ($item = $mssqlQuery->fetch(PDO::FETCH_ASSOC)) {
            $mysqlQuery->bindParam(':value1', $item['relation_col_owner']);
            $mysqlQuery->bindParam(':value2', $item['relation_col_for_medcard']);
            $mysqlQuery->bindParam(':value3', $item['alias']);
            $mysqlQuery->bindParam(':value4', $item['note']);

            $mysqlQuery->execute();

            $count++;
            $this->logger->setSuccess()
                ->simpleMessage("[{$count}] Added in \"{$this->tableName}\": {$item['alias']}")
                ->setNormal();
        }
    }
}
