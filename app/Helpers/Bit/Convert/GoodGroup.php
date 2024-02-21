<?php

namespace Helpers\Bit\Prepare;

class GoodGroup extends APrepare
{
    protected string $tableName = 'vetdesk_good_groups';

    protected function getFromSQL(): string
    {
        return "
            SELECT DISTINCT
                d.id,
                d.value AS titls,
                0 AS is_service,
                NULL AS vm_id
            FROM {$this->fromDBName}.materials m
            JOIN {$this->fromDBName}.trees_data d ON d.id = m.cat_id
            UNION ALL
            SELECT DISTINCT
                d.id,
                d.value AS title,
                1 AS is_service,
                NULL AS vm_id
            FROM {$this->fromDBName}.manipulations m
            JOIN {$this->fromDBName}.trees_data d ON d.id = m.cat_id;
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `id` INT NOT NULL,
                `title` varchar(50) NOT NULL,
                `is_service` INT,
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
