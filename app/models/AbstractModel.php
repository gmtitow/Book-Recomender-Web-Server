<?php

namespace App\Models;

use App\Libs\SupportClass;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;


abstract class AbstractModel extends \Phalcon\Mvc\Model
{
    const SORT_DEFAULT = '';
    const FORMAT_DEFAULT = '';

    public static function findByIdDefault(int $id, array $columns = null, $name_id_field = null)
    {
        if($name_id_field==null)
            $name_id_field = 'id';

        if ($columns == null)
            return self::findFirst([$name_id_field . ' = :id:',
                'bind' => ['id' => $id]]);
        else {
            return self::findFirst(['columns' => $columns, $name_id_field . ' = :id:',
                'bind' => ['id' => $id]]);
        }
    }

    public static function getWhereToFindByQuery($query,
                                                 $subject_table_name = 'subjects',
                                                 $userinfo_table_name = 'userinfo',
                                                 $company_table_name = 'companies',
                                                 $users_table_name = 'users'){

        $query = str_replace(['%','_'],'',$query);
        return ['where'=>'(('.$userinfo_table_name.'.user_id is not null and '.$users_table_name.'.deleted = false
                	and ( 
                    (('.$userinfo_table_name.'.first_name || \' \'|| '.$userinfo_table_name.'.last_name || \' \'|| '.$userinfo_table_name.'.email || \' \'|| '.$subject_table_name.'.nickname) ilike \'%\'||:query||\'%\')
                     OR
                    (('.$userinfo_table_name.'.first_name || \' \'|| '.$userinfo_table_name.'.last_name || \' \'|| '.$userinfo_table_name.'.patronymic || \' \'|| '.$userinfo_table_name.'.email
                    || \' \'|| '.$subject_table_name.'.nickname) 
                     ilike \'%\'||:query||\'%\')
                    OR
                    (('.$userinfo_table_name.'.first_name || \' \'|| '.$userinfo_table_name.'.last_name || \' \'|| '.$subject_table_name.'.nickname) ilike \'%\'||:query||\'%\')
                    OR
                    (('.$userinfo_table_name.'.first_name || \' \'|| '.$userinfo_table_name.'.last_name || \' \'|| '.$userinfo_table_name.'.patronymic || \' \'|| '.$subject_table_name.'.nickname) ilike \'%\'||:query||\'%\')
                    )
                ) or ('.$company_table_name.'.company_id is not null and '.$company_table_name.'.deleted = false
    			   and (
                    (('.$company_table_name.'.name || \' \' ||'.$company_table_name.'.full_name || \' \' ||'.$subject_table_name.'.nickname) ilike \'%\'||:query||\'%\')
                    or (('.$company_table_name.'.name || \' \' ||'.$subject_table_name.'.nickname) ilike \'%\'||:query||\'%\')
                    ))   
                    )','bind'=>['query'=>$query]];
    }

    public function sendSocketData(array $message){
        try {

            /*
             * Format of a messages
                $msg=[
                    "action"=>"message",
                    "data"=>[
                        "message_id"=>15,
                        "content"=>"hello from php",
                        "create_at"=>"10.02.1996"
                    ],
                    "user" => 158
                ];
            */

            $host = [
                'scheme' => 'udp', // udp makes it lightweight and connectionless
                'host' => SOCKET_HOST, // choose an IP where node is running
                'port' => SOCKET_PORT, // choose one > 1023
            ];

            $param = sprintf('%s://%s:%s', $host['scheme'], $host['host'], $host['port']);
            $errstr = null;
            $socket = fsockopen($param, 15, $errstr, $timeout);
            fwrite($socket, json_encode($message));
            // $this->log("SOCKET DISTRIBUTION: ".json_encode($message));
        } catch (Exception $e) {
//            var_dump($e);
            SupportClass::writeMessageInLogFile(var_export($e,true));
        }
    }

    public static function sendSocketDataV2(array $message){
        try {

            /*
             * Format of a messages
                $msg=[
                    "action"=>"message",
                    "data"=>[
                        "message_id"=>15,
                        "content"=>"hello from php",
                        "create_at"=>"10.02.1996"
                    ],
                    "user" => 158
                ];
            */

            $host = [
                'scheme' => 'udp', // udp makes it lightweight and connectionless
                'host' => '176.119.159.187', // choose an IP where node is running
                'port' => '8990', // choose one > 1023
            ];

            $param = sprintf('%s://%s:%s', $host['scheme'], $host['host'], $host['port']);
            $errstr = null;
            $socket = fsockopen($param, 15, $errstr, $timeout);
            fwrite($socket, json_encode($message));
            // $this->log("SOCKET DISTRIBUTION: ".json_encode($message));
        } catch (Exception $e) {
//            var_dump($e);
            SupportClass::writeMessageInLogFile(var_export($e,true));
        }
    }

    public static abstract function getTableName();
}