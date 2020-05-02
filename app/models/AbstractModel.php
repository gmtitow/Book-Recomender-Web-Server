<?php

namespace App\Models;

use App\Libs\SupportClass;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;


abstract class AbstractModel extends \Phalcon\Mvc\Model
{
    /**
     * @param array $object_data
     * @return static
     */
    public static function convert(array $object_data) {
        $class = static::class;
        $object = new $class();

        foreach ($object_data as $name=>$data) {
            if (property_exists($object,$name)) {
                $object->$name = $data;
            }
        }

        return $object;
    }

    public static abstract function getTableName();

    public static abstract function getIdField();

    /**
     * @param $id
     * @param null $columns
     * @return static|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findById($id, $columns = null){
        if ($columns == null)
            return self::findFirst([static::getIdField() . ' = :id:',
                'bind' => ['id' => $id]]);
        else {
            return self::findFirst(['columns' => $columns, static::getIdField() . ' = :id:',
                'bind' => ['id' => $id]]);
        }
    }

    public function getErrors() {
        $errors = [];
        foreach ($this->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        return $errors;
    }
}