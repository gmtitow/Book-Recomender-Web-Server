<?php

namespace App\Libs\SocialAuther\Adapter;

use App\Libs\SupportClass;

class Google extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId' => 'id',
            'email' => 'email',
            'first_name' => 'given_name',
            'last_name' => 'family_name',
            'name' => 'name',
            'socialPage' => 'link',
            'avatar' => 'picture',
            'sex' => 'gender'
        );

        $this->provider = 'google';
    }

    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate($code)
    {
        SupportClass::writeMessageInLogFile("Вызвал authenticate");

        if (!empty($code)) {
            $params = array(
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
                'code' => $code
            );

            SupportClass::writeMessageInLogFile("Перед получением токена");

            $tokenInfo = $this->post('https://accounts.google.com/o/oauth2/token', $params);

            $strTokenInfo = var_export($tokenInfo, true);
            SupportClass::writeMessageInLogFile("Получил токен. tokenInfo - " . $strTokenInfo);

            if (isset($tokenInfo['access_token'])) {
                $params['access_token'] = $tokenInfo['access_token'];

                SupportClass::writeMessageInLogFile("Перед запросом данных о юзере");

                $userInfo = $this->get('https://www.googleapis.com/oauth2/v1/userinfo', $params);

                $strUserInfo = var_export($userInfo, true);
                SupportClass::writeMessageInLogFile("Инфа о юзере - " . $strUserInfo);

                if (isset($userInfo[$this->socialFieldsMap['socialId']])) {
                    $this->userInfo = $userInfo;

                    SupportClass::writeMessageInLogFile("Успешно получил данные");
                    return true;
                }
            }
        }

        SupportClass::writeMessageInLogFile("Не смог получить данные");
        return false;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    public function prepareAuthParams()
    {
        return array(
            'auth_url' => 'https://accounts.google.com/o/oauth2/auth',
            'auth_params' => array(
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code',
                'client_id' => $this->clientId,
                'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
            )
        );
    }

    public function getSex()
    {
        $result = null;

        if (isset($this->userInfo[$this->socialFieldsMap['sex']])) {
            if ($this->userInfo[$this->socialFieldsMap['sex']] == 'male')
                $result = 1;
            elseif ($this->userInfo[$this->socialFieldsMap['sex']] == 'female')
                $result = 0;
        }

        return $result;
    }

    public function getPhotoName()
    {
        $avatar_uri = $this->getAvatar();

        if ($avatar_uri != null) {
            $resultName = '';

            for ($i = strlen($avatar_uri) - 1; $i > 0; $i--) {
                if ($avatar_uri[$i] != '/') {
                    $resultName = $avatar_uri[$i] . $resultName;
                } else
                    break;
            }

            return $resultName;
        }

        return null;
    }

    public function getEmail()
    {
        $result = null;

        if (isset($this->userInfo[$this->socialFieldsMap['email']]) && $this->userInfo['verified_email']) {
            $result = $this->userInfo[$this->socialFieldsMap['email']];
        }

        return $result;
    }

    public function getUser()
    {
        return [
            'network' => $this->getProvider(),
            'identity' => $this->getSocialId(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'male' => $this->getSex(),
            //'country'=>$this->getCountry(),
            //'city'=>$this->getCity(),
            'profile' => $this->getSocialPage(),
            'email' => $this->getEmail(),
            //'city_id'=>$this->getCityId(),
            //'status'=>$this->getStatus(),
            //'about'=>$this->getAbout(),
            //'birthday' => $this->getBirthday(),
            'uri_to_photo' => $this->getAvatar(),
            'photo_name' => $this->getPhotoName()
        ];
    }
}