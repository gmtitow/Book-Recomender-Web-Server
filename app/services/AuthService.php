<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\ActivationCodes;

use App\Models\BookLists;
use App\Models\UsersSocial;
use Phalcon\DI\FactoryDefault as DI;

//Models
use App\Models\Users;
use App\Models\Phones;
use App\Models\PasswordResetCodes;

use App\Libs\SupportClass;
use App\Libs\PHPMailerApp;
/**
 * business and other logic for authentication. Maybe just creation simple objects.
 *
 * Class UsersService
 */
class AuthService extends AbstractService
{
    const LOGIN_EXISTS = 1;
    const LOGIN_DO_NOT_EXISTS = 0;
    const LOGIN_INCORRECT = 2;


    const WRONG_ACTIVATION_CODE = 1;
    const RIGHT_ACTIVATION_CODE = 0;
    const RIGHT_DEACTIVATION_CODE = 2;

    const ADDED_CODE_NUMBER = 2000;

    const ERROR_USER_ALREADY_ACTIVATED = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_TO_CREATE_ACTIVATION_CODE = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_ACTIVATION_CODE = 3 + self::ADDED_CODE_NUMBER;

    /*Time to resend did't come. Return time to resend*/
    const ERROR_NO_TIME_TO_RESEND = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_INCORRECT_PASSWORD = 7 + self::ADDED_CODE_NUMBER;

    const ERROR_UNABLE_SEND_ACTIVATION_CODE = 8 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_SEND_ACTIVATION_CODE_TO_SOCIAL = 9 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_INCREASE_CHECK_COUNT = 10 + self::ADDED_CODE_NUMBER;

    //
    const RIGHT_PASSWORD_RESET_CODE = 0;
    const WRONG_PASSWORD_RESET_CODE = 1;
    const RIGHT_DEACTIVATE_PASSWORD_RESET_CODE = 2;

    const MESSAGE_FOR_SMS_FOR_ACTIVATION_CODE = 'Код: ';
    /**
     * Check login.
     *
     * @param string $login
     * @return int
     */
    public function checkLogin(string $login)
    {
        if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return self::LOGIN_INCORRECT;
        }

        $user = Users::findByLogin($login);

        if ($user != false) {
            return self::LOGIN_EXISTS;
        }

