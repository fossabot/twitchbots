<?php

namespace Mini\Model;

use \PDO;

class TypeListDescriptor extends ListDescriptor
{
    /** @var bool */
    public $includeDisabled = false;

    /**
     * @return string[]
     */
    protected function addWhere(): array
    {
        $where = [];

        if(count($this->ids)) {
            foreach($this->ids as $id) {
                $this->addParam($id, PDO::TYPE_STRING);
            }
            $conditions = array_fill(0, count($this->ids), '?');
            $where[] = 'table.'.self::$idField.' IN ('.implode(',', $conditions).')';
        }

        if(!$this->includeDisabled) {
            $where[] = 'table.enabled=1';
        }

        return $where;
    }
}