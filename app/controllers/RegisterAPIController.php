<?php

namespace App\Controllers;

use App\Models\Userinfo;
use App\Services\AbstractService;
use App\Services\CityService;
use App\Services\ResetPasswordService;
use App\Services\SettingsService;
use App\Services\SubjectService;
use Dmkit\Phalcon\Auth\Auth;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

use App\Libs\SupportClass;
use App\Libs\PHPMailerApp;

//Models
use App\Models\Phones;
use App\Models\Accounts;
use App\Models\Users;
use App\Models\ActivationCodes;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

//Services
use App\Services\UserService;
use App\Services\UserInfoService;
use App\Services\AccountService;
use App\Services\AuthService;

/**
 * Class RegisterAPIController
 * Контроллер для регистрации пользователей.
 * Содержит методы для регистрации пользователя и работы с активационным кодом.
 * На данный момент это касается только активационного кода через email.
 *
 * @url authentication
 */
class RegisterAPIController extends AbstractController
{
    /**
     * Регистрирует пользователя в системе
     *
     * @url register
     *
     * @access public
     * @method POST
     *
     * @params login
     * @params password
     * @params activation_code
     *
     * @return array. Если все прошло успешно - [status, token, lifetime (время, после которого токен будет недействительным)],
     * иначе [status,errors => <массив сообщений об ошибках>]
     */
    public function indexAction()
    {
        $inputData = json_decode($this->request->getRawBody(), true);

        $data['login'] = $inputData['login'];
        $data['password'] = $inputData['password'];
        $data['activation_code'] = $inputData['activation_code'];

        $errors = null;
        if(empty($data['login']))
            $this->addError('login',$errors);

        if(empty($data['password']))
            $this->addError('password',$errors);

        if(empty($data['activation_code']))
            $this->addError('activation_code',$errors);

        $this->checkErrors($errors);

        $this->db->begin();

        $checking = $this->authService->checkActivationCode($data['activation_code'], $data['login']);
        if ($checking == AuthService::WRONG_ACTIVATION_CODE)
            $errors['activation_code'] = 'Wrong activation code';

        $this->checkErrors($errors);
        if ($checking == AuthService::RIGHT_ACTIVATION_CODE) {
            $this->authService->deleteActivationCode($data['login']);
        } elseif ($checking == AuthService::RIGHT_DEACTIVATION_CODE) {
            $this->authService->deleteActivationCode($data['login']);

            $this->db->commit();
            return $this->successResponse('Activation code was successfully deactivated');
        } else {
            throw new ServiceException(_('Internal Server Error'));
        }

        if (strlen($data['password']) < 6) {
            $errors['password'] = 'password too few';
        }

        $this->checkErrors($errors);

        try {
            $data['role'] = ROLE_USER;
            $data['activated'] = true;

            $resultUser = $this->userService->createUser($data);

            $tokens = $this->authService->createSession($resultUser);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AccountService::ERROR_UNABLE_CREATE_ACCOUNT:
                case UserService::ERROR_UNABLE_CREATE_USER:
                case AuthService::ERROR_UNABLE_SEND_TO_MAIL:
                case AuthService::ERROR_UNABLE_TO_CREATE_ACTIVATION_CODE:
                case AbstractService::ERROR_UNABLE_SEND_TO_MAIL:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('All ok', $tokens);
    }

    /**
     * Проверяет, подходит ли логин для регистрации нового пользователя
     *
     * @url /check/login
     *
     * @access public
     * @method POST
     *
     * @params login
     *
     * @return string json array Status
     */
    public function checkLoginAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        $checking = $this->authService->checkLogin($data['login']);

        if ($checking == AuthService::LOGIN_INCORRECT) {
            $errors['login'] = 'Invalid login';
        } elseif ($checking == AuthService::LOGIN_EXISTS) {
            $errors['login'] = 'User with same login already exists';
        }

        if (!is_null($errors)) {
            $errors['errors'] = true;
            $exception = new Http400Exception(_('Invalid some parameters'), $checking);
            throw $exception->addErrorDetails($errors);
        }

        return self::successResponse('All ok');
    }

    /**
     * Проверяет активационный код
     *
     * @url check/activation-code
     *
     * @access public
     * @method POST
     *
     * @params activation_code
     * @params login
     *
     * @return array
     */
    public function checkActivationCodeAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['activation_code'] = $inputData->activation_code;
        $data['login'] = $inputData->login;

