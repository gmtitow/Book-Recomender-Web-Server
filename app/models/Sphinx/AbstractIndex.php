<?php


namespace App\models\Sphinx;


use App\Libs\Database\MySQLAdapter;
use Phalcon\Di\FactoryDefault as DI;

abstract class AbstractIndex
{
    protected $params;
    protected $index = '';

    /**
     * @var MySQLAdapter
     */
    protected $mysql;

    protected function __construct(string $index){

        $di = DI::getDefault();

        $this->mysql = $di->getMysql();

        $this->index = $index;
    }

    public function insert(){
        $this->mysql->insertIntoSphinx($this->index,$this->params);
    }

    public function replace(){
        $this->mysql->replaceIntoSphinx($this->index,$this->params);
    }

    public function delete($id){
        $this->mysql->deleteFromSphinx($this->index,$id);
    }
}