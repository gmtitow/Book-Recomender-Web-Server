<?php

namespace App\Models;

class ActivationCodes extends AbstractModel
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $code_id;
    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    protected $activation;
    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    protected $deactivation;
    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $time;
    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $used;

    /**
     *
     * @var string
     * @Column(type="string", length=250, nullable=false)
     */
    protected $login;

    /**
     *
     * @var int
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $check_count;

    /**
     * Минимальное время, которое должно пройти перед повторной отправкой.
     * В секундах.
     */
    const RESEND_TIME = 300;

    const TIME_LIFE = 3600;

    const MAX_CHECK_COUNT = 10;

    /**
     * @return int
     */
    public function getCheckCount()
    {
        return $this->check_count;
    }

    /**
     * @param int $check_count
     * @return $this
     */
    public function setCheckCount(int $check_count)
    {
        $this->check_count = $check_count;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return $this
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * Method to set the value of field userid
     *
     * @param integer $code_id
     * @return $this
     */
    public function setCodeId($code_id)
    {
        $this->code_id = $code_id;
        return $this;
    }
    /**
     * Method to set the value of field activation
     *
     * @param string $activation
     * @return $this
     */
    public function setActivation($activation)
    {
        $this->activation = $activation;
        return $this;
    }
    /**
     * Method to set the value of field deactivation
     *
     * @param string $deactivation
     * @return $this
     */
    public function setDeactivation($deactivation)
    {
        $this->deactivation = $deactivation;
        return $this;
    }
    /**
     * Method to set the value of field time
     *
     * @param string $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }
    public function setUsed($used)
    {
        $this->used = $used;
        return $this;
    }
    /**
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getCodeId()
    {
        return $this->code_id;
    }
    /**
     * Returns the value of field activation
     *
     * @return string
     */
    public function getActivation()
    {
        return $this->activation;
    }
    /**
     * Returns the value of field deactivation
     *
     * @return string
     */
    public function getDeactivation()
    {
        return $this->deactivation;
    }
    /**
     * Returns the value of field time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }
    public function getUsed()
    {
        return $this->used;
    }
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("activation_codes");
    }

    public function getSequenceName()
    {
        return "activationcodes_code_id_seq";
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'activation_codes';
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ActivationCodes[]|ActivationCodes|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }
    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ActivationCodes|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByLogin($login) {
        return ActivationCodes::findFirstByLogin($login);
    }
}