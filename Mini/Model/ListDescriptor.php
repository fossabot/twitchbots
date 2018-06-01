<?php

namespace Mini\Model;

use \PDO;

class ListDescriptor
{
    const DIR_ASC = 1;
    const DIR_DESC = 2;

    /** @var int */
    public $offset = 0;
    /** @var int */
    public $limit;
    /** @var int */
    public $direction = self::DIR_DESC;
    /** @var string */
    public $orderBy;
    /** @var string[] */
    public $ids = [];

    protected static $idField = 'id';

    protected $query = '';
    private $params = [];
    private $paramTypes = [];

    function __constructor() {
        $this->ids = [];
        $this->reset();
    }

    private function addLimit()
    {
        if($this->limit) {

            if($this->offset > 0) {
                $this->query .= ' LIMIT :offset, :limit';
                $this->addParam($this->offset, PDO::PARAM_INT, ':offset');
                $this->addParam($this->limit, PDO::PARAM_INT, ':limit');
            }
            else {
                $this->query .= ' LIMIT :limit';
                $this->addParam($this->limit, PDO::PARAM_INT, ':limit');
            }
        }
    }

    private function addOrder()
    {
        if($this->orderBy) {
            $this->query .= ' ORDER BY '.$this->orderBy;
            if($this->direction == self::DIR_ASC) {
                $this->query .= ' ASC';
            }
            else {
                $this->query .= ' DESC';
            }
        }
    }

    /**
     * @return string[]
     */
    protected function addWhere(): array
    {
        $where = [];

        if(count($this->ids)) {
            foreach($this->ids as $id) {
                $this->addParam($id);
            }
            $conditions = array_fill(0, count($this->ids), self::$idField.' = ?');
            $where[] = '('.implode(' OR ', $conditions).')';
        }

        return $where;
    }

    public function makeSQLQuery(): string
    {
        $where = $this->addWhere();
        if(count($where)) {
            $this->query .= ' WHERE '.implode(' AND ', $where);
        }
        $this->addOrder();
        $this->addLimit();

        return $this->query;
    }

    protected function addParam($value, int $type = PDO::PARAM_STR, string $name = null) {
        $this->params[] = $value;
        if(!$name) {
            $name = count($this->params) - 1;
        }
        $this->paramType[$name] = $type;
    }

    public function bindParams(\PDOStatement $query)
    {
        foreach($this->params as $i => $value) {
            $type = $this->paramTypes[$i] ?? PDO::PARAM_STR;
            $position = $i;
            if(is_numeric($i)) {
                $position = $i + 1;
            }
            Store::BindNullable($query, $position, $value, $type);
        }
    }

    public function reset()
    {
        $this->params = [];
        $this->paramTypes = [];
    }
}
