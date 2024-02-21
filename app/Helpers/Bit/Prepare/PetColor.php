<?php

namespace Helpers\Bit\Prepare;

class PetColor extends APrepare
{
    protected string $tableName = 'vetdesk_pet_colors';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                d.id,
                d.value AS title,
                NULL AS vm_id
            FROM `{$this->fromDBName}`.`dictionaries_list` l
            JOIN `{$this->fromDBName}`.`dictionaries_data` d ON d.dict_id = l.id
            WHERE l.`name` LIKE '%Окрас%'
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `id` INT NOT NULL,
                `title` varchar(50) NOT NULL,
                `vm_id` int
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
