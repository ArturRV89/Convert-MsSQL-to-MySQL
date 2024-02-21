<?php

namespace Import;

use Entity\ComboManual;
use Entity\ComboManualItemsRow;
use App\Components\NDatabase\NDatabase;

abstract class AComboManualItem extends ADBEntity
{
    protected $comboManualName = '';

    private $itemsRow;
    protected $defaultParams = [
        'dop_param1' => '',
        'dop_param2' => '',
        'dop_param3' => ''
    ];
    public function __construct()
    {
        $this->itemsRow = new ComboManualItemsRow();
        parent::__construct();
    }

    protected function save()
    {
        if (empty($this->comboManualName)) {
            throw new \Exception('Combo manual name is not set in ' . get_class($this));
        }

        $title = $this->getDescription();
        $comboManual = new ComboManual();
        $manualId = $this->getComboManualId();
        $nextValue = $this->getComboItemValue();

        $this->itemsRow->clear();

        if ($this->itemsRow->isUniqueTitle($manualId, $title)) {
            $id = $comboManual->addItem(
                $manualId,
                $title,
                $nextValue,
                $this->defaultParams['dop_param1'],
                $this->defaultParams['dop_param2'],
                $this->defaultParams['dop_param3']
            );
            $this->itemsRow->load($id);
            $value = $this->itemsRow->value;
            $this->logger->add($this, $value);
        } else {
            $value = $comboManual->getItemValueByComboIdAndTitle($manualId, $title);
            $this->logger->exists($this, $value);
        }
        return $value;
    }

    private function getComboManualId()
    {
        return (int) NDatabase::getOne(
            "
                SELECT id
                FROM combo_manual_names
                WHERE `name` = :name
                LIMIT 1;
            ",
            [
                ':name' => $this->comboManualName
            ]
        );
    }

    private function getComboItemValue()
    {
        return (int) NDatabase::getOne(
            "
                SELECT MAX(value + 1)
                FROM combo_manual_items
                WHERE combo_manual_id = :manualId
            ",
            [
                ':manualId' => $this->getComboManualId()
            ]
        );
    }
}
