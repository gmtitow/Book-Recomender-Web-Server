<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 20.03.2019
 * Time: 9:53
 */

namespace App\Libs\Database;

class MySQLAdapter
{
    private $host;
    private $user;
    private $password;
    private $port;

    private $DBH;

    public function __construct($config)
    {
        $this->host = $config['host'];
        $this->user = $config['username'];
        $this->password = $config['password'];
        $this->port = $config['port'];
    }

    public function openConnection()
    {
        $this->DBH = new \PDO("mysql:host=$this->host;port=$this->port",$this->user,$this->password);
        $this->DBH->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function closeConnection()
    {
        $this->DBH = null;
    }

    public function execute(string $query, array $bind = null)
    {
        $STH = $this->DBH->prepare($query);

        foreach ($bind as $param_name => $param) {
                if (is_null($param))
                    $result = $STH->bindParam($param_name, $var = '');
                else
                    $result = $STH->bindParam($param_name, $bind[$param_name]);
        }

        $result = $STH->execute();

        return $result;
    }

    public function executeQuery(CustomQuery $query)
    {
        $STH = $this->DBH->prepare($query->formSql());

        foreach ($query->getBind() as $param_name => $param) {
            $STH->bindParam($param_name, $param);
        }

        $result = $STH->execute();

        $STH->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $STH->fetchAll();

        $rowCount = $STH->rowCount();

        return ['data'=>$result,'pagination'=>['total'=>$rowCount]];
    }

    public function deleteFromSphinx($index,$id){
        $sql = 'DELETE FROM '.$index.' WHERE id=:id';
        $bind = ['id'=>$id];
        return $this->execute($sql,$bind);
    }

    public function insertIntoSphinx($index,$params){
        $sql = 'INSERT INTO '.$index.'(';

        $res = $this->formSqlFromParamsForSphinx($params);
        $sql.=$res['names'];
        $sql.=') VALUES(';
        $sql.=$res['sql'];
        $sql.=')';

        return $this->execute($sql,$res['bind']);
    }

    public function replaceIntoSphinx($index, $params){
        $sql = 'REPLACE INTO '.$index.'(';

        $res = $this->formSqlFromParamsForSphinx($params);
        $sql.=$res['names'];
        $sql.=') VALUES(';
        $sql.=$res['sql'];
        $sql.=')';

        return $this->execute($sql,$res['bind']);
    }

    private function formSqlFromParamsForSphinx($params){
        $bind = [];
        $first = true;
        $sql = '';
        $names = '';
        foreach ($params as $name=>$param){
            if($first){
                $first = false;
            } else {
                $sql .= ',';
                $names .= ',';
            }

            $sql.=':'.$name;
            $names.=$name;
            $bind[$name] = $param;
        }

        return ['sql'=>$sql, 'bind'=>$bind, 'names'=>$names];
    }
}