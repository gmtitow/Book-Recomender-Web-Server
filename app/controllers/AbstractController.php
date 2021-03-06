<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Libs\SupportClass;
use App\Models\AbstractModel;
use App\Models\Products;
use App\Services\CommonService;
use App\Services\AccountService;
use Phalcon\DI\FactoryDefault as DI;

use App\Models\Accounts;
/**
 * Class AbstractController
 *
 * @property \Phalcon\Http\Request $request
 * @property \Phalcon\Http\Response $htmlResponse
 * @property \Phalcon\Db\Adapter\Pdo\Postgresql $db
 * @property \Phalcon\Config $config
 * @property \App\Services\UsersService $usersService
 * @property \App\Models\Users $user
 */
abstract class AbstractController extends \Phalcon\DI\Injectable
{
    /**
     * @var AbstractController
     */
    private static $instance;

    /**
     * Route not found. HTTP 404 Error
     */
    const ERROR_NOT_FOUND = 1;

    /**
     * Invalid Request. HTTP 400 Error.
     */
    const ERROR_INVALID_REQUEST = 2;

    /**
     * Validation errors in input parameters
     */

    /**
     * Parameter missing, but required
     */
    const ERROR_MISSING_PARAMETER = 101;
    const ERROR_NOT_IN_VALID_LIST = 102;
    const ERROR_TOO_SMALL = 103;
    const ERROR_TOO_MUCH = 104;
    const ERROR_LENGTH_TOO_MUCH = 105;
    const ERROR_LENGTH_TOO_SMALL = 106;

    /**
     * Format settings names
     */
    const LAST_COMMENT = 'last-comment';

    /**
     * Global success response format
     */
    public function chatResponce($msg, $data = null)
    {
        return ['success' => true, 'msg' => $msg, 'data' => $data];
    }

    public function successResponse($msg, $data = null)
    {
        return ['success' => true, 'msg' => $msg, 'data' => $data];
    }

    public function successPaginationResponse($msg, $data = null, $pagination = null)
    {
        if (!is_null($pagination)) {
            if (is_integer($pagination))
                return ['success' => true, 'msg' => $msg, 'pagination' => ['total' => $pagination], 'data' => $data];
            elseif (is_array($pagination)) {
                return ['success' => true, 'msg' => $msg, 'pagination' => $pagination, 'data' => $data];
            }
        }
        return ['success' => true, 'msg' => $msg, 'data' => $data];
    }

    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @return AbstractController
     */
    public static function getInstance() {
        return self::$instance;
    }

    public function isAuthorized()
    {
        $payload = $this->session->get('auth');
        return $payload != null && $payload['id'] != null;
    }

    public function getUserId()
    {
        $payload = $this->session->get('auth');
        $current_user_id = $payload['id'];
        return $current_user_id;
    }

    public function setAccountId($accountId)
    {
        $this->session->set('accountId', $accountId);
    }

    public function setCurrentAccount(Accounts $account, $setId = true)
    {
        $this->session->set('account', $account);

        if ($setId) {
            $this->session->set('accountId', $account->getId());
        }
    }

    public static function getAccount()
    {
        $di = DI::getDefault();
        return $di->getSession()->get('account');
    }

    /**
     * To send in the common area formatting settings
     *
     * Possible settings:
     *      last-comment
     */
    public static function setFormatSetting($setting, $value)
    {
        $di = DI::getDefault();
        $di->getSession()->set('format', [$setting => $value]);
    }

    public static function getFormatSetting($setting)
    {
        $di = DI::getDefault();
        return $di->getSession()->get('format')[$setting];
    }

    public static function unsetFormatSetting($setting)
    {
        $di = DI::getDefault();
        $di->getSession()->unset('format', $setting);
    }

    public static function getAccountId()
    {
        $di = DI::getDefault();
        return $di->getSession()->get('accountId');
    }

    public static function returnPermissionException()
    {
        throw new Http403Exception('Permission error');
    }

    public function checkErrors($errors, $code = null)
    {
        if (!is_null($errors) && is_array($errors) && count($errors) > 0) {
            $errors['errors'] = true;
            $exception = new Http400Exception('Some parameters are invalid', $code);
            throw $exception->addErrorDetails($errors);
        }
    }

    protected function putPageAndPageSizeInData(array &$data, array $inputData = null, $defPageSize = SupportClass::COMMON_PAGE_SIZE)
    {
        $data['page'] = $inputData['page'];
        $data['page_size'] = $inputData['page_size'];

        $data['page'] = filter_var($data['page'], FILTER_VALIDATE_INT);
        $data['page'] = (!$data['page']) ? 1 : $data['page'];

        $data['page_size'] = filter_var($data['page_size'], FILTER_VALIDATE_INT);
        $data['page_size'] = ($data['page_size'] === false) ? $defPageSize : $data['page_size'];

        return $data;
    }

    protected function addError(
        string $param_name,
        array &$errors = null,
        int $error_type = self::ERROR_MISSING_PARAMETER,
        $added_data = null
    ) {
        if ($errors == null) {
            $errors = [];
        }

        switch ($error_type) {
            case self::ERROR_NOT_IN_VALID_LIST:
                if (count($added_data) > 0) {
                    $errors[$param_name] = 'Parameter \'' . $param_name . '\' must be one of the following values: ';
                    $first = true;
                    foreach ($added_data as $value) {
                        if ($first) {
                            $first = false;
                        } else {
                            $errors[$param_name] .= ', ';
                        }
                        if (is_string($value))
                            $errors[$param_name] .= "'$value'";
                        else
                            $errors[$param_name] .= "$value";
                    }
                } else {
                    $val = $added_data[0];
                    if (is_string($val))
                        $val .= "'$val'";
                    $errors[$param_name] = 'Parameter \'' . $param_name . '\' must be value: ' . $val;
                }
                break;
            case self::ERROR_TOO_MUCH: {
                    $errors[$param_name] = 'Parameter \'' . $param_name . '\' must be '.$added_data.' or less';
                    break;
                }
            case self::ERROR_TOO_SMALL: {
                    $errors[$param_name] = 'Parameter \'' . $param_name . '\' must be '.$added_data.' or greater';
                    break;
                }
            case self::ERROR_LENGTH_TOO_MUCH: {
                    $errors[$param_name] = 'The length of the parameter \'' . $param_name . '\' must be less than ' . $added_data;
                    break;
                }
            case self::ERROR_LENGTH_TOO_SMALL: {
                    $errors[$param_name] = 'The length of the parameter \'' . $param_name . '\' must be greater than ' . $added_data;
                    break;
                }
            case self::ERROR_MISSING_PARAMETER:
            default: {
                    $errors[$param_name] = 'Missing required parameter \'' . $param_name . '\'';
                    break;
                }
        }

        return $errors;
    }

    private function handleTypeVar($name, $value, $info, array &$all_values = null)
    {
        $handledValue = null;
        $errors = [];
        $found = false;

        if (is_string($info))
            $info = array('type' => $info);

        $types = [];

        if (isset($info['type'])) {
            if (is_array($info['type'])) {
                $types = $info['type'];
            } else {
                $types[] = $info['type'];
            }
        } else {
            $types[] = 'array';
        }

        foreach ($types as $type) {

            if ($found)
                break;

            // if (empty($type))
            //     $type = 'array';

            switch (strtolower($type)) {
                case 'integer':
                case 'int':
                case 'uint':
                case 'unsigned int': {
                        $val = filter_var($value, FILTER_VALIDATE_INT);
                        if ($val !== false) {

                            if (!isset($info['max']))
                                $info['max'] = 2147483648;

                            if ($this->checkNumeric($info, $val, $name, $errors))
                                $handledValue = $val;
                            $found = true;
                        }
                        break;
                    }
                case 'bigint': {
                        $val = filter_var($value, FILTER_VALIDATE_INT);
                        if ($val !== false) {
                            if ($this->checkNumeric($info, $val, $name, $errors))
                                $handledValue = $val;
                            $found = true;
                        }
                        break;
                    }
                case 'float':
                case 'double':
                    $val = filter_var($value, FILTER_VALIDATE_FLOAT);
                    if ($val !== false) {
                        if ($this->checkNumeric($info, $val, $name, $errors))
                            $handledValue = $val;
                        $found = true;
                    }
                    break;
                case 'boolean':
                case 'bool':
                    $handledValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    $found = true;
                    break;
                case 'char':
                case 'string':
                    if (is_string($value)) {
                        $val = strval($value);
                        if ($this->checkString($info, $val, $name, $errors))
                            $handledValue = $val;
                        $found = true;
                    }
                    break;
                case 'arr':
                case 'array':
                    if (is_array($value)) {
                        if (isset($info['sub_data'])) {
                            if (!(isset($info['type']) && $info['type'] = "array")) {
                                foreach ($info['sub_data'] as $sub_name => $sub_info) {
                                    $res = $this->handleVariable($sub_name, $value[$sub_name], $sub_info, $all_values);
                                    if (!is_null($res['value'])) {
                                        $handledValue[$sub_name] = $res['value'];
                                    }
                                    if (count($res['errors']) > 0) {
                                        foreach ($res['errors'] as $sub_name_err => $error) {
                                            $errors[$sub_name_err] = $error;
                                        }
                                    }
                                }
                            } else {
                                if ($this->checkArray($info, $value, $name, $errors)) {
                                    foreach ($value as $key => $val) {
                                        if (isset($info['sub_data']['type']) && count($info['sub_data']) == 1) {
                                            $new_info = $info['sub_data'];
                                        } else {
                                            $new_info = ['sub_data' => $info['sub_data']];
                                        }
                                        $res = $this->handleVariable($key, $val, $new_info, $all_values);

                                        if ($res['value'] != null) {
                                            $handledValue[$key] = $res['value'];
                                        }
                                        if (count($res['errors']) > 0) {
                                            foreach ($res['errors'] as $sub_name_err => $error) {
                                                $errors[$name]['_' . $key][$sub_name_err] = $error;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;
                case 'file':
                case 'files': {
                        $values = $this->request->getUploadedFiles();

                        foreach ($values as $image) {
                            if ($image->getError() == 0){
                                $handledValue[] = $image;
                            }
                        }
                        break;
                    }
                default: {
                        $handledValue = $value;
                        $found = true;
                    }
            }
        }

        return ['value' => $handledValue, 'errors' => $errors];
    }

    private function checkNumeric($info, $val, $name, &$errors)
    {
        if (isset($info['max']) && $val > $info['max']) {
            $errors = $this->addError($name, $errors, self::ERROR_TOO_MUCH, $info['max']);
            return false;
        }

        if (isset($info['min']) && $val < $info['min']) {
            $errors = $this->addError($name, $errors, self::ERROR_TOO_SMALL, $info['min']);
            return false;
        }

        return true;
    }

    private function checkString($info, $val, $name, &$errors)
    {
        if (isset($info['max_length']) && mb_strlen($val) > $info['max_length']) {
            $errors = $this->addError($name, $errors, self::ERROR_LENGTH_TOO_MUCH, $info['max_length']);
            return false;
        }
        if (isset($info['min_length']) && mb_strlen($val) < $info['min_length']) {
            $errors = $this->addError($name, $errors, self::ERROR_LENGTH_TOO_SMALL, $info['min_length']);
            return false;
        }

        return true;
    }

    private function checkArray($info, $val, $name, &$errors)
    {
        if (isset($info['max_length']) && count($val) > $info['max_length']) {
            $errors = $this->addError($name, $errors, self::ERROR_LENGTH_TOO_MUCH, $info['max_length']);
            return false;
        }
        if (isset($info['min_length']) && count($val) < $info['min_length']) {
            $errors = $this->addError($name, $errors, self::ERROR_LENGTH_TOO_SMALL, $info['min_length']);
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @param $value
     * @param $info array => {
     *                      type => string (int) | array,
     *                      is_require => bool (false),
     *                      default => :type (null),
     *                      sub_data => :{expectation type}, - for arrays
     *                      valid_list => array [:type]|null (null),
     *                      reference => string|null (null) - other parameter name
     *                  }
     *         | string (type)
     * @param array &$all_values
     * @return array { value=>:type|null, errors => array [{:name => string}] }
     */
    private function handleVariable(string $name, $value, $info, array &$all_values = null)
    {
        $found = false;
        $handledValue = null;
        $errors = [];
        if (!is_null($value) || $info === 'files' || (isset($info['type']) && ($info['type'] === 'files' || $info['type'] === 'file'))) {
            $res = $this->handleTypeVar($name, $value, $info, $all_values);
            $handledValue = $res['value'];

            if ($handledValue !== null || $res['errors'] !== null)
                $found = true;

            $errors = $res['errors'];
        }

        if (!is_null($handledValue))
            $found = true;

        if (!$found) {
            if (isset($info['reference'])) {
                if (isset($all_values[$info['reference']])) {
                    $res = $this->handleTypeVar($name, $all_values[$info['reference']], $info);

                    $handledValue = $res['value'];

                    if ($handledValue != null)
                        $found = true;

                    $errors = $res['errors'];
                }
            }
        }

        if (isset($info['default']) && !$found) {
            $handledValue = $info['default'];
            $found = true;
        }

        if ($handledValue !== null && isset($info['valid_list']) && !in_array($handledValue, $info['valid_list'], true)) {
            $handledValue = null;
            $errors = $this->addError($name, $errors, self::ERROR_NOT_IN_VALID_LIST, $info['valid_list']);
        }

        if (isset($info['is_require']) && $info['is_require'] === true && !$found) {
            $errors = $this->addError($name, $errors);
        }

        return ['value' => $handledValue, 'errors' => $errors];
    }

    /**
     * @param string $method = {'GET', 'POST', 'PUT', 'DELETE'}
     * @param array $expectations [
     *                             'name' => {
     *                                  type => string (int),
     *                                  is_require => bool (false),
     *                                  default => :type (null),
     *                                  sub_data => :{expectation type}, - for arrays
     *                                  reference => string - other parameter name,
     *                                  ...
     *                              },
     *                              ...
     *                           ]
     * Possible parameters:
     *      - type
     *      - is_require
     *      - default
     *      - sub_data,
     *      - reference,
     *      - max - for numeric types,
     *      - min - for numeric types,
     *      - max_length - for string
     *      - min_length - for string
     *
     * @param array $added_data = null - data from other sources
     * @param bool $is_form_data = false
     * @param bool $added_data_first = false
     *
     * @return array
     */
    protected function getInput(
        string $method,
        array $expectations,
        array $added_data = null,
        $is_form_data = false,
        $added_data_first = false
    ) {
        switch (strtoupper($method)) {
            case 'GET': {
                    $inputData = $this->request->getQuery();
                    break;
                }
            case 'POST': {
                    if ($is_form_data === true) {
                        $inputData = $this->request->getPost();
                    } else {
                        $inputData = json_decode($this->request->getRawBody(), true);
                    }
                    break;
                }
            case 'PUT': {
                    $inputData = json_decode($this->request->getRawBody(), true);
                    break;
                }
            case 'DELETE': {
                    $inputData = json_decode($this->request->getRawBody(), true);
                    if ($inputData === null)
                        $inputData = [];
                    $inputData = array_merge($this->request->getQuery(), $inputData);
                    break;
                }
            default: {
                    throw new Http500Exception("method $method not supported in method getInput");
                }
        }

        if (is_null($added_data))
            $added_data = [];

        if (is_null($inputData))
            $inputData = [];

        if ($added_data_first)
            $inputData = array_merge($inputData, $added_data);
        else {
            $inputData = array_merge($added_data, $inputData);
        }

        $data = [];
        $errors = [];
        //        foreach ($expectations as $name=>$info) {
        //            $res = $this->handleVariable($name,$inputData[$name],$info);
        //            if($res['value']!=null) {
        //                $data[$name] = $res['value'];
        //            }
        //            if(count($res['errors'])>0) {
        //                foreach ($res['errors'] as $name_err=>$error) {
        //                    $errors[$name_err] = $error;
        //                }
        //            }
        //        }

        $res = $this->handleVariable(
            'some',
            $inputData,
            [
                'sub_data' => $expectations
            ],
            $inputData
        );

        if (count($res['errors']) > 0) {
            $errors = $res['errors'];
            if (count($errors) > 0) {
                self::checkErrors($errors);
            }
        }

        $data = $res['value'];

        return $data;
    }
}
