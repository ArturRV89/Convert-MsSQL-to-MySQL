<?php

namespace Helpers\Bit\Prepare;

class Diagnos extends APrepare
{
    protected string $tableName = 'vetdesk_diagnoses';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                id,
                `name` AS `title`,
                NULL AS vm_id
            FROM `{$this->fromDBName}`.`diagnoses`
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `id` INT NOT NULL,
                `title` varchar(50) NOT NULL,
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
