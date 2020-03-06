<?php

namespace App\Libs\SocialAuther\Adapter;

use App\Libs\SupportClass;

class Instagram extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'id',
            'email'      => 'email',
            'name'       => 'full_name',
            'avatar'     => 'profile_picture',
            'sex'        => 'gender',
            'about'      => 'bio'
        );

        $this->provider = 'instagram';
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
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUri,
                'grant_type'    => 'authorization_code',
                'code'          => $code
            );

            SupportClass::writeMessageInLogFile("Перед получением токена");

            $tokenInfo = $this->post('https://api.instagram.com/oauth/access_token', $params);

            $strTokenInfo = var_export($tokenInfo,true);
            SupportClass::writeMessageInLogFile("Получил токен. tokenInfo - ".$strTokenInfo);

            if (isset($tokenInfo['access_token'])) {
                //$params['access_token'] = $tokenInfo['access_token'];

                $params = ['access_token' => $tokenInfo['access_token']];

                SupportClass::writeMessageInLogFile("Перед запросом данных о юзере");
                $userInfo = $this->get('https://api.instagram.com/v1/users/self', $params);

                $strUserInfo = var_export($userInfo,true);
                SupportClass::writeMessageInLogFile("Инфа о юзере - ".$strUserInfo);

                if (isset($userInfo['data'][$this->socialFieldsMap['socialId']])) {
                    $this->userInfo = $userInfo['data'];
                    SupportClass::writeMessageInLogFile("Успешно получил данные");
                    return true;
                }
            }
        }

        SupportClass::writeMessageInLogFile("Не смог получить данные");

        return false;
    }

    public function getSocialPage()
    {
        $result = null;

        if (isset($this->userInfo['username'])) {
            $result = 'https://www.instagram.com/' . $this->userInfo['username'].'/';
        }

        return $result;
    }

    public function getFirstName()
    {
        $result = null;

        if (isset($this->userInfo[$this->socialFieldsMap['name']])) {
            $result = explode(' ',$this->userInfo[$this->socialFieldsMap['name']]);
        }

        return $result[0];
    }

    public function getLastName()
    {
        $result = null;

        if (isset($this->userInfo[$this->socialFieldsMap['name']])) {
            $result = explode(' ',$this->userInfo[$this->socialFieldsMap['name']]);
        }

        return $result[1];
    }

    public function getPhotoName()
    {
        $avatar_uri = $this->getAvatar();

        if($avatar_uri!=null){
            $resultName = '';

            $nameStart = false;
            for($i = strlen($avatar_uri)-1;$i>0; $i--){
                if($nameStart){
                    if($avatar_uri[$i]!='/'){
                        $resultName =$avatar_uri[$i] . $resultName;
                    } else
                        break;
                } elseif($avatar_uri[$i]=='?'){
                    $nameStart = true;
                }
            }

            return $resultName;
        }

        return null;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    public function prepareAuthParams()
    {
        return array(
            'auth_url'    => 'https://api.instagram.com/oauth/authorize/',
            'auth_params' => array(
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code',
                'client_id'     => $this->clientId,
                'scope'         => 'basic'
            )
        );
    }

    public function getAbout()
    {
        $result = null;
        if (isset($this->userInfo['bio'])) {
            $result = $this->userInfo['bio'];
        }

        return $result;
    }

    public function getUser(){
        return [
            'network'=>$this->getProvider(),
            'identity'=>$this->getSocialId(),
            'first_name'=>$this->getFirstName(),
            'last_name'=>$this->getLastName(),
            //'male'=>$this->getSex(),
            //'country'=>$this->getCountry(),
            //'city'=>$this->getCity(),
            'profile'=>$this->getSocialPage(),
            //'email'=>$this->getEmail(),
            //'city_id'=>$this->getCityId(),
            //'status'=>$this->getStatus(),
            'about'=>$this->getAbout(),
            //'birthday'=>$this->getBirthday(),
            'uri_to_photo'=>$this->getAvatar(),
            'photo_name'=>$this->getPhotoName()
        ];
    }
}