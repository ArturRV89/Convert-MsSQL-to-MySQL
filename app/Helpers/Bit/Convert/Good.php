<?php

namespace Helpers\Bit\Convert;

use PDO;

class Good extends APrepare
{
    protected string $tableName = 'bit_goods';

    protected function getFromMSSQL(): string
    {
        return
            <<<SQL
            SELECT
                _Fld4197 as title,
                'description' as description
            FROM {$this->fromDBName}.dbo._Reference113
            SQL;
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                id int auto_increment primary key,
                title varchar(160),
                description text
            ) DEFAULT CHARSET=utf8;
        ";
    }

    protected function migrateData(): void
    {
        $mysqlQuery = $this->rootSqlPDO->prepare(
            "INSERT INTO `{$this->toDBName}`.`{$this->tableName}` (
                title,
                description
            ) VALUES (
                :value1, :value2
            )"
        );

        $mssqlQuery = $this->rootMsSqlPDO->query($this->getFromMSSQL());
        $count = null;

        while ($item = $mssqlQuery->fetch(PDO::FETCH_ASSOC)) {
            $mysqlQuery->bindParam(':value1', $item['title']);
            $mysqlQuery->bindParam(':value2', $item['description']);

            $mysqlQuery->execute();

            $count++;
            $this->logger->setSuccess()
                ->simpleMessage("[{$count}] Added in \"{$this->tableName}\": {$item['title']}")
                ->setNormal();
        }
    }
}
