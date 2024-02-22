<?php

namespace Helpers\Bit\Convert;



class Client extends APrepare
{
    protected string $tableName = 'bit_clients';

    protected function getFromSQL(): string
    {
        return <<<SQL
            SELECT 
                u._IDRRef as relationCol,
                'Адрес' as address,
                '0000000000' as home_phone,
                '0000000000' as work_phone,
                'record' as note,
                '0.0000000000' as balance,
                '' as email,
                'Город' as city,
                p._Fld5570 as cell_phone,
                '0' as zip,
                u._Fld3998 as last_name,
                u._Fld4000 as first_name,
                u._Fld4022 as middle_name,
                '00000000000' as passport_series,
                '000000000' as lab_number
            FROM `{$this->fromDBName}`.`_Reference99`
            JOIN _InfoRg5562 p
            WHERE u._IDRRef = p._Fld5563_RRRef; 
            SQL
        ;
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                relationCol binary(16), 
                address varchar(100), 
                home_phone varchar(40), 
                work_phone varchar(40), 
                note mediumtext,
                balance decimal(25, 10), 
                email varchar(255), 
                city varchar(255), 
                cell_phone varchar(25), 
                zip varchar(25), 
                last_name varchar(50),
                first_name varchar(50), 
                middle_name varchar(50), 
                passport_series varchar(250), 
                lab_number varchar(20)
            ) DEFAULT CHARSET=utf8;
        ";
    }

    protected function fillTable(): void
    {
        parent::fillTable();

        $this->logger->setSuccess()
            ->simpleMessage("Update balances")
            ->setNormal();

        $stmt = $this->rootPDO->query("SELECT id FROM `{$this->toDBName}`.`{$this->tableName}`");

        while ($clientId = (int) $stmt->fetchColumn()) {
            $this->setBalance($clientId, $this->getBalance($clientId));
        }

        $stmt->closeCursor();
        $this->logger->setSuccess()
            ->simpleMessage("Done")
            ->setNormal();
    }

    private function setBalance(int $clientId, float $balance)
    {
        $this->rootPDO->prepare(
            "
                UPDATE `{$this->toDBName}`.`{$this->tableName}`
                SET balance = :balance
                WHERE id = :clientId
            "
        )->execute(
            [
                ':clientId' => $clientId,
                ':balance' => $balance
            ]
        );
    }

    private function getPaidAmount(int $clientId): float
    {
        $stmt = $this->rootPDO->prepare(
            "
                SELECT
                    SUM(paid) AS paid
                FROM `{$this->fromDBName}`.`visits`
                WHERE client_id = :clientId and IFNULL(trash, 0) = 0
            "
        );
        $stmt->execute([':clientId' => $clientId]);
        $paidAmount = (float) $stmt->fetchColumn();
        $stmt->closeCursor();

        return $paidAmount;
    }

    private function getInvoiceAmount(int $clientId): float
    {
        $stmt = $this->rootPDO->prepare(
            "
                SELECT
                    SUM(i.price * i.amount) AS amount
                FROM `{$this->fromDBName}`.`visits` v
                JOIN `{$this->fromDBName}`.`uc_invoice_data` i ON i.visit_id = v.id
                WHERE v.client_id = :clientId AND IFNULL(v.trash, 0) = 0;
            "
        );
        $stmt->execute([':clientId' => $clientId]);
        $invoiceAmount = (float) $stmt->fetchColumn();
        $stmt->closeCursor();

        return $invoiceAmount;
    }

    private function getBalance(int $clientId): float
    {
        return $this->getPaidAmount($clientId) - $this->getInvoiceAmount($clientId);
    }
}
