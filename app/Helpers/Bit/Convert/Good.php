<?php

namespace Helpers\Bit\Prepare;

class Good extends APrepare
{
    protected string $tableName = 'vetdesk_goods';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                id,
                cat_id AS group_id,
                `name` AS `title`,
                price,
                unit_id,
                NULL AS vm_id
            FROM `{$this->fromDBName}`.`materials`
            WHERE LENGTH(name) > 2
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `id` INT NOT NULL,
                `group_id` INT,
                `title` varchar(160) NOT NULL,
                `price` DECIMAL(15, 10),
                `unit_id` INT,
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
