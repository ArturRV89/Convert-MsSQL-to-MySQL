<?php

namespace Helpers\Bit\Prepare;

class Visit extends APrepare
{
    protected string $tableName = 'vetdesk_visits';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                id,
                pet_id,
                rec_created,
                rec_changed,
                anamnesis,
                prescription,
                recommend,
                eat,
                vaccine_id,
                next_vaccination,
                pre_diagnosis,
                weight,
                temperature,
                user_created,
                NULL AS vm_id
            FROM `{$this->fromDBName}`.`visits`
            WHERE IFNULL(trash, 0) = 0
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `id` INT NOT NULL,
                `pet_id` INT,
                `rec_created` DATETIME,
                `rec_changed` DATETIME,
                `anamnesis` TEXT,
                `prescription` TEXT,
                `recommend` TEXT,
                `eat` TEXT,
                `vaccine_id` INT,
                `next_vaccination` DATETIME,
                `pre_diagnosis` TEXT,
                `weight` FLOAT,
                `temperature` FLOAT,
                `user_created` INT,
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
