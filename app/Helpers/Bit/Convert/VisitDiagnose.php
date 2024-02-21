<?php

namespace Helpers\Bit\Prepare;

class VisitDiagnose extends APrepare
{
    protected string $tableName = 'vetdesk_visit_diagnoses';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                pet_id,
                visit_id,
                diag_id,
                NULL AS vm_id
            FROM {$this->fromDBName}.x_pet_diags
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `pet_id` INT,
                `vm_visit_id` int,
                `diag_id` int,
                `vm_id` int
            ) DEFAULT CHARSET=utf8;
        ";
    }
}
