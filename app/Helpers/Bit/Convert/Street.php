<?php

namespace Helpers\Bit\Prepare;

class Street extends APrepare
{
    protected string $tableName = 'vetdesk_streets';

    protected function getFromSQL(): string
    {
        return "
            SELECT DISTINCT
                addr_region AS city,
                addr_street AS title,
                NULL AS vm_id
            FROM {$this->fromDBName}.clients;
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `city` varchar(255) NOT NULL,
                `title` varchar(255) NOT NULL,
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
