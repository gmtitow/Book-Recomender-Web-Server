<?php

namespace App\Middleware;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http401Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\DI\FactoryDefault as DI;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use App\Models\Accesstokens;

use App\Libs\SupportClass;

/**
 * CORSMiddleware
 *
 * CORS checking
 */
class JWTMiddleware implements MiddlewareInterface
{

    const ERROR_TOKEN_EXPIRED = 100;

    const access = [
        ROLE_MODERATOR => [ROLE_MODERATOR],
        ROLE_USER => [ROLE_MODERATOR, ROLE_USER],
        ROLE_GUEST => [ROLE_MODERATOR, ROLE_USER, ROLE_GUEST]
    ];

    public function getRoleByAccess($access){
        switch ($access) {
            case ACCESS_PUBLIC:
                {
                    $require_role = ROLE_GUEST;
                    break;
                }
            case ACCESS_PRIVATE:
                {
                    $require_role = ROLE_USER;
                    break;
                }
            case ACCESS_MODERATOR:
                {
                    $require_role = ROLE_MODERATOR;
                    break;
                }
            default:
                {
                    $require_role = ROLE_GUEST;
                    break;
                }
        }
        return $require_role;
    }

    /**
     * Before anything happens
     *
     * @param Event $event
     * @param Micro $application
     *
     * @returns bool
     */
    public function beforeHandleRoute(Event $event, Micro $application)
    {
        $di = DI::getDefault();
        $tokenRecieved = self::getTokenFromHeader();
        $query = $_SERVER['REQUEST_URI'];
        if ($query[0] == '/')
            $query = substr($query, 1);

        $matches = explode('/', $query);

        SupportClass::writeMessageInLogFile("query: " . $query);
        SupportClass::writeMessageInLogFile("first match: " . $matches[0]);

        if (count($matches) > 1) {

            $match = $matches[1];

            switch ($match) {
                case ACCESS_PUBLIC:
                    {
                        $require_role = ROLE_GUEST;
                        break;
                    }
                case ACCESS_PRIVATE:
                    {
                        $require_role = ROLE_USER;
                        break;
                    }
                case ACCESS_MODERATOR:
                    {
                        $require_role = ROLE_MODERATOR;
                        break;
                    }
                default:
                    {
                        $match = $matches[0];
                        if ($match != VERSION) {
                            $require_role = $this->getRoleByAccess($match);
                        } else
                            $require_role = ROLE_GUEST;
                        break;
                    }
            }
        } elseif (count($matches) > 0) {
            $require_role = $this->getRoleByAccess($matches[0]);
        } else
            $require_role = ROLE_GUEST;

        $check = false;

        if ($tokenRecieved != null) {
            $check = $di->getAuthService()->checkToken($tokenRecieved);

            if (!$check)
                throw new Http400Exception("Invalid token");
        }

        $info = false;
        if ($check)
            $info = json_decode($check, true);

        SupportClass::writeMessageInLogFile("Результат проверки токена: " . ($check ? "положительный" : "отрицательный"));

        if (!$info) {
            SupportClass::writeMessageInLogFile("require role: " . $require_role);
            if ($require_role != ROLE_GUEST) {
                SupportClass::writeMessageInLogFile("return 401 exception");
                throw new Http401Exception("Missing token in \"Authorization\" header");
            }
        } else {
            if (strtotime($info['lifetime']) <= time()) {
                throw new Http400Exception("Token expired", self::ERROR_TOKEN_EXPIRED);
            } else {

                if (!in_array($info['role'], self::access[$require_role]))
                    throw new Http403Exception("Not enough rights");

                $di->getAuthService()->_registerSessionByData($info, $application);
            }
        }
    }

    public static function getTokenFromHeader()
    {
        if (!function_exists('getallheaders')) {
            function getallheaders()
            {
                if (!is_array($_SERVER)) {
                    return array();
                }

                $headers = array();
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                return $headers;
            }

        }

        $tokenRecieved = null;
        try {
            $result = getallheaders();
        } catch (Exception $e) {
        }

        if (isset($result['Authorization'])) {
            $tokenRecieved = $result['Authorization'];
        } elseif (isset($result['authorization']))
            $tokenRecieved = $result['authorization'];


        return $tokenRecieved;
    }

    /**
     * Calls the middleware
     *
     * @param Micro $application
     *
     * @returns bool
     */
    public function call(Micro $application)
    {
        return true;
    }
}



