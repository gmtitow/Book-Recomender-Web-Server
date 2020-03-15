<?php

/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 13.08.2018
 * Time: 15:29
 */

namespace App\Libs;

use App\library\Database\QueryBuilder;
use App\Libs\Database\CustomQuery;
use Phalcon\Http\Response;
use App\Services\ServiceExtendedException;
use Phalcon\DI\FactoryDefault as DI;

class SupportClass
{

    static $time;
    const COMMON_PAGE_SIZE = 10;
    static $AvatarColors = [
        "#F87261,#F55590",
        "#fec180,#ff8993",
        "#6681ea,#7e43aa",
        "#efbad3,#a254f2",
        "#f3dcfb,#679fe4",
        "#d0ffae,#34ebe9",
        "#3B87F5,#D4ECFF",
        "#6acbe0,#6859ea",
    ];
    
    static $AvatarColorsv1 = [
        '#3B87F5',
        '#85B4F7',
        '#4730FA',
        '#2A46DE',
        '#2AA4DE',
        '#F76654',
        '#1854A8',
        '#9EC21B',
        '#8BA820',
        '#A87107',
        '#F5B53B',
        '#F6BD79',
        '#C25E1B',
        '#615B8F',
    ];

    public static function reformatDate($date, $only_string = false)
    {
        if (!$only_string) {
            $time = filter_var($date, FILTER_VALIDATE_INT);
            if (is_int($time))
                return date(USUAL_DATE_FORMAT, $time);
        }

        $date = strtotime($date);
        if (empty($date))
            return false;
        else {
            return date(USUAL_DATE_FORMAT, $date);
        }
    }

    public static function checkInteger($var)
    {
        return ((string) (int) $var == $var);
    }

    public static function checkDouble($var)
    {
        $result = filter_var($var, FILTER_VALIDATE_FLOAT);
        return is_double($result);
    }

    public static function convertBooleanToString($var)
    {
        return $var ? 'true' : 'false';
    }

    public static function checkPositiveInteger($var)
    {
        return ((string) (int) $var == $var);
    }

    /*public static function checkDouble($var){
        return ((string)(double)$var == $var);
    }*/

