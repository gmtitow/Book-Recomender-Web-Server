<?php
namespace App\Models;

use App\Libs\SupportClass;
use http\Client\Curl\User;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

use Phalcon\DI\FactoryDefault as DI;
use App\Libs\ImageLoader;

class Users extends AbstractModel
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    protected $email;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    protected $password;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $role;


    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $activated;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date_registration;


    /**
     * Method to set the value of field userId
     *
     * @param integer $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Method to set the value of field email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Method to set the value of field password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $security = DI::getDefault()->getSecurity();
        $this->password = $security->hash($password);

        return $this;
    }

    /**
     * Method to set the value of field role
     *
     * @param string $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the value of field password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the value of field role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    public function getDateRegistration()
    {
        return $this->date_registration;
    }

    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    public function getActivated()
    {
        return $this->activated;
    }

    public static function findByLogin($login)
    {
        $email = $login;

        $user = Users::findFirst(
            [
                "email = :email:",
                "bind" => [
                    "email" => $email
                ]
            ]
        );

        return $user;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model' => $this,
                    'message' => 'Введите, пожалуйста, правильный адрес',
                ]
            )
        );

        $validator->add(
            'role',
            new PresenceOf(
                [
                    "message" => "Не указана роль пользователя",
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
		$this->setSource(self::getTableName());
    }

    public static function getIdField()
    {
        return 'user_id';
    }

    public function getSequenceName() {
        return "users_user_id_seq";
    }

    /**
     * @param $id
     * @return bool
     */
    public static function isUserExist($id){
        if(is_null($id) || empty($id))
            return false;
        $user = parent::findFirst($id);
        if(!$user)
            return false;
        return true;
    }

	public static function getTableName() {
		return 'users';
	}
}
