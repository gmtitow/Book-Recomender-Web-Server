<?php

namespace App\Libs\SocialAuther\Adapter;

use App\Libs\SupportClass;

class Facebook extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'id',
            'email'      => 'email',
            'name'       => 'name',
            'socialPage' => 'link',
            'sex'        => 'gender',
            'birthday'   => 'birthday',
            'first_name'=>'first_name',
            'last_name'=>'last_name'
        );

        $this->provider = 'facebook';
    }

    /**
     * Get url of user's avatar or null if it is not set
     *
     * @return string|null
     */
    public function getAvatar()
    {
        $result = null;
        if (isset($this->userInfo['access_token'])) {
            $result = 'https://graph.facebook.com/me/picture?type=large&access_token='.$this->userInfo['access_token'];
        }

        return $result;
    }

    public function getPhotoName()
    {
        $result = null;
        if (isset($this->userInfo['access_token'])) {
            $result = 'downloaded.jpg';
        }

        return $result;
    }

    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate($code)
    {
        if (!empty($code)) {
            $params = array(
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'client_secret' => $this->clientSecret,
                'code'          => $code
            );


            SupportClass::writeMessageInLogFile("Перед получением токена");

            $tokenInfo = $this->get('https://graph.facebook.com/oauth/access_token', $params);

            $strTokenInfo = var_export($tokenInfo,true);
            SupportClass::writeMessageInLogFile("Получил токен. tokenInfo - ".$strTokenInfo);

            SupportClass::writeMessageInLogFile("Перед запросом данных о юзере");
            if (count($tokenInfo) > 0 && isset($tokenInfo['access_token'])) {

                SupportClass::writeMessageInLogFile("Прошел проверку токена. Получение прав");
                $params = array(
                    'access_token' => $tokenInfo['access_token'],
                );
                $permissions = $this->get('https://graph.facebook.com/me/permissions', $params);
                $strPermissions = var_export($permissions,true);
                SupportClass::writeMessageInLogFile("Права - ".$strPermissions);

                SupportClass::writeMessageInLogFile("Перед получением информации о пользователе");
                $params = array(
                    'access_token' => $tokenInfo['access_token'],
                    'fields'=>'id,name,first_name,last_name,picture,hometown,birthday,email'
                );
                $userInfo = $this->get('https://graph.facebook.com/me', $params);

                $strUserInfo = var_export($userInfo,true);
                SupportClass::writeMessageInLogFile("Инфа о юзере - ".$strUserInfo);
                if (isset($userInfo['id'])) {
                    $this->userInfo = $userInfo;
                    $this->userInfo['access_token'] = $tokenInfo['access_token'];
                    return true;
                }
            }
        }

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
            'auth_url'    => 'https://www.facebook.com/dialog/oauth',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
                'scope'         => 'email,public_profile,user_birthday,user_hometown'
            )
        );
    }

    public function getUser(){
        return [
            'network'=>$this->getProvider(),
            'identity'=>$this->getSocialId(),
            'first_name'=>$this->getFirstName(),
            'last_name'=>$this->getLastName(),
            'male'=>$this->getSex(),
            //'country'=>$this->getCountry(),
            //'city'=>$this->getCity(),
            'profile'=>$this->getSocialPage(),
            'email'=>$this->getEmail(),
            //'city_id'=>$this->getCityId(),
            //'status'=>$this->getStatus(),
            //'about'=>$this->getAbout(),
            'birthday'=>$this->getBirthday(),
            'uri_to_photo'=>$this->getAvatar(),
            'photo_name'=>$this->getPhotoName()
        ];
    }
}