<?php

namespace Helpers\Bit\Prepare;

class City extends APrepare
{
    protected string $tableName = 'vetdesk_cities';

    protected function getFromSQL(): string
    {
        return "
            SELECT DISTINCT
                addr_region AS title,
                NULL AS vm_id
            from {$this->fromDBName}.clients
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `title` varchar(255) NOT NULL,
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