    public static function pullRegions($filename, $db = null)
    {
        if ($db == null)
            $db = Phalcon\DI::getDefault()->getDb();

        $content = file_get_contents($filename);
        //$content = trim($content);
        $str = str_replace("\n", '', $content);
        $str = str_replace('osmId', '"osmId"', $str);
        $str = str_replace('name', '"name"', $str);
        $str = str_replace("'", '"', $str);
        $regions = json_decode($str, true);
        //$res = json_decode($str,true);

        $db->begin();
        foreach ($regions as $region) {
            $regionObj = Regions::findFirstByRegionid($region['osmId']);
            if (!$regionObj) {
                $regionObj = new Regions();
                $regionObj->setRegionId($region['osmId']);
            }
            $regionObj->setRegionName($region['name']);

            if (!$regionObj->save()) {
                $db->rollback();
                $errors = [];
                foreach ($regionObj->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                return ['result' => false, 'errors' => $errors];
            }
        }
        $db->commit();
        return ['result' => true];
    }

    public static function transformControllerName($controllerName)
    {
        $new_controller = array();
        for ($i = 0; $i < strlen($controllerName); $i++) {
            $lowercase = strtolower($controllerName[$i]);
            if (ord($controllerName[$i]) <= 90 && $i > 0) {
                $new_controller[] = '_';
            }
            $new_controller[] = $lowercase;
        }
        $str = implode('', $new_controller);
        return implode('', $new_controller);
    }

    public static function writeMessageInLogFile($message)
    {
        //        $file = fopen(BASE_PATH . '/public/logs.txt', 'a');
        //        fwrite($file, 'Дата: ' . date('Y-m-d H:i:s') . ' - ' . $message . "\r\n");
        //        fflush($file);
        //        fclose($file);

        $logger = DI::getDefault()->getLogger();

        $logger->debug(
            $message
        );
    }

    /**
     * Optimized algorithm from http://www.codexworld.com
     *
     * @param float $latitudeFrom
     * @param float $longitudeFrom
     * @param float $latitudeTo
     * @param float $longitudeTo
     *
     * @return float [m]
     */
    public static function codexworldGetDistanceOpt($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
        $rad = M_PI / 180;
        //Calculate distance from latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin($latitudeFrom * $rad) * sin($latitudeTo * $rad) + cos($latitudeFrom * $rad) * cos($latitudeTo * $rad) * cos($theta * $rad);

        return acos($dist) / $rad * 60 * 1853;
    }

    public static function translateInPhpArrFromPostgreJsonObject($str)
    {
        //$str = json_decode($str);
        if (is_null($str))
            return [];

        /*$str[0] = '[';
        $str[strlen($str) - 1] = ']';*/

        $str = str_replace('"{', '{', $str);
        $str = str_replace('}"', '}', $str);
        //$str = stripslashes($str);

        $str = json_decode($str, true);
        return $str;
    }

    public static function to_php_arr($str)
    {
        //$str = json_decode($str);
        if (is_null($str))
            return [];

        $str[0] = '[';
        $str[strlen($str) - 1] = ']';

        $str = str_replace('"{', '{', $str);
        $str = str_replace('}"', '}', $str);
        $str = stripslashes($str);

        $str = json_decode($str, true);
        return $str;
    }

    /*public static function getResponseWithErrors($object){
        $response = new Response();
        $errors = [];
        foreach ($object->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        $response->setJsonContent(
            [
                "errors" => $errors,
                "status" => STATUS_WRONG
            ]);

        return $response;
    }*/

    public static function getResponseWithErrors($object)
    {
        $errors = [];
        foreach ($object->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        return
            [
                "errors" => $errors,
                "status" => STATUS_WRONG
            ];
    }

    public static function getArrayWithErrors($object)
    {
        $errors = [];
        foreach ($object->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        return $errors;
    }

    public static function getErrorsWithException($object, $code, $msg)
    {
        $errors = SupportClass::getArrayWithErrors($object);
        if (count($errors) > 0)
            throw new ServiceExtendedException(
                $msg,
                $code,
                null,
                null,
                $errors
            );
        else {
            throw new ServiceExtendedException(
                $msg,
                $code
            );
        }
    }

    public static function getResponseWithErrorsFromArray($errors)
    {
        $response = new Response();
        $response->setJsonContent(
            [
                "errors" => $errors,
                "status" => STATUS_WRONG
            ]
        );

        return $response;
    }

    /**
     * Convert PHP array to postgres array
     *
     * @param $set
     * @return string
     */
    public static function to_pg_array($set)
    {
        settype($set, 'array'); // can be called with a scalar or array
        $result = array();
        foreach ($set as $t) {
            if (is_array($t)) {
                $result[] = self::to_pg_array($t);
            } else {
                $t = str_replace('"', '\\"', $t); // escape double quote
                if (!is_numeric($t)) // quote only non-numeric values
                    $t = '"' . $t . '"';
                $result[] = $t;
            }
        }
        return '{' . implode(",", $result) . '}'; // format
    }

    /**
     * Convert Postgres array to php
     *
     * @param $s
     * @param int $start
     * @param null $end
     * @return array|null
     */
    public static function to_php_array($s, $start = 0, &$end = null)
    {
        if (empty($s) || $s[0] != '{') return null;
        $return = array();
        $string = false;
        $quote = '';
        $len = strlen($s);
        $v = '';
        for ($i = $start + 1; $i < $len; $i++) {
            $ch = $s[$i];

            if (!$string && $ch == '}') {
                if ($v !== '' || !empty($return)) {
                    $return[] = $v;
                }
                $end = $i;
                break;
            } elseif (!$string && $ch == '{') {
                $v = self::to_php_array($s, $i, $i);
            } elseif (!$string && $ch == ',') {
                $return[] = $v;
                $v = '';
            } elseif (!$string && ($ch == '"' || $ch == "'")) {
                $string = true;
                $quote = $ch;
            } elseif ($string && $ch == $quote && $s[$i - 1] == "\\") {
                $v = substr($v, 0, -1) . $ch;
            } elseif ($string && $ch == $quote && $s[$i - 1] != "\\") {
                $string = false;
            } else {
                $v .= $ch;
            }
        }

        return $return;
    }

    /**
     * Remove an element from an array.
     *
     * @param string|int $element
     * @param array $array
     */
    public static function deleteElement($element, &$array)
    {
        $index = array_search($element, $array);
        if ($index !== false) {
            unset($array[$index]);
        }
    }

    public static function getCertainColumnsFromArray(array $data, array $columns)
    {
        $toRet = [];
        foreach ($columns as $info)
            if (isset($data[$info]))
                $toRet[$info] = $data[$info];
            else
                $toRet[$info] = null;
        return $toRet;
    }

    /**
     * Save file by url
     * @param $URL
     * @param $PATH
     *
     * @return $file
     */
    public static function downloadFile($URL, $PATH)
    {
        $ReadFile = fopen($URL, "rb");
        if ($ReadFile) {
            $WriteFile = fopen($PATH, "wb");
            if ($WriteFile) {
                while (!feof($ReadFile)) {
                    fwrite($WriteFile, fread($ReadFile, 4096));
                }
            }
            fclose($ReadFile);
            return $WriteFile;
        }
        return null;
    }

    public static function injectFieldsInInsert($sql, $params)
    {
        $values = 'VALUES (';
        $fields = '(';
        $first = true;
        foreach ($params as $field => $value) {
            if (!is_null($value)) {
                if ($first) {
                    $first = false;
                } else {
                    $values .= ', ';
                    $fields .= ', ';
                }
                if (is_string($value)) {
                    $values .= '\'' . $value . '\'';
                } else {
                    $values .= $value;
                }

                $fields .= $field;
            }
        }

        $fields .= ')';
        $values .= ')';

        $sql .= $fields;
        $sql .= ' ' . $values;

        return $sql;
    }

    public static function execute($sqlRequest, $params = null, $db = null)
    {
        if ($db == null)
            $db = DI::getDefault()->getDb();

        $query = $db->prepare($sqlRequest);
        try {
            $query->execute($params);
        } catch (\Exception $e) {
            SupportClass::writeMessageInLogFile("Ошибка: " . $e->getMessage());
            throw $e;
        }

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function executeQuery(QueryBuilder $builder, $db = null)
    {
        if ($db == null)
            $db = DI::getDefault()->getDb();

        $query = $db->prepare($builder->getSql());
        try {
            $query->execute($builder->getBind());
        } catch (\Exception $e) {
            SupportClass::writeMessageInLogFile("Ошибка: " . $e->getMessage());
            throw $e;
        }

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function executeWithPagination($sqlRequest, $params = null, $page = 1, $page_size = self::COMMON_PAGE_SIZE, $db = null)
    {
        if ($db == null)
            $db = DI::getDefault()->getDb();

        $page = filter_var($page, FILTER_VALIDATE_INT);
        $page = (!$page) ? 1 : $page;

        $page_size = filter_var($page_size, FILTER_VALIDATE_INT);
        $page_size = ($page_size===false) ? self::COMMON_PAGE_SIZE : $page_size;

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        if (is_string($sqlRequest)) {

            $sqlRequestReplaced = self::str_replace_once('from', ', count(*) OVER() AS total_count_pagination from', $sqlRequest);
            $sqlRequestReplaced .= '
                    LIMIT :limit 
                    OFFSET :offset';

            $query = $db->prepare($sqlRequestReplaced);
            //            SupportClass::writeMessageInLogFile("Query: \n".var_export($sqlRequestReplaced,true));
            $params_2 = [];
            if ($params != null)
                foreach ($params as $key => $data) {
                    $params_2[$key] = $data;
                }

            $params_2['limit'] = $page_size;
            $params_2['offset'] = $offset;

            try {
                //                SupportClass::writeMessageInLogFile("Исходный запрос: ".$sqlRequest);
                //                SupportClass::writeMessageInLogFile("Исполняемый запрос: ".$sqlRequestReplaced);
                //                SupportClass::writeMessageInLogFile("Params: \n".var_export($params_2,true));
                $query->execute($params_2);
            } catch (\Exception $e) {
                SupportClass::writeMessageInLogFile("Ошибка: " . $e->getMessage());
                throw $e;
            }

            $results = $query->fetchAll(\PDO::FETCH_ASSOC);

            //            SupportClass::writeMessageInLogFile("Results: \n".var_export($results,true));

            if (count($results) > 0) {
                $final_results = [];
                foreach ($results as $result) {
                    $final_result = [];
                    foreach ($result as $key => $data) {
                        if ($key != 'total_count_pagination') {
                            $final_result[$key] = $data;
                        }
                    }
                    $final_results[] = $final_result;
                }
                //                SupportClass::writeMessageInLogFile("Final results: \n".var_export($final_results,true));

                return ['pagination' => ['total' => $results[0]['total_count_pagination']], 'data' => $final_results];
            } else {
                //$sqlRequestReplaced = str_replace(["\r","\n"],' ',strtolower($sqlRequest));
                $sqlRequestReplaced = $sqlRequest;
                $sqlRequestReplaced = preg_replace(
                    "/select.*?from/is",
                    'select count(*) AS total_count_pagination from',
                    $sqlRequestReplaced,
                    1,
                    $count
                );

                $sqlRequestReplaced = preg_replace(
                    "/order by(?!(.|\n)*order by)(.|\n)*\z/im",
                    '',
                    $sqlRequestReplaced,
                    1,
                    $count
                );

                //                SupportClass::writeMessageInLogFile("Количество найденных замен order by: ".$count);

                $query = $db->prepare($sqlRequestReplaced);

                try {
                    $query->execute($params);
                } catch (\Exception $e) {
                    SupportClass::writeMessageInLogFile("Запрос, который вызвал ошибку: " . $sqlRequestReplaced);
                    //                    echo $e;
                    throw $e;
                }

                $results = $query->fetchAll(\PDO::FETCH_ASSOC);

                if (count($results) > 0)
                    return ['data' => [], 'pagination' => ['total' => $results[0]['total_count_pagination']]];
                else {
                    return ['data' => [], 'pagination' => ['total' => 0]];
                }
            }
        } else if (get_class($sqlRequest) == "App\Libs\Database\CustomQuery") {
            $sqlRequestReplaced = $sqlRequest->getCopy();

            if (!empty($sqlRequestReplaced->getDistinct())) {
                $overQuery = new CustomQuery([
                    'from'=> '('.$sqlRequestReplaced->getSql().') as tempDistinctTable',
                    'bind'=>$sqlRequestReplaced->getBind()
                ]);
                $sqlRequestReplaced = $overQuery;
            }

            $sqlRequestReplaced->addColumn('count(*) OVER() AS total_count_pagination');
            $sqlRequestReplaced->setLimit(':limit');
            $sqlRequestReplaced->setOffset(':offset');
            $sqlRequestReplaced->addBind(['limit'=>$page_size,'offset'=>$offset]);

            $results = self::executeQuery($sqlRequestReplaced);

            if (count($results) > 0) {
                $final_results = [];
                foreach ($results as $result) {
                    $final_result = [];
                    foreach ($result as $key => $data) {
                        if ($key != 'total_count_pagination') {
                            $final_result[$key] = $data;
                        }
                    }
                    $final_results[] = $final_result;
                }
                return ['pagination' => ['total' => $results[0]['total_count_pagination']], 'data' => $final_results];
            } else {
                $sqlRequestReplaced = $sqlRequest->getCopy();

                $sqlRequestReplaced->setOrder('');

                if (!empty($sqlRequestReplaced->getDistinct())) {
                    $overQuery = new CustomQuery([
                        'from'=> '('.$sqlRequestReplaced->getSql().') as tempDistinctTable',
                        'columns'=>'count(*)  AS total_count_pagination',
                        'bind'=>$sqlRequestReplaced->getBind()
                    ]);
                    $sqlRequestReplaced = $overQuery;
                } else {
                    $sqlRequestReplaced->setColumns('count(*) AS total_count_pagination');
                }
                $results = self::executeQuery($sqlRequestReplaced);

                if (count($results) > 0)
                    return ['data' => [], 'pagination' => ['total' => $results[0]['total_count_pagination']]];
                else {
                    return ['data' => [], 'pagination' => ['total' => 0]];
                }
            }

        } elseif (is_object($sqlRequest) && get_class($sqlRequest) == 'Phalcon\Mvc\Model\Query\Builder') {

            $sqlGotRequest = $sqlRequest;
            $sqlRequest->limit($page_size)
                ->offset($offset);

            $data = $sqlRequest->getQuery()->execute();

            $count = $sqlGotRequest->columns('count(*) as count')
                ->limit(null)
                ->offset(null)
                ->orderBy(null)
                ->getQuery()->execute();


            return ['data' => $data->toArray(), 'pagination' => ['total' => $count[0]->toArray()['count']]];
        } elseif (is_array($sqlRequest)) {
            $model = $sqlRequest['model'];

            unset($sqlRequest['model']);

            $sqlRequest['limit'] = $page_size;
            $sqlRequest['offset'] = $offset;

            if (isset($sqlRequest['deleted'])) {
                $deleted = $sqlRequest['deleted'];
                unset($sqlRequest['deleted']);
            }

            if (!is_null($deleted))
                $data = $model::find($sqlRequest, $deleted);
            else
                $data = $model::find($sqlRequest);

            unset($sqlRequest['limit']);
            unset($sqlRequest['offset']);
            unset($sqlRequest['order']);

            $sqlRequest['columns'] = 'count(*) as count';

            if (!is_null($deleted))
                $count = $model::find($sqlRequest, $deleted);
            else
                $count = $model::find($sqlRequest);

            return ['data' => $data->toArray(), 'pagination' => ['total' => $count[0]->toArray()['count']]];
        }

        return null;
    }

    public static function getCountForObjectByModel($model, $where_condition, $params)
    {
        $result = $model::findFirst([
            'columns' => 'count(*) as count', 'conditions' => $where_condition,
            'bind' => $params
        ]);
        /*$result = $model::findFirst(['columns'=>'count(*) as count',
            'conditions'=>'(publish_date > :publish_date:) OR (publish_date = :publish_date_2: AND news_id > :news_id:)',
            'bind'=>$params]);*/
        return $result['count'];
    }

    public static function getCountForObjectByQuery($from, $where_condition, $params)
    {
        if (strpos(strtolower($from), 'from') === false)
            $from = 'FROM ' . $from;
        if (strpos(strtolower($where_condition), 'where') === false)
            $where_condition = 'WHERE ' . $where_condition;
        $result_query = "SELECT count(*) " . $from . " " . $where_condition;

        $db = DI::getDefault()->getDb();

        $query = $db->prepare($result_query);
        $query->execute($params);

        $result = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $result[0]['count'];
    }

    public static function str_replace_once($search, $replace, $text)
    {
        $pos = stripos($text, $search);
        return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }

    public static function handleGroupOfObjectsWithFunction($objects, $function)
    {
        $handledObjects = [];
        if ($objects != null)
            foreach ($objects as $object) {
                $handledObjects[] = $function($object);
            }
        return $handledObjects;
    }

    /*public static function formQuery($query): string
    {
        if (!is_null($query['columns']))
            $sql_query = 'SELECT ' . $query['columns'];
        else
            $sql_query = 'SELECT *';

        $sql_query .= ' FROM ' . $query['from'];

        if (!empty($query['where']))
            $sql_query .= ' where ' . $query['where'];

        if (!is_null($query['order']))
            $sql_query .= ' order by ' . $query['order'];

        return $sql_query;
    }*/

    public static function handleLeftLowRightTopPoints(array $data)
    {
        if (!isset($data['low_left']) || !isset($data['high_right'])) {
            if (isset($data['diagonal']) || isset($data['center'])) {
                $data['high_right']['longitude'] = $data['diagonal']['longitude'];
                $data['high_right']['latitude'] = $data['diagonal']['latitude'];

                $diffLong = $data['diagonal']['longitude'] - $data['center']['longitude'];
                $data['low_left']['longitude'] = $data['center']['longitude'] - $diffLong;

                $diffLat = $data['diagonal']['latitude'] - $data['center']['latitude'];
                $data['low_left']['latitude'] = $data['center']['latitude'] - $diffLat;
            }
        }

        return $data;
    }

    public static function divideSorts($sort)
    {
        $sorts = explode('|', $sort);
        $sorts2 = [];
        foreach ($sorts as $sort) {
            //$sorts_temp = [];
            $sorts_temp = explode(' ', trim($sort));

            if (count($sorts_temp) == 2 && ($sorts_temp[1] == 'asc' || $sorts_temp[1] == 'desc')) {
                if ($sorts_temp[0] == 'price') {
                    if ($sorts_temp[1] == 'asc') {
                        $sorts_temp[0] = 'price_min';
                    } else {
                        $sorts_temp[0] = 'price_max';
                    }
                }
                $sorts2[$sorts_temp[0]] = ['field' => $sorts_temp[0], 'direction' => $sorts_temp[1]];
            }
        }

        return $sorts2;
    }

    public static function setTimeInLog($message)
    {

        if (self::$time != null) {
            $di = DI::getDefault();
            $di->getTime()->critical(
                'Разница: ' . (microtime(true) - self::$time) . ' message: ' . $message
            );
        }

        self::$time = microtime(true);
    }
}
