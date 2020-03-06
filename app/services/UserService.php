<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ActivationCodes;
use App\Models\ChangesAuthentication;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class UserService extends AbstractService
{
    const ADDED_CODE_NUMBER = 26000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_USER = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_USER = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_USER_NOT_FOUND = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_USER = 4 + self::ADDED_CODE_NUMBER;


    const ERROR_INNER_LOGIC_ERROR = 5 + self::ADDED_CODE_NUMBER;
    /**
     * Creating a new user
     *
     * @param array $userData
     */
    /*public function createUser(array $userData) {
        try {
            $user = new Users();
            $result = $user->setEmail($userData['email'])
                    ->setPassword(password_hash($userData['password'], PASSWORD_DEFAULT))
                    //->setLastName($userData['first_name'])
                    //->setFirstName($userData['last_name'])
                    ->setStatus($userData['status'])
                    ->create();

            if (!$result) {
                throw new ServiceException('Unable to create user', self::ERROR_UNABLE_CREATE_USER);
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23505) {
                throw new ServiceException('User already exists', self::ERROR_ALREADY_EXISTS, $e);
            } else {
                throw new ServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }*/

    /**
     * Creating a new user
     *
     * @param array $userData
     * @return Users. If all ok, return Users object
     */
    public function createUser(array $userData)
    {
        $this->db->begin();
        try {
            $user = new Users();

            $user->setEmail($userData['login']);

            $user->setPassword($userData['password']);
            $user->setRole(isset($userData['role'])?$userData['role']:ROLE_GUEST);

            $user->setActivated(isset($userData['activated'])?$userData['activated']:false);

            if ($user->save() == false) {
                $this->db->rollback();
                SupportClass::getErrorsWithException($user,self::ERROR_UNABLE_CREATE_USER,'unable to create user');
            }

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        $this->db->commit();

        return $user;
    }

    /**
     * Setting a new password for user
     *
     * @param Users $user
     * @param string $password
     */
    public function setPasswordForUser(Users $user, string $password)
    {
        try {
            $user->setPassword($password);
            if ($user->update() == false) {
                $errors = SupportClass::getArrayWithErrors($user);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to change password of user',
                        self::ERROR_UNABLE_CHANGE_USER, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to change password of user',
                        self::ERROR_UNABLE_CHANGE_USER);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Setting a new email for user
     *
     * @param Users $user
     * @param string $email
     *
     * @return true
     */
    public function setNewEmail(Users $user, string $email)
    {
        try {
            $this->db->begin();
            $this->refreshChangedEmail($user,$user->changesAuthentication, false);

            $user->changesAuthentication->setNewEmail($email);
            $user->changesAuthentication->setTimeEmailChanged(date(USUAL_DATE_FORMAT));
            if ($user->changesAuthentication->update() == false) {
                $this->db->rollback();
                SupportClass::getErrorsWithException($user->changesAuthentication,
                    self::ERROR_UNABLE_CHANGE_USER, 'Unable to change email');
            }


            $this->db->commit();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return true;
    }

    /**
     * Setting a new phone for user
     *
     * @param Users $user
     * @param string $phone
     *
     * @return true
     */
    public function setNewPhone(Users $user, string $phone)
    {
        try {
            $this->db->begin();

            $phone = $this->phoneService->createPhone($phone);

            $this->refreshChangedPhone($user,$user->changesAuthentication, false);

            $user->changesAuthentication->setNewPhoneId($phone->getPhoneId());
            $user->changesAuthentication->setTimePhoneChanged(date(USUAL_DATE_FORMAT));
            if ($user->changesAuthentication->update() == false) {
                $this->db->rollback();
                SupportClass::getErrorsWithException($user->changesAuthentication,
                    self::ERROR_UNABLE_CHANGE_USER, 'Unable to change phone');
            }


            $this->db->commit();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return true;
    }

    /**
     * Set Users phone_id from ChangedAuthentication new_phone_id if time is over
     * @param Users $user
     * @param ChangesAuthentication $changesAuthentication
     * @param bool $change_in_changes_authentication = true
     */
    public function refreshChangedPhone(Users $user, ChangesAuthentication $changesAuthentication,
                                        bool $change_in_changes_authentication = true){

        if($user->getUserId()!=$changesAuthentication->getUserId()){
            throw new ServiceException('User does not match with change authentication object',
                self::ERROR_INNER_LOGIC_ERROR, null);
        }


        if(!empty($changesAuthentication->getNewPhoneId())){
            if(time() - strtotime($changesAuthentication->getTimePhoneChanged())
                > ChangesAuthentication::TIME_TO_CHANGE_EMAIL){

                $this->db->begin();
                $user->setPhoneId($changesAuthentication->getNewPhoneId());

                if ($user->update() == false) {
                    $this->db->rollback();
                    SupportClass::getErrorsWithException($user,
                        self::ERROR_UNABLE_CHANGE_USER, 'Can\'t refresh phone');
                }

                if($change_in_changes_authentication){
                    $changesAuthentication->setNewPhoneId(null);
                    $changesAuthentication->setTimePhoneChanged(null);

                    if ($changesAuthentication->update() == false) {
                        $this->db->rollback();
                        SupportClass::getErrorsWithException($changesAuthentication,
                            self::ERROR_UNABLE_CHANGE_USER, 'Can\'t refresh phone');
                    }
                }

                $this->db->commit();
            }
        }
    }

    /**
     * Set Users email from ChangedAuthentication new_email if time is over
     * @param Users $user
     * @param ChangesAuthentication $changesAuthentication
     * @param bool $change_in_changes_authentication = true
     */
    public function refreshChangedEmail(Users $user, ChangesAuthentication $changesAuthentication,
                                        bool $change_in_changes_authentication = true){

        if($user->getUserId()!=$changesAuthentication->getUserId()){
            throw new ServiceException('User does not match with change authentication object',
                self::ERROR_INNER_LOGIC_ERROR, null);
        }

        if(!empty($changesAuthentication->getNewEmail())){
            if(time() - strtotime($changesAuthentication->getTimeEmailChanged())
                > ChangesAuthentication::TIME_TO_CHANGE_EMAIL){

                $this->db->begin();

                $user->setEmail($changesAuthentication->getNewEmail());

                if ($user->update() == false) {
                    $this->db->rollback();
                    SupportClass::getErrorsWithException($user,
                        self::ERROR_UNABLE_CHANGE_USER, 'Can\'t refresh email');
                }

                if($change_in_changes_authentication){
                    $changesAuthentication->setNewEmail(null);
                    $changesAuthentication->setTimeEmailChanged(null);

                    if ($changesAuthentication->update() == false) {
                        $this->db->rollback();
                        SupportClass::getErrorsWithException($changesAuthentication,
                            self::ERROR_UNABLE_CHANGE_USER, 'Can\'t refresh phone');
                    }
                }

                $this->db->commit();
            }
        }
    }

    public function refreshEmailAndPhone(Users $user, ChangesAuthentication $changesAuthentication,
                                         bool $change_in_changes_authentication = true){
        $this->refreshChangedEmail($user,$changesAuthentication, $change_in_changes_authentication);
        $this->refreshChangedPhone($user,$changesAuthentication, $change_in_changes_authentication);
    }

    public function cancelChangingAuthentication(ChangesAuthentication $changesAuthentication,
                                                 bool $phone = false, bool $email = false){

        if($phone) {
            $changesAuthentication->setNewPhoneId(null);
            $changesAuthentication->setTimePhoneChanged(null);
        }

        if($email){
            $changesAuthentication->setNewEmail(null);
            $changesAuthentication->setTimeEmailChanged(null);
        }

        if ($changesAuthentication->update() == false) {
            SupportClass::getErrorsWithException($changesAuthentication,
                self::ERROR_UNABLE_CHANGE_USER, 'Can\'t refresh phone');
        }

        return $changesAuthentication;
    }

    /**
     * Setting a new role for user
     *
     * @param Users $user
     * @param string $role
     */
    public function setNewRoleForUser(Users $user, string $role)
    {
        try {
            $user->setRole($role);
            if ($user->update() == false) {
                $errors = SupportClass::getArrayWithErrors($user);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to change role of user',
                        self::ERROR_UNABLE_CHANGE_USER, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to change role of user',
                        self::ERROR_UNABLE_CHANGE_USER);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function changeUser(Users $user, array $userData){
        if(!empty(trim($userData['email']))){
            $user->setEmail($userData['email']);
        }

        if(!empty(trim($userData['phoneId']))){
            $user->setPhoneId($userData['phoneId']);
        }

        if(!empty(trim($userData['role']))){
            $user->setRole($userData['role']);
        }

        if(!empty(trim($userData['password']))){
            $user->setPassword($userData['password']);
        }

        if(!empty(trim($userData['activated']))){
            $user->setActivated($userData['activated']);
        }

        if(!empty(trim($userData['isSocial']))){
            $user->setIsSocial($userData['isSocial']);
        }

        if (!$user->update()) {
            $errors = SupportClass::getArrayWithErrors($user);
            if (count($errors) > 0)
                throw new ServiceExtendedException('Unable to update user',
                    self::ERROR_UNABLE_CHANGE_USER, null, null, $errors);
            else {
                throw new ServiceExtendedException('Unable to update user',
                    self::ERROR_UNABLE_CHANGE_USER);
            }
        }
    }

    /**
     * Delete an existing user
     *
     * @param Users $user
     */
    public function deleteUser(Users $user)
    {
        try {
            $result = $user->delete();

            if (!$result) {
                $errors = SupportClass::getArrayWithErrors($user);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to delete user',
                        self::ERROR_UNABLE_DELETE_USER, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to delete user',
                        self::ERROR_UNABLE_DELETE_USER);
                }
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $login
     *
     * @throw ServiceException
     * @return Users
     */
    public function getUserByLogin(string $login){
        $user = Users::findByLogin($login);

        if (!$user || $user == null) {
            throw new ServiceException('Invalid login', self::ERROR_USER_NOT_FOUND);
        }
        return $user;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getUserById(int $userId){
        $user = Users::findFirstByUserId($userId);

        if (!$user || $user == null) {
            throw $this->getExceptionUserNotFound();
        }
        return $user;
    }

    /**
     * Updating an existing user
     *
     * @param array $userData
     */
    public function findOnByEmail($email)
    {
        try {
            $user = User::findFirst(
                [
                    'conditions' => 'email = :email:',
                    'bind' => [
                        'email' => $email
                    ],
                    'columns' => "id, email, first_name, last_name, lastconnexion, status",
                ]
            );

            if (!$user) {
                return [];
            }

            return $user->toArray();
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function createGroup(array $data)
    {
        try {
            $group = new Group();
            $chatHis = $this->chatHistoryService->createChatHistory();
            $group->setName($data["name"]);
            $group->setChatHistId($chatHis->getId());
            $result = $group->save();
            $this->logger->critical(
                $result . '===' . $result
            );
            if (!$result) {
                throw new ServiceException('Unable to create Groupe', self::ERROR_UNABLE_CREATE_USER, '', $this->logger);
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23505) {
                throw new ServiceException('User already exists', self::ERROR_ALREADY_EXISTS, $e, $this->logger);
            } else {
                throw new ServiceException($e->getMessage(), $e->getCode(), $e, $this->logger);
            }
        }
    }

    /**
     * Updating an existing user
     *
     * @param array $userData
     */
    public function updateUser(array $userData)
    {
        try {
            $user = Users::findFirst(
                [
                    'conditions' => 'id = :id:',
                    'bind' => [
                        'id' => $userData['id']
                    ]
                ]
            );

            $userData['email'] = (is_null($userData['email'])) ? $user->getemail() : $userData['email'];
            $userData['password'] = (is_null($userData['password'])) ? $user->getPass() : password_hash($userData['password'], PASSWORD_DEFAULT);
            $userData['first_name'] = (is_null($userData['first_name'])) ? $user->getFirstName() : $userData['first_name'];
            $userData['last_name'] = (is_null($userData['last_name'])) ? $user->getLastName() : $userData['last_name'];

            $result = $user->setemail($userData['email'])
                ->setPass($userData['password'])
                ->setFirstName($userData['first_name'])
                ->setLastName($userData['last_name'])
                ->update();

            if (!$result) {
                throw new ServiceException('Unable to update user', self::ERROR_UNABLE_UPDATE_USER);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete an existing user
     *
     * @param int $userId
     */
    /*public function deleteUser($userId) {
        try {
            $user = User::findFirst(
                            [
                                'conditions' => 'id = :id:',
                                'bind' => [
                                    'id' => $userId
                                ]
                            ]
            );

            if (!$user) {
                throw new ServiceException("User not found", self::ERROR_USER_NOT_FOUND);
            }

            $result = $user->delete();

            if (!$result) {
                throw new ServiceException('Unable to delete user', self::ERROR_UNABLE_DELETE_USER);
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }*/


    /**
     * Returns user list
     *
     * @return array
     */
    public function getUserList($currentUserId)
    {
        try {

            $users = Users::find(
                [
                    'conditions' => 'userid != :id:',
                    'bind' => ['id' => $currentUserId],
                ], false
            );

            $this->logger->critical(
                ' Internal Server Error '
            );

            if (!$users) {
                return [];
            }

            return $users->toArray();
        } catch (\PDOException $e) {
            $this->logger->critical(
                $e->getMessage() . ' ' . $e->getCode()
            );
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return ServiceException
     */
    public function getExceptionUserNotFound(){
        return new ServiceException('User doesn\'t exists', self::ERROR_USER_NOT_FOUND);
    }
}
