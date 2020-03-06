<?php


namespace App\library;


class TestRequestsSupport
{
    const MAIN_DOMAIN = 'https://app.rast.ltd';

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var string
     */
    protected $token = '';

    /**
     * @var boolean
     */
    protected $with_authorization = false;

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var string
     */
    protected $access = 'public';

    /**
     * @var string
     */
    protected $method = self::METHOD_GET;

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithAuthorization(): bool
    {
        return $this->with_authorization;
    }

    /**
     * @param bool $with_authorization
     * @return $this
     */
    public function setWithAuthorization(bool $with_authorization)
    {
        $this->with_authorization = $with_authorization;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccess(): string
    {
        return $this->access;
    }

    /**
     * @param string $access
     * @return $this
     */
    public function setAccess(string $access)
    {
        $this->access = $access;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function sendRequest(){
        $headers = [];
        if($this->isWithAuthorization())
            $headers['Authorization'] = $this->getToken();

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::MAIN_DOMAIN.'/'.$this->access.$this->path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $info = curl_getinfo($curl);

        curl_close($curl);

        return ['answer'=>$response,'error'=>$err, 'info'=>$info];
    }
}