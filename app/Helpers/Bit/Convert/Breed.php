<?php

namespace Helpers\Bit\Prepare;

class Breed extends APrepare
{
    protected string $tableName = 'vetdesk_breeds';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                id,
                value AS title,
                dict_id AS pet_type_id,
                NULL AS vm_id
            FROM `{$this->fromDBName}`.`breed_data`
            
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `id` INT NOT NULL,
                `title` varchar(50) NOT NULL,
                `pet_type_id` INT,
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
