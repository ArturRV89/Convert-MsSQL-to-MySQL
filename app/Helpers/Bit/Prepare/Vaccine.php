<?php

namespace Helpers\Bit\Prepare;

class Vaccine extends APrepare
{
    protected string $tableName = 'vetdesk_vaccines';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                d.id,
                d.`value` AS `title`,
                NULL AS vm_id
            FROM `{$this->fromDBName}`.`dictionaries_list` l
            JOIN `{$this->fromDBName}`.`dictionaries_data` d ON l.id = d.dict_id
            WHERE l.name like '%вакцин%'
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `id` INT NOT NULL,
                `title` varchar(160) NOT NULL,
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
