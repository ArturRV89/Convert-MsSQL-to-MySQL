<?php

namespace Helpers\Bit\Convert;

use Components\NSession\NSession;
use PDO;

class MedicalCard extends APrepare
{
    protected string $tableName = 'bit_medical_cards';

    protected function getFromMSSQL(): string
    {
        return
            <<<SQL
            SELECT
                pet._IDRRef as patient_id,
                card._Date_Time as date_create,
                '' as diagnose,
                '' as recommendation,
                card._Fld1364 as description
            FROM {$this->fromDBName}.dbo._Reference118 pet
            JOIN {$this->fromDBName}.dbo._Document227 card
                ON pet._IDRRef = card._Fld1372RRef
            SQL;
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                id                INT auto_increment PRIMARY KEY,
                patient_id        BINARY(16),
                date_create       timestamp,
                diagnose          TEXT,
                recommendation    LONGTEXT,
                description       LONGTEXT
            ) DEFAULT CHARSET=utf8;
        ";
    }

    protected function migrateData(): void
    {
        $mysqlQuery = $this->rootSqlPDO->prepare(
            "INSERT INTO `{$this->toDBName}`.`{$this->tableName}` (
                patient_id,
                date_create,
                diagnose,
                recommendation,
                description
            ) VALUES (
                :value1, :value2, :value3, :value4, :value5
            )"
        );

        $mssqlQuery = $this->rootMsSqlPDO->query($this->getFromMSSQL());
        $count = null;

        while ($item = $mssqlQuery->fetch(PDO::FETCH_ASSOC)) {
            $mysqlQuery->bindParam(':value1', $item['patient_id']);
            $mysqlQuery->bindParam(':value2', $item['date_create']);
            $mysqlQuery->bindParam(':value3', $item['diagnose']);
            $mysqlQuery->bindParam(':value4', $item['recommendation']);
            $mysqlQuery->bindParam(':value5', $item['description']);

            $mysqlQuery->execute();

            $count++;
            $this->logger->setSuccess()
                ->simpleMessage("[{$count}] Added in \"{$this->tableName}\": {$item['diagnose']}")
                ->setNormal();
        }
    }
}
