<?php

namespace Helpers\Bit\Prepare;

class Service extends APrepare
{
    protected string $tableName = 'vetdesk_services';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                id,
                cat_id AS group_id,
                `name` AS `title`,
                price,
                NULL AS vm_id
            FROM `{$this->fromDBName}`.`manipulations`
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
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
