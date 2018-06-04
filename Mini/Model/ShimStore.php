<?php

namespace Mini\Model;

use PDOStatement;

class ShimStore extends Store {
    public function prepareQuery(string $sql): PDOStatement
    {
        return parent::prepareQuery($sql);
    }

    public function prepareSelect(string $select = "*", string $where = "", string $table = NULL): PDOStatement
    {
        return parent::prepareSelect($select, $where, $table);
    }

    public function prepareDelete(string $condition): PDOStatement
    {
        return parent::prepareDelete($condition);
    }

    public function prepareInsert(string $structure): PDOStatement
    {
        return parent::prepareInsert($structure);
    }

    public function prepareUpdate(string $set): PDOStatement
    {
        return parent::prepareUpdate($set);
    }

    public function createTempTable(array $values): string
    {
        return parent::createTempTable($values);
    }

    public function cleanUpTempTable(string $tempTable): void
    {
        parent::cleanUpTempTable($tempTable);
    }
}