        return self::LOGIN_DO_NOT_EXISTS;
    }

    public function formatLogin($login) {
        return $login;
    }

    /**
     * Check password for user.
     *
     * @param Users $user
     * @param string $password
     * @return bool
     */
    public function checkPassword(Users $user, string $password)
    {
        $res = $this->security->checkHash($password, $user->getPassword());
        // Формируем ответ
        if (!($user && $res))
            throw new ServiceException('Incorrect password or login', self::ERROR_INCORRECT_PASSWORD);

        return true;
    }

    /**
     * Отправляет активационный код пользователю на почту.
     * @param string $login
     * @return ActivationCodes.
     */
    public function sendActivationCode(string $login)
    {
        $this->db->begin();
        $checking = $this->checkLogin($login);

        if ($checking == AuthService::LOGIN_INCORRECT) {
            throw new ServiceException('Invalid login', self::ERROR_UNABLE_SEND_ACTIVATION_CODE);
        } elseif ($checking == AuthService::LOGIN_EXISTS) {
            throw new ServiceException('User with same login already exists',
                self::ERROR_UNABLE_SEND_ACTIVATION_CODE);
        }

        $code = $this->createActivationCode($login);

        //Отправляем письмо.
        $this->sendMailForActivation($code);

        $this->db->commit();
        return $code;
    }

    /**
     * create activation code for user if it not exists. In any case rewrite it with new time and code.
     * @param string $login
     * @return ActivationCodes. If all ok return ActivationCodes object.
     */
    public function createActivationCode($login)
    {
        $activationCode = ActivationCodes::findFirstByLogin($login);

        if (!$activationCode) {
            $activationCode = new ActivationCodes();
            $activationCode->setLogin($login);
        } else {
            if (strtotime($activationCode->getTime()) > strtotime(date('Y-m-d H:i:sO')) - ActivationCodes::RESEND_TIME) {
                throw new ServiceExtendedException('Time to resend did\'t come', self::ERROR_NO_TIME_TO_RESEND, null, null,
                    ['time_left' => strtotime($activationCode->getTime())
                        - (strtotime(date('Y-m-d H:i:sO')) - ActivationCodes::RESEND_TIME)]);
            }
        }

        $activationCode->setActivation($this->generateActivation($login));


        $activationCode->setDeactivation($this->generateDeactivation($login));
        $activationCode->setCheckCount(0);
        $activationCode->setTime(date('Y-m-d H:i:sO'));

        if (!$activationCode->save()) {
            SupportClass::getErrorsWithException($activationCode,
                self::ERROR_UNABLE_TO_CREATE_ACTIVATION_CODE,'Unable to create activation code');
        }

        return $activationCode;
    }

    /**
     * Deleting activation code of user
     * @param string $login
     * @return bool
     */
    public function deleteActivationCode($login)
    {
        try {
            $login = $this->formatLogin($login);
            $code = ActivationCodes::findFirstByLogin($login);

            if (!$code) {
                return true;
            }

            return $this->deleteActivationCodeByCode($code);

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deleteActivationCodeByCode(ActivationCodes $code) {
        try {

            if (!$code->delete()) {
                SupportClass::getErrorsWithException($code,
                    self::ERROR_UNABLE_DELETE_ACTIVATION_CODE,'Unable to delete activation code');
            }

            return true;
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getActivationCode(string $login)
    {
        $login = $this->formatLogin($login);

        $activationCode = ActivationCodes::findByLogin($login);

        if (!$activationCode || ((time() - strtotime($activationCode->getTime())) > ActivationCodes::TIME_LIFE)) {
            return null;
        }

        return $activationCode;
    }

    public function checkActivationCode(string $code, string $login)
    {
        $login = $this->formatLogin($login);

        $activationCode = ActivationCodes::findFirstByLogin($login);

        if (!$activationCode || ((time() - strtotime($activationCode->getTime())) > ActivationCodes::TIME_LIFE)
                    || $activationCode->getCheckCount()>ActivationCodes::MAX_CHECK_COUNT) {

            return self::WRONG_ACTIVATION_CODE;
        }

        if ($activationCode->getActivation() != $code) {
            if ($activationCode->getDeactivation() != $code) {
                return self::WRONG_ACTIVATION_CODE;
            } else {
                return self::RIGHT_DEACTIVATION_CODE;
            }
        } else {
            return self::RIGHT_ACTIVATION_CODE;
        }
    }

    public function increaseCheckCount(ActivationCodes $code) {
        if ($code->getCheckCount()>=ActivationCodes::MAX_CHECK_COUNT) {
//            $this->deleteActivationCodeByCode($code);

            return null;
        } else {
            $code->setCheckCount($code->getCheckCount()+1);

            if (!$code->update()) {
                SupportClass::getErrorsWithException($code,
                    self::ERROR_UNABLE_INCREASE_CHECK_COUNT,'Unable to change activation code');
            }

            return $code;
        }
    }

    /**
     * generate code for activation user account to phone
     * @param string $login
     * @return string
     */
    public function generateActivationPhone(string $login)
    {
//        $hash = hash('sha256', $login
//            . $user->getPassword());
        return /*$hash[12] . $hash[7] . $hash[9] . $hash[53]*/rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }

    /**
     * generate code for activation user account
     * @param string $login
     * @return string
     */
    public function generateActivation(string $login)
    {
//        $hash = hash('sha256', $login
//            . $user->getPassword());
        return /*$hash[12] . $hash[7] . $hash[9] . $hash[53]*/rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }

    /**
     * generate code for deactivation user account
     * @param string $login
     * @return string
     */
    public function generateDeactivation(string $login)
    {
//        $hash = hash('sha256', ($user->getEmail() == null ? ' ' : $user->getEmail()) .
//            time() . ($user->getPhoneId() == null ? ' ' : $user->phones->getPhone())
//            . $user->getPassword() . '-no');
        return /*$hash[12] . $hash[7] . $hash[9] . $hash[53]*/rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }

    public function sendMailForActivation(ActivationCodes $activationCode)
    {
        $this->sendMail('hello_world', 'emails/hello_world',
            ['activation' => $activationCode->getActivation(),
                'deactivation' => $activationCode->getDeactivation(),
                'email' => $activationCode->getLogin()], 'Подтвердите регистрацию в нашем замечательном сервисе');
    }

    public function _registerSession($user)
    {
        $di = DI::getDefault();

        $di->getSession()->set(
            "auth",
            [
                "id" => $user->getUserId(),
                "email" => $user->getEmail(),
                "role" => $user->getRole()
            ]
        );
    }

    public function _registerSessionByData($data)
    {
        $di = DI::getDefault();

        $di->getSession()->set(
            "auth",
            [
                "id" => $data['userId'],
                "login" => $data['login'],
                "role" => $data['role']
            ]
        );
    }

    public function createSession(Users $user)
    {
        $time = time() + 604800;
        $lifetime = date(USUAL_DATE_FORMAT, $time);

        $token = self::GenerateToken($user->getUserId(), $user->getEmail(),
                $user->getRole(), $lifetime);

        $this->_registerSession($user);

        $lists = BookLists::findByUserId($user->getUserId());

        $data = [
            'user_id'=>$user->getUserId(),
            'token' => $token,
            'life_time' => $lifetime,
            'lifetime_int' => $time,
            'role' => $user->getRole(),
            'lists'=>$lists
        ];

        return $data;
    }

    public function GenerateToken($userId, $login, $role, $lifetime)
    {
        $header = base64_encode('{"alg":"RS512","typ":"JWT"}');
        $payload = base64_encode(json_encode(['userId' => $userId, 'login' => $login, 'role' => $role, 'lifetime' => $lifetime]));
        $signature = '.';
        //$private = openssl_pkey_get_private(,'foobar');
        $di = DI::getDefault();

        $riv = file_get_contents($di->getConfig()['token_rsa']['pathToPrivateKey']);

        $pk = openssl_get_privatekey($riv, $di->getConfig()['token_rsa']['password']);

        $err = openssl_error_string();
        $result = openssl_private_encrypt($header . '.' . $payload, $signature, $pk, OPENSSL_PKCS1_PADDING);
        if (!$result) {
            return openssl_error_string();
        }

        return $header . '.' . $payload . '.' . base64_encode($signature);
    }

    public function checkToken($token)
    {
        $data = explode('.', $token);

        if(count($data) < 3){
            return false;
        }

        //openssl_public_encrypt($header.$payload,$signature,PRIVATE_KEY,OPENSSL_PKCS1_PADDING);
        $di = DI::getDefault();

        $pub = file_get_contents($di->getConfig()['token_rsa']['pathToPublicKey']);

        $pk = openssl_get_publickey($pub);

        openssl_public_decrypt(base64_decode($data[2]), $signature, $pk, OPENSSL_PKCS1_PADDING);

        if ($data[0] . '.' . $data[1] == $signature)
            return base64_decode($data[1]);
        else
            return false;
    }

    public function getMessageForSmsForActivationCode(ActivationCodes $activationCode){

        if($activationCode==null){
            throw new ServiceException('Активационный код не должен быть null',
                self::ERROR_UNABLE_SEND_ACTIVATION_CODE);
        }

        return 'Код '.$activationCode->getActivationPhone();
    }


    //Сессия

    /**
     * @return Response
     */
    /*public function destroySession()
    {
        $response = new Response();
        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        $tokenRecieved = SecurityPlugin::getTokenFromHeader();
        $token = Accesstokens::findFirst(['userid = :userId: AND token = :token:',
            'bind' => ['userId' => $userId,
                'token' => sha1($tokenRecieved)]]);

        if ($token) {
            $token->delete();
        }

        $this->session->remove('auth');
        $this->session->destroy();
        $response->setJsonContent(
            [
                "status" => STATUS_OK
            ]
        );

        return $response;
    }*/
}
