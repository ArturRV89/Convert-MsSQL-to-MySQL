<?php

namespace Helpers\Bit\Prepare;

class Pet extends APrepare
{
    protected string $tableName = 'vetdesk_pets';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                p.id,
                p.client_id,
                p.kind AS pet_type,
                p.breed,
                p.birthday,
                CASE s.value
                    WHEN 'male' THEN IF(p.sterilized, 'castrated', 'male')
                    WHEN 'female' THEN IF(p.sterilized, 'sterilized', 'female')
                END AS sex,
                p.name,
                p.rec_created,
                p.comments,
                p.color,
                p.rip,
                p.rip_date,
                NULL AS vm_id
            FROM {$this->fromDBName}.pets AS p
            LEFT JOIN {$this->fromDBName}.dictionaries_data AS s ON s.id = p.sex
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `id` INT NOT NULL,
                client_id INT,
                pet_type INT,
                breed INT,
                birthday DATETIME,
                sex VARCHAR(10),
                name VARCHAR(255),
                rec_created DATETIME,
                comments TEXT,
                color INT,
                rip BOOL,
                rip_date DATETIME,
                `vm_id` INT
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
