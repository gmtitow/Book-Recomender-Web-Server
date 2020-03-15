<?php


namespace App\library\Database;


abstract class QueryBuilder
{
    public abstract function getCopy();

    public abstract function getSql();
    public abstract function formSql();

    public abstract function getDistinct();
    public abstract function getGroup();
    public abstract function getOption();
    public abstract function getLimit();
    public abstract function getOffset();
    public abstract function getNotDeleted();
    public abstract function getOrder();
    public abstract function getWhere();
    public abstract function getColumns();
    public abstract function getFrom();
    public abstract function getBind();

    public abstract function setDistinct(string $distinct/*, string $id_name*/);
    public abstract function setGroup($group);
    public abstract function setOption($option);
    public abstract function setLimit($limit);
    public abstract function setOffset($offset);
    public abstract function setNotDeleted($deleted);
    public abstract function setOrder($order);
    public abstract function setWhere($where);
    public abstract function setColumns($columns);
    public abstract function setFrom($from);
    public abstract function setBind($bind);


    public abstract function addWhere($where, array $bind = null);
    public abstract function addBind(array $bind);
    public abstract function addColumn($column);

    public function __construct(array $query = null, $not_deleted = null) {
        if($query!=null) {
            if(isset($query['where']))
                $this->setWhere($query['where']);
            if(isset($query['bind']))
                $this->setBind($query['bind']);
            if(isset($query['columns']))
                $this->setColumns($query['columns']);
            if(isset($query['from']))
                $this->setFrom($query['from']);
            if(isset($query['order']))
                $this->setOrder($query['order']);
            if(isset($query['limit']))
                $this->setLimit($query['limit']);
            if(isset($query['offset']))
                $this->setOffset($query['offset']);
            if(isset($query['option']))
                $this->setOption($query['option']);
            if(isset($query['group']))
                $this->setGroup($query['group']);
            if(isset($query['distinct']))
            {
//                if (!isset($query['id'])){
//                    throw new \Exception("Query with 'distinct' must contain 'id'");
//                }
                $this->setDistinct($query['distinct']/*, $query['id']*/);
            }

            if(isset($query['id']))
                $this->setId($query['id']);
            if(!is_null($not_deleted))
                $this->setNotDeleted($not_deleted);
        }
    }

    public abstract function getQueryInArray() : array;
}