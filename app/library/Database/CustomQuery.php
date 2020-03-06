<?php

namespace App\Libs\Database;

use App\Libs\SupportClass;

class CustomQuery
{
    private $where;

    private $columns;

    private $from;

    private $bind;

    private $columns_map;

    private $id;

    private $order;

    private $group;

    private $limit;

    private $offset;

    private $distinct;

    private $not_deleted;

    private $option;

    /**
     * @return mixed
     */
    public function getDistinct()
    {
        return $this->distinct;
    }

    /**
     * @param mixed $distinct
     * @return $this
     */
    public function setDistinct($distinct)
    {
        $this->distinct = $distinct;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     * @return $this;
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param mixed $option
     * @return $this;
     */
    public function setOption($option)
    {
        $this->option = $option;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     * @return $this;
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param mixed $offset
     * @return $this;
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotDeleted()
    {
        return $this->not_deleted;
    }

    /**
     * @param mixed $not_deleted
     * @return $this;
     */
    public function setNotDeleted($not_deleted)
    {
        $this->not_deleted = $not_deleted;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     * @return $this;
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        return $this->where;
    }

    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    public function addWhere($where, $bind=null){
        if(empty(trim($this->where)))
            $this->where .=$where;
        else
            $this->where .= ' and '.$where;

        $this->addBind($bind);

        return $this;
    }

    public function addColumn($column){
        if(empty($this->getColumns()))
            $this->setColumns('*');

        $this->setColumns($this->getColumns().', '.$column);
        return $this;
    }

    public function addBind($bind){
        if($this->bind==null)
            $this->bind = $bind;
        else if($bind!=null && is_array($bind))
            $this->bind = array_merge($this->bind,$bind);
        return $this;
    }

    public function addFrom($from){
        if(empty($this->getFrom()))
            $this->setFrom($from);
        else
            $this->setFrom($this->getFrom().' '.$from);
        return $this;
    }

    public function innerJoin($join){
        $this->setFrom($this->getFrom().' inner join '.$join);
        return $this;
    }


    public function addDeleted($deleted, $table = ""){
        $this->addWhere(($table==""?"":$table.'.').'deleted = :deleted',['deleted'=>SupportClass::convertBooleanToString($deleted)]);
//        if($this->bind==null)
//            $this->bind = ['deleted'=>SupportClass::convertBooleanToString($deleted)];
//        else
//            $this->bind = array_merge($this->bind,['deleted'=>SupportClass::convertBooleanToString($deleted)]);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     * @return $this;
     */
    public function setColumns($columns)
    {
        if(is_array($columns)){
            $this->columns = implode(',',$columns);
        } else
            $this->columns = $columns;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     * @return $this;
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBind()
    {
        return $this->bind;
    }

    /**
     * @param mixed $bind
     * @return $this;
     */
    public function setBind($bind)
    {
        $this->bind = $bind;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumnsMap()
    {
        return $this->columns_map;
    }

    /**
     * @param mixed $columns_map
     * @return $this;
     */
    public function setColumnsMap($columns_map)
    {
        $this->columns_map = $columns_map;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this;
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function __construct(array $query = null, $not_deleted = null)
    {
        if($query!=null) {
            if(isset($query['where']))
                $this->setWhere($query['where']);
            if(isset($query['id']))
                $this->setId($query['id']);
            if(isset($query['bind']))
                $this->setBind($query['bind']);
            if(isset($query['columns']))
                $this->setColumns($query['columns']);
            if(isset($query['columns_map']))
                $this->setColumnsMap($query['columns_map']);
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
                $this->setDistinct($query['distinct']);
        }
    }

    public function getQueryInArray(){
        return [
            'from'=>$this->getFrom(),
            'where'=>$this->getWhere(),
            'id'=>$this->getId(),
            'bind'=>$this->getBind(),
            'columns'=>$this->getColumns(),
            'columns_map'=>$this->getColumnsMap(),
            'order'=>$this->getOrder(),
            'options'=>$this->getOption(),
            'group'=>$this->getGroup(),
            'distinct'=>$this->getDistinct()
        ];
    }

    public function getCopy(){
        return new CustomQuery($this->getQueryInArray());
    }

    public function getSql(): string{
        return $this->formSql();
    }

    public function formSql(): string
    {
        $sql_query = 'SELECT ';
        if(!is_null($this->getDistinct())){
            $sql_query .= $this->getDistinct();
        }

        if (!is_null($this->getColumns()))
            $sql_query .= $this->getColumns();
        else
            $sql_query .= '*';

        $sql_query .= ' FROM ' . $this->getFrom();

        if (!empty($this->getWhere()))
            $sql_query .= ' where ' . $this->getWhere();

        if(!empty($this->getGroup())){
            $sql_query .= ' group by ' . $this->getGroup();
        }

        if (!empty($this->getOrder()))
            $sql_query .= ' order by ' . $this->getOrder();

        if(!is_null($this->getLimit()))
            $sql_query .= ' limit ' . $this->getLimit();

        if(!is_null($this->getOffset()))
            $sql_query .= ' offset ' . $this->getOffset();

        if(!is_null($this->getOption()))
            $sql_query .= ' option ' . $this->getOption();


        return $sql_query;
    }
}