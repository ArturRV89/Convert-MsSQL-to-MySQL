<?php

namespace Helpers\Bit\Convert;

use PDO;

class Breed extends APrepare
{
    protected string $tableName = 'bit_breeds';

    protected function getFromMSSQL(): string
    {
        return
            <<<SQL
            SELECT
                _Description as title,
                '1' as pet_type_id
            FROM {$this->fromDBName}.dbo._Reference122
            SQL;
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                title varchar(255),
                pet_type_id int
            ) DEFAULT CHARSET=utf8;
        ";
    }

    protected function migrateData(): void
    {
        $mysqlQuery = $this->rootSqlPDO->prepare(
            "INSERT INTO `{$this->toDBName}`.`{$this->tableName}` (
                title,
                pet_type_id
            ) VALUES (
                :value1, :value2
            )"
        );

        $mssqlQuery = $this->rootMsSqlPDO->query($this->getFromMSSQL());
        $count = null;

        while ($item = $mssqlQuery->fetch(PDO::FETCH_ASSOC)) {
            $mysqlQuery->bindParam(':value1', $item['title']);
            $mysqlQuery->bindParam(':value2', $item['pet_type_id']);

            $mysqlQuery->execute();

            $count++;
            $this->logger->setSuccess()
                ->simpleMessage("[{$count}] Added in \"{$this->tableName}\": {$item['title']}")
                ->setNormal();
        }
    }
}
