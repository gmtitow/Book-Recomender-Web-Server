<?php

namespace App\Controllers;

use App\Libs\SimpleULogin;
use App\Libs\SocialAuther\Adapter\Facebook;
use App\Libs\SocialAuther\Adapter\Google;
use App\Libs\SocialAuther\Adapter\Instagram;
use App\Models\Accounts;
use App\Models\UsersSocial;
use App\Services\AbstractService;
use App\Services\AccountService;
use App\Services\CityService;
use App\Services\ImageService;
use App\Services\SocialNetService;
use App\Services\UserInfoService;
use Phalcon\Http\Client\Exception;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

use App\Libs\SupportClass;

use App\Models\Phones;
use App\Models\Accesstokens;
use App\Models\Users;

use App\Services\UserService;
use App\Services\AuthService;

use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http404Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;

use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use ULogin\Auth;
use App\Libs\SocialAuther\Adapter\Vk;
use App\Libs\SocialAuther\SocialAuther;

/**
 * Class SessionAPIController
 * Контроллер, предназначеный для авторизации пользователей и содержащий методы сязанные с этим процессом
 * А именно, методы для авторизации пользователя, разрыва сессии, получение роли текущего пользователя
 * и авторизация через соц. сеть (которая по совместительству и регистрация).
 *
 * @url authentication
 */
class SessionAPIController extends AbstractController
{
    /**
     * Разрывает сессию пользователя
     * @method POST
     *
     * @return string - json array Status
     */
    /*public function endAction()
    {
        return $this->destroySession();
    }*/

    /**
     * Выдает текущую роль пользователя.
     *
     * @url /get/role
     *
     * @method GET
     * @access public
     */
    public function getCurrentRoleAction()
    {
        $auth = $this->session->get('auth');

        try {
            if ($auth == null) {
                $role = ROLE_GUEST;
            } else {
                $userId = $auth['id'];

                $user = $this->userService->getUserById($userId);

                $role = $user->getRole();
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('', ['role' => $role]);
    }

    /**
     * Авторизует пользователя в системе
     *
     * @url login
     *
     * @method POST
     * @access public
     *
     * @params login
     * @params password
     * @return array
     */
    public function indexAction()
    {
        $expectation = [
            'login' => [
                'type'=>'string',
                'is_require' => true
            ],
            'password' => [
                'type'=> 'string',
                'is_require' => true
            ]
        ];

        $data = self::getInput('POST',$expectation);
//        $data = json_decode($this->request->getRawBody(), true);
//
//        $errors = null;
//        if (empty($data['login'])) {
//            $errors['login'] = 'Missing required parameter \'login\'';
//        }
//
//        if (empty($data['password'])) {
//            $errors['password'] = 'Missing required parameter \'password\'';
//        }
//
//        if (!is_null($errors)) {
//            $errors['errors'] = true;
//            $exception = new Http400Exception('Invalid some parameters', self::ERROR_INVALID_REQUEST);
//            throw $exception->addErrorDetails($errors);
//        }

        try {
            $user = $this->userService->getUserByLogin(trim($data['login']));
            SupportClass::writeMessageInLogFile('email пользователя ' . $user->getEmail());
            SupportClass::writeMessageInLogFile('Юзер найден в бд');
            $this->authService->checkPassword($user, trim($data['password']));
            $result = $this->authService->createSession($user);
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http400Exception('Incorrect password or login',
                        AuthService::ERROR_INCORRECT_PASSWORD, $e);
                case AuthService::ERROR_INCORRECT_PASSWORD:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return self::successResponse('Successfully login', $result);
    }
}