<?php

namespace App\Libs\SocialAuther\Adapter;

use App\Libs\SupportClass;

class Vk extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'id',
            'email'      => 'email',
            'avatar'     => 'photo_max_orig',
            'birthday'   => 'bdate',
            'first_name'=>'first_name',
            'last_name'=>'last_name'
        );

        $this->provider = 'vk';
    }

    /**
     * Get user name or null if it is not set
     *
     * @return string|null
     */
    public function getName()
    {
        $result = null;

        if (isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['first_name'] . ' ' . $this->userInfo['last_name'];
        } elseif (isset($this->userInfo['first_name']) && !isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['first_name'];
        } elseif (!isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['last_name'];
        }

        return $result;
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;

        if (isset($this->userInfo['screen_name'])) {
            $result = 'http://vk.com/' . $this->userInfo['screen_name'];
        }

        return $result;
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        $result = null;
        if (isset($this->userInfo['sex'])) {
            $result = $this->userInfo['sex'] == 1 ? 0 : 1;
        }

        return $result;
    }

    public function getCountry()
    {
        $result = null;
        if (isset($this->userInfo['country'])) {
            $result = $this->userInfo['country']['title'];
        }

        return $result;
    }

    public function getCity()
    {
        $result = null;
        if (isset($this->userInfo['city'])) {
            $result = $this->userInfo['city']['title'];
        }

        return $result;
    }

    public function getCityId()
    {
        $result = null;
        if (isset($this->userInfo['city'])) {
            $result = $this->userInfo['city']['id'];
        }

        return $result;
    }

    public function getAbout()
    {
        $result = null;
        if (isset($this->userInfo['about'])) {
            $result = $this->userInfo['about'];
        }

        return $result;
    }

    public function getStatus()
    {
        $result = null;
        if (isset($this->userInfo['status'])) {
            $result = $this->userInfo['status'];
        }

        return $result;
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
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'scope'=>'4195328',
                'v' => 5.92
            );

            SupportClass::writeMessageInLogFile("Перед получением токена");

            $tokenInfo = $this->get('https://oauth.vk.com/access_token', $params);
            $strTokenInfo = var_export($tokenInfo,true);
            SupportClass::writeMessageInLogFile("Получил токен. tokenInfo - ".$strTokenInfo);

            if (isset($tokenInfo['access_token'])) {
                $params = array(
                    'uids'         => $tokenInfo['user_id'],
                    'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_max_orig,email,city,country,status,about',
                    'access_token' => $tokenInfo['access_token'],
                    'scope'=>'4195328',
                    'v' => 5.92
                );

                SupportClass::writeMessageInLogFile("Перед запросом данных о юзере");

                $userInfo = $this->get('https://api.vk.com/method/users.get', $params);

                $strUserInfo = var_export($userInfo,true);
                SupportClass::writeMessageInLogFile("Инфа о юзере - ".$strUserInfo);

                $strUserInfo = var_export($userInfo['response'],true);
                SupportClass::writeMessageInLogFile("response - ".$strUserInfo);

                if (isset($userInfo['response'])) {
                    $this->userInfo = $userInfo['response'][0];
                    $this->userInfo['email'] = $tokenInfo['email'];
                    SupportClass::writeMessageInLogFile("Успешно получил данные");
                    return true;
                }
            }
        }

        SupportClass::writeMessageInLogFile("Возвращаемое значение - false");
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
            'auth_url'    => 'http://oauth.vk.com/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'scope'         => '4195328',
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code'
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
            'country'=>$this->getCountry(),
            'city'=>$this->getCity(),
            'profile'=>$this->getSocialPage(),
            'email'=>$this->getEmail(),
            'city_id'=>$this->getCityId(),
            'status'=>$this->getStatus(),
            'about'=>$this->getAbout(),
            'birthday'=>$this->getBirthday(),
            'uri_to_photo'=>$this->getAvatar(),
            'photo_name'=>$this->getPhotoName()
        ];
    }
}