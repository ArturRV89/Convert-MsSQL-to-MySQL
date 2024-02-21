<?php

namespace Import;

use Entity\User as VMUser;
use App\Components\NDatabase\NDatabase;
use NSession\NSession;
use const Helpers\Import\DEFAULT_CITY_ID;

abstract class ADBEntity implements IDBEntity, ILoggableEntity
{
    protected $data = [];
    protected $tableName;
    protected $idFieldName = 'id';

    /** @var $logger CLILogger */
    protected $logger;
    private int $defaultCityId;

    public function __construct()
    {
        if (!empty($this->tableName)) {
            if (!NDatabase::isColumnExists($this->tableName, 'vm_id')) {
                NDatabase::query("ALTER TABLE `{$this->tableName}` ADD COLUMN `vm_id` INT DEFAULT NULL");
            }
        }
    }

    public function setLogger($logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    abstract protected function save();
    abstract protected function buildMainQuery();

    public function getEntityName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function import()
    {
        $stmt = NDatabase::getStatement($this->buildMainQuery());
        $this->each($stmt);
    }

    protected function each(\PDOStatement $stmt)
    {
        $countRows = $stmt->rowCount();
        $this->logger->getProgress()->clear()->setLimit($countRows);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->logger->getProgress()->inc();
            $this->setData($row);
            $vmId = $this->save();
            $this->saveVMId($vmId);
        }
        $stmt->closeCursor();
    }

    protected function getDefaultVMCityId()
    {
        if (!defined('DEFAULT_CITY_ID')) {
            return (int) NDatabase::getOne("SELECT id FROM city WHERE title = 'Ваш город';");
        } else {
            return (int) DEFAULT_CITY_ID;
        }
    }

    protected function getVMClinicId()
    {
        return (int) NSession::get('clinic', 'id');
    }

    protected function getVMCityId()
    {
        if (empty($this->defaultCityId)) {
            $clinicId = $this->getVMClinicId();
            $this->defaultCityId = (int) NDatabase::getOne(
                "
                    SELECT
                        city_id
                    FROM clinics
                    WHERE id = :id;
                ",
                [':id' => $clinicId]
            );
        }
        return $this->defaultCityId;
    }

    protected function getVMAdminId()
    {
        return VMUser::getFirstAdminId();
    }

    abstract protected function getId();

    public function getDescription(): string
    {
        return $this->data['title'] ?? 'EMPTY';
    }

    protected function setData($data)
    {
        $this->data = $data;
    }

    protected function saveVMId($vmId)
    {
        if (!empty($this->tableName)) {
            NDatabase::query(
                "UPDATE {$this->tableName} SET vm_id = :vmId WHERE {$this->idFieldName} = :id",
                [':vmId' => $vmId, ':id' => $this->getId()]
            );
        }
    }
    protected function getDafauldTimeZone()
    {
        return NDatabase::getOne("SELECT `time_zone` FROM `clinics` WHERE `status` = 'ACTIVE'");
    }
}