        $errors = null;
        if(empty($data['login']))
            $this->addError('login',$errors);

        if(empty($data['activation_code']))
            $this->addError('activation_code',$errors);

        $this->checkErrors($errors);
        try {
            $code = $this->authService->getActivationCode($data['login']);

            if (!$code) {
                $errors['activation_code'] = 'Wrong activation code';
                $this->checkErrors($errors);
            }

            $checking = $this->authService->checkActivationCode($data['activation_code'], $data['login']);
            if ($checking == AuthService::WRONG_ACTIVATION_CODE) {
                $errors['activation_code'] = 'Wrong activation code';
                $this->authService->increaseCheckCount($code);
                $this->checkErrors($errors);
            }

            if ($checking == AuthService::RIGHT_ACTIVATION_CODE) {
                return $this->successResponse('Activation code is right');
            } elseif ($checking == AuthService::RIGHT_DEACTIVATION_CODE) {
                $this->authService->deleteActivationCode($data['login']);

                return $this->successResponse('Activation code was successfully deactivated');
            } else {
                throw new ServiceException(_('Internal Server Error'));
            }
        } catch(ServiceExtendedException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch(ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Подтверждает, что пользователь - владелец (пока только) почты.
     *
     * @deprecated
     * @access public
     * @method POST
     *
     * @params activation_code, login
     *
     * @return Status
     */
    public function activateLinkAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['activation_code'] = $inputData->activation_code;
        $data['login'] = $inputData->login;

        if (empty(trim($data['login']))) {
            $errors['login'] = 'Required login';
        }

        $user = Users::findByLogin($data['login']);

        $errors = [];
        if (!$user) {
            $errors['login'] = 'User with this login don\'t exists';
        }

        if ($user->getActivated()) {
            $errors['login'] = 'User already activate';
        }

        $checking = $this->authService->checkActivationCode($data['activation_code'], $user->getUserId());
        if ($checking == AuthService::WRONG_ACTIVATION_CODE) {
            $errors['activation_code'] = 'Wrong activation code';

            $code = $this->authService->getActivationCode($data['login']);
            $this->authService->increaseCheckCount($code);
        }

        $this->checkErrors($errors);

        $this->db->begin();
        try {
            if ($checking == AuthService::RIGHT_ACTIVATION_CODE) {
                $this->authService->deleteActivationCode($user->getUserId());
                $this->userService->changeUser($user, ['role' => ROLE_USER_DEFECTIVE, 'activated' => true]);
            } elseif ($checking == AuthService::RIGHT_DEACTIVATION_CODE) {
                $this->userService->deleteUser($user->getUserId());
            } else {
                throw new ServiceException(_('Internal Server Error'));
            }

            $res = $this->authService->createSession($user);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case AuthService::ERROR_UNABLE_DELETE_ACTIVATION_CODE:
                case UserService::ERROR_UNABLE_DELETE_USER:
                case UserService::ERROR_UNABLE_CHANGE_USER:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
        }


        $this->db->commit();

        return self::successResponse('User was successfully activated', $res);
    }

    /**
     * Отправляет активационный код
     *
     * @url /get/activation-code
     *
     * @access public
     * @method POST
     *
     * @params login
     *
     * @return Response - json array в формате Status
     */
    public function getActivationCodeAction()
    {
        $inputData = $this->request->getJsonRawBody();

        $data['login'] = $inputData->login;
        $errors = null;
        if(empty($data['login']))
            $this->addError('login',$errors);

        $this->checkErrors($errors);

        try {
            $this->authService->sendActivationCode($data['login']);
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AuthService::ERROR_UNABLE_SEND_TO_MAIL:
                case AuthService::ERROR_UNABLE_TO_CREATE_ACTIVATION_CODE:
                case AuthService::ERROR_NO_TIME_TO_RESEND:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case AuthService::ERROR_USER_ALREADY_ACTIVATED:
                case AuthService::ERROR_UNABLE_SEND_ACTIVATION_CODE:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
        return self::successResponse('Activation code successfully sent');
    }

    /**
     * Отправляет пользователю код для сброса пароля
     *
     * @url /get/resetPasswordCode
     *
     * @method POST
     * @access public
     *
     * @params login
     *
     * @return Status
     */
    public function getResetPasswordCodeAction()
    {
        $data = json_decode($this->request->getRawBody(), true);
        $user = Users::findByLogin($data['login']);

        if (!$user || $user == null) {
            $errors['login'] = 'Invalid login';
        }

        if (!is_null($errors)) {
            $errors['errors'] = true;
            $exception = new Http400Exception(_('Invalid some parameters'), self::ERROR_INVALID_REQUEST);
            throw $exception->addErrorDetails($errors);
        }

        //Пока, если код существует, то просто перезаписывается
        try {
            $this->resetPasswordService->sendPasswordResetCode($user);
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case AbstractService::ERROR_UNABLE_SEND_TO_MAIL:
                case ResetPasswordService::ERROR_UNABLE_TO_CREATE_RESET_PASSWORD_CODE:
                case ResetPasswordService::ERROR_NO_TIME_TO_RESEND:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Code for reset password successfully sent');
    }

    /**
     * Проверяет, верен ли код для сброса пароля
     *
     * @url /check/resetPasswordCode
     *
     * @access public
     * @method POST
     *
     * @params login
     * @params reset_code
     *
     * @return Status
     */
    public function checkResetPasswordCodeAction()
    {
        $data = json_decode($this->request->getRawBody(), true);
        try {
            $user = $this->userService->getUserByLogin($data['login']);

            $checking = $this->resetPasswordService->checkResetPasswordCode($user, $data['reset_code']);

            if ($checking == ResetPasswordService::RIGHT_DEACTIVATE_PASSWORD_RESET_CODE) {
                $this->resetPasswordService->deletePasswordResetCode($user->getUserId());
                return self::successResponse('Request to change password successfully canceled');
            }

            if ($checking == ResetPasswordService::WRONG_PASSWORD_RESET_CODE) {
                $exception = new Http400Exception(_('Invalid some parameters'));
                $errors['errors'] = true;
                $errors['reset_code'] = 'Invalid reset code';
                throw $exception->addErrorDetails($errors);
            }

            if ($checking == ResetPasswordService::RIGHT_PASSWORD_RESET_CODE) {
                return self::successResponse('Code is valid');
            }

            throw new Http500Exception(_('Internal Server Error'));

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case ResetPasswordService::ERROR_UNABLE_DELETE_RESET_PASSWORD_CODE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Меняет пароль, если активационный код верен
     *
     * @url /change/password
     *
     * @access public
     * @method POST
     *
     * @params login
     * @params resetcode
     * @params password
     *
     * @return string - json array Status
     */
    public function changePasswordAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        try {
            $user = $this->userService->getUserByLogin($data['login']);

            $checking = $this->resetPasswordService->checkResetPasswordCode($user, $data['reset_code']);

            if ($checking == ResetPasswordService::WRONG_PASSWORD_RESET_CODE) {
                $exception = new Http400Exception(_('Invalid some parameters'));
                $errors['errors'] = true;
                $errors['reset_code'] = 'Invalid reset code';
                throw $exception->addErrorDetails($errors);
            }

            if ($checking == ResetPasswordService::RIGHT_PASSWORD_RESET_CODE) {
                $this->userService->setPasswordForUser($user, $data['password']);
                $this->resetPasswordService->deletePasswordResetCode($user->getUserId());
                return self::successResponse('Password was changed successfully');
            }

            throw new Http500Exception(_('Internal Server Error'));
        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_UNABLE_CHANGE_USER:
                case ResetPasswordService::ERROR_UNABLE_DELETE_RESET_PASSWORD_CODE:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case UserService::ERROR_USER_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Проверяет, существует ли уже никнейм
     *
     * @url /check/nickname
     *
     * @method POST
     * @access public
     *
     * @params nickname
     *
     * @return array (bool nickname_exists)
     */
    public function checkNicknameAction()
    {
        $inputData = $this->request->getJsonRawBody();
        $data['nickname'] = $inputData->nickname;

        if (!isset($data['nickname'])) {
            $errors['nickname'] = 'Missing required parameter "nickname"';
        }

        if ($errors != null) {
            $exception = new Http400Exception(_('Invalid some parameters'));
            $errors['errors'] = true;
            throw $exception->addErrorDetails($errors);
        }

        $exists = $this->userInfoService->checkNicknameExists($data['nickname']);

        return self::successResponse('', ['nickname_exists' => $exists]);
    }
}

