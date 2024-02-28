<?php

namespace Helpers\Bit\Convert;

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
                diagnose._Fld6987 as diagnos,
                '' as recomendation,
                '' as description,
                diagnose._Fld6988 as diagnos_text,
                diagnose._Fld6988 as diagnos_type_text,
                '1' as clinic_id     
            FROM {$this->fromDBName}.dbo._Reference118 pet
            JOIN {$this->fromDBName}.dbo._Document6972X1 diagnose
                ON pet._Fld4291RRef = diagnose._Fld6985RRef
            SQL;
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                id                INT auto_increment PRIMARY KEY,
                patient_id        BINARY(16),
                diagnos           TEXT,
                recomendation     LONGTEXT,
                description       LONGTEXT,
                diagnos_text      TEXT,
                diagnos_type_text TEXT,
                clinic_id         INT
            ) DEFAULT CHARSET=utf8;
        ";
    }

    protected function migrateData(): void
    {
        $mysqlQuery = $this->rootSqlPDO->prepare(
            "INSERT INTO `{$this->toDBName}`.`{$this->tableName}` (
                patient_id,
                diagnos,
                recomendation,
                description,
                diagnos_text,
                diagnos_type_text,
                clinic_id
            ) VALUES (
                :value1, :value2, :value3, :value4, :value5, :value6, :value7
            )"
        );

        $mssqlQuery = $this->rootMsSqlPDO->query($this->getFromMSSQL());
        $count = null;

        while ($item = $mssqlQuery->fetch(PDO::FETCH_ASSOC)) {
            $mysqlQuery->bindParam(':value1', $item['patient_id,']);
            $mysqlQuery->bindParam(':value2', $item['diagnos,']);
            $mysqlQuery->bindParam(':value3', $item['recomendation,']);
            $mysqlQuery->bindParam(':value4', $item['description,']);
            $mysqlQuery->bindParam(':value5', $item['diagnos_text,']);
            $mysqlQuery->bindParam(':value6', $item['diagnos_type_text']);
            $mysqlQuery->bindParam(':value7', $item['clinic_id']);

            $mysqlQuery->execute();

            $count++;
            $this->logger->setSuccess()
                ->simpleMessage("[{$count}] Added in \"{$this->tableName}\": {$item['diagnos']}")
                ->setNormal();
        }
    }
}
