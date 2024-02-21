<?php

namespace Helpers\Bit\Prepare;

class Client extends APrepare
{
    protected string $tableName = 'bit_clients';

    protected function getFromSQL(): string
    {
        return "
            SELECT
                id,
                fname AS first_name,
                mname as middle_name,
                sname AS last_name,
                phone,
                email,
                addr_zip,
                addr_region,
                addr_street,
                addr_build,
                addr_build_k,
                addr_apt,
                rec_created,
                comments,
                0 AS balance,
                NULL AS vm_id
            FROM `{$this->fromDBName}`.`clients`
        ";
    }

    protected function getCreateTableSQL(): string
    {
        return "
            CREATE TABLE `{$this->toDBName}`.`{$this->tableName}` (
                `id` INT NOT NULL,
                `first_name` VARCHAR(50) NOT NULL,
                `middle_name` VARCHAR(50) NOT NULL,
                `last_name` VARCHAR(50) NOT NULL,
                `phone` VARCHAR(50) NOT NULL,
                `email` VARCHAR(50) NOT NULL,
                `addr_zip` VARCHAR(50) NOT NULL,
                `addr_region` VARCHAR(50) NOT NULL,
                `addr_street` VARCHAR(50) NOT NULL,
                `addr_build` VARCHAR(50) NOT NULL,
                `addr_build_k` VARCHAR(50) NOT NULL,
                `addr_apt` VARCHAR(50) NOT NULL,
                `rec_created` VARCHAR(50) NOT NULL,
                `comments` VARCHAR(255) NOT NULL,
                `balance` DECIMAL(15, 10),
                `vm_id` INT
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
