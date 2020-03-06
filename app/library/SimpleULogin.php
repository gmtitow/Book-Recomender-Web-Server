<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 12.02.2019
 * Time: 10:44
 */

namespace App\Libs;

use Phalcon\DI\FactoryDefault as DI;

class SimpleULogin
{
    /**
     * Got user data
     *
     * @var boolean|array
     */
    protected $user = false;

    /**
     * Token key
     *
     * @var boolean
     */
    protected $token = false;

    /**
     * Available auth providers. Default show on the panel
     *
     * @var string
     */
    private $requiredProviders = 'vkontakte,odnoklassniki,mailru,facebook';

    /**
     * Hidden auth providers. Default hide on the drop down
     *
     * @var string
     */
    private $hiddenProviders = 'twitter,livejournal,google,yandex,openid';

    /**
     * Required providers fields.
     *
     * @var string
     */
    private $requiredFields = 'first_name,last_name,photo';

    /**
     * Optional (additional) fields providers fields.
     *
     * @var string
     */
    private $optionalFields = 'email,nickname,bdate,sex,photo_big,city,country';

    /**
     * Widget types
     *
     * @var array
     */
    protected $types = [
        'small',
        'panel',
        'window'
    ];

    /**
     * Widget. 'small' as default
     *
     * @var string
     */
    private $widget = 'small';

    /**
     * Redirect url
     *
     * @var boolean|string
     */
    private $url = false;

    public function __construct(array $params = [])
    {
        if (empty($params) === false) {

            foreach ($params as $key => $values) {

                if (method_exists($this, 'set' . ucfirst($key)) === true) {
                    $this->{'set' . ucfirst($key)}($values);
                }
            }

        }
    }

    /**
     * @return array|bool
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param array|bool $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isToken(): bool
    {
        return $this->token;
    }

    /**
     * @param bool $token
     */
    public function setToken(bool $token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getRequiredProviders(): string
    {
        return $this->requiredProviders;
    }

    /**
     * @param string $requiredProviders
     */
    public function setRequiredProviders(string $requiredProviders)
    {
        $this->requiredProviders = $requiredProviders;
    }

    /**
     * @return string
     */
    public function getHiddenProviders(): string
    {
        return $this->hiddenProviders;
    }

    /**
     * @param string $hiddenProviders
     */
    public function setHiddenProviders(string $hiddenProviders)
    {
        $this->hiddenProviders = $hiddenProviders;
    }

    /**
     * @return string
     */
    public function getRequiredFields(): string
    {
        return $this->requiredFields;
    }

    /**
     * @param string $requiredFields
     */
    public function setRequiredFields(string $requiredFields)
    {
        $this->requiredFields = $requiredFields;
    }

    /**
     * @return string
     */
    public function getOptionalFields(): string
    {
        return $this->optionalFields;
    }

    /**
     * @param string $optionalFields
     */
    public function setOptionalFields(string $optionalFields)
    {
        $this->optionalFields = $optionalFields;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param array $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * @return string
     */
    public function getWidget(): string
    {
        return $this->widget;
    }

    /**
     * @param string $widget
     */
    public function setWidget(string $widget)
    {
        $this->widget = $widget;
    }

    /**
     * @return bool|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param bool|string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Returns the html-form widget
     *
     * @return View
     */
    public function getForm()
    {
        $view = DI::getDefault()->getView();
        ob_start();
        $view->partial('social/ulogin', [
            'widget' => $this->widget,
            'fields' => $this->requiredFields,
            'optional' => $this->optionalFields,
            'providers' => $this->requiredProviders,
            'hidden' => $this->hiddenProviders,
            'url' => $this->url
        ]);
        return ob_get_clean();

    }
}