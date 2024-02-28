<?php

namespace Helpers\Bit\Convert;

use PDO;

class Client extends APrepare
{
    protected string $tableName = 'bit_clients';

    protected function getFromMSSQL(): string
    {
        return
            <<<SQL
            SELECT
                client._IDRRef as relationCol,
                MAX(IIF(ISNULL(info._Fld5585, '') = '', '', info._Fld5577)) as address,
                '0000000000' as home_phone,
                '0000000000' as work_phone,
                '0.0000000000' as balance,
                '' as email,
                'city' as city,
                MAX(info._Fld5570) as cell_phone,
                '0' as zip,
                client._Fld3998 as last_name,
                client._Fld4000 as first_name,
                client._Fld4022 as middle_name,
                '00000000000' as passport_series,
                '000000000' as lab_number
            FROM {$this->fromDBName}.dbo._Reference99 client
            JOIN {$this->fromDBName}.dbo._InfoRg5562 info
                ON info._Fld5563_RRRef = client._IDRRef
            GROUP BY client._IDRRef, client._Fld3998, client._Fld4000, client._Fld4022
            SQL;
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                id int auto_increment primary key,
                relationCol binary(16), 
                address varchar(100), 
                home_phone varchar(40), 
                work_phone varchar(40), 
                note mediumtext,
                balance decimal(25, 10), 
                email varchar(255), 
                city varchar(255), 
                cell_phone varchar(100), 
                zip varchar(25), 
                last_name varchar(50),
                first_name varchar(50), 
                middle_name varchar(50), 
                passport_series varchar(250), 
                lab_number varchar(20)
            ) DEFAULT CHARSET=utf8;
        ";
    }

    protected function migrateData(): void
    {
        $mysqlQuery = $this->rootSqlPDO->prepare(
            "INSERT INTO `{$this->toDBName}`.`{$this->tableName}` (
                relationCol,
                address,
                home_phone,
                work_phone,
                note,
                balance,
                email,
                city,
                cell_phone,
                zip,
                last_name,
                first_name,
                middle_name,
                passport_series,
                lab_number
            ) VALUES (
                :value1, :value2, :value3, :value4, :value5,
                :value6, :value7, :value8, :value9, :value10,
                :value11, :value12, :value13, :value14, :value15
            )"
        );

        $mssqlQuery = $this->rootMsSqlPDO->query($this->getFromMSSQL());
        $count = null;

        while ($item = $mssqlQuery->fetch(PDO::FETCH_ASSOC)) {
            $mysqlQuery->bindParam(':value1', $item['relationCol']);
            $mysqlQuery->bindParam(':value2', $item['address']);
            $mysqlQuery->bindParam(':value3', $item['home_phone']);
            $mysqlQuery->bindParam(':value4', $item['work_phone']);
            $mysqlQuery->bindParam(':value5', $item['note']);
            $mysqlQuery->bindParam(':value6', $item['balance']);
            $mysqlQuery->bindParam(':value7', $item['email']);
            $mysqlQuery->bindParam(':value8', $item['city']);
            $mysqlQuery->bindParam(':value9', $item['cell_phone']);
            $mysqlQuery->bindParam(':value10', $item['zip']);
            $mysqlQuery->bindParam(':value11', $item['last_name']);
            $mysqlQuery->bindParam(':value12', $item['first_name']);
            $mysqlQuery->bindParam(':value13', $item['middle_name']);
            $mysqlQuery->bindParam(':value14', $item['passport_series']);
            $mysqlQuery->bindParam(':value15', $item['lab_number']);
            $mysqlQuery->execute();

            $count++;
            $this->logger->setSuccess()
            ->simpleMessage("[{$count}] Added in \"{$this->tableName}\": {$item['last_name']} {$item['first_name']} {$item['middle_name']}")
            ->setNormal();
        }
    }
}
