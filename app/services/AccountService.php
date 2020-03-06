<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http403Exception;
use App\Models\Accounts;
use App\Models\CompanyRole;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use App\Libs\SupportClass;
//Models
use App\Models\Users;
use App\Models\Phones;

/**
 * business and other logic for authentication. Maybe just creation simple objects.
 *
 * Class UsersService
 */
class AccountService extends AbstractService {

    const ADDED_CODE_NUMBER = 1000;

    const ERROR_UNABLE_CREATE_ACCOUNT = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_ACCOUNT_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_ACCOUNT = 3 + self::ADDED_CODE_NUMBER;

    /**
     * create account
     *
     * @param array $accountData [user_id, company_id = null, company_role_id = null]
     * @return int. Return id of account.
     */
    public function createAccount(array $accountData) {
        try {
            $account = new Accounts();
            $account
                ->setUserId($accountData['user_id'])
                ->setCompanyId($accountData['company_id'])
                ->setCompanyRoleId($accountData['company_role_id']);

            if ($account->save() == false) {
                $errors = SupportClass::getArrayWithErrors($account);
                if (count($errors) > 0)
                    throw new ServiceExtendedException('Unable to create account',
                        self::ERROR_UNABLE_CREATE_ACCOUNT, null, null, $errors);
                else {
                    throw new ServiceExtendedException('Unable to create account',
                        self::ERROR_UNABLE_CREATE_ACCOUNT);
                }
            }
        }catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }

        return $account->getId();
    }

    public function deleteAccount(Accounts $account) {
        if ($account->delete() == false) {
            $errors = SupportClass::getArrayWithErrors($account);
            if(count($errors)>0)
                throw new ServiceExtendedException('Unable to delete account',
                    self::ERROR_UNABLE_DELETE_ACCOUNT,null,null,$errors);
            else{
                throw new ServiceExtendedException('Unable to delete account',
                    self::ERROR_UNABLE_DELETE_ACCOUNT);
            }
        }

        return $account->getId();
    }

    public function getForUserDefaultAccount($userId){
        $account = Accounts::findForUserDefaultAccount($userId);

        if(!$account)
            throw new ServiceException('Account for user not found',self::ERROR_ACCOUNT_NOT_FOUND);
        return $account;
    }

    public function getForCompanyRelatedAccounts($companyId){
        $accounts = Accounts::getRelatedAccountsForCompany($companyId);

        if(empty($accounts) || $accounts == '{}')
            throw new ServiceException('Accounts for company not found',self::ERROR_ACCOUNT_NOT_FOUND);
        return $accounts;
    }

    public function getAccountByIds(int $companyId, int $userId){
        $account = Accounts::findFirst(['(company_id = :companyId:) and user_id = :userId:',
            'bind' =>[
                'userId'=>$userId,
                'companyId'=>$companyId,
            ]]);

        if(!$account)
            throw new ServiceException('Account not found',self::ERROR_ACCOUNT_NOT_FOUND);
        return $account;
    }

    /**
     * @param $accountId
     * @return Accounts
     */
    public function getAccountById($accountId){
        $account = Accounts::findFirstById($accountId);

        if(!$account)
            throw new ServiceException('Account not found',self::ERROR_ACCOUNT_NOT_FOUND);
        return $account;
    }

    public function checkPermissionOrGetDefaultAccount($userId, $accountId, $right = ''){
        if ($accountId!=false && $accountId != null && SupportClass::checkInteger($accountId)) {
            if (!Accounts::checkUserHavePermission($userId, $accountId, $right)) {
                throw new Http403Exception('Permission error');
            }

            $account = Accounts::findAccountById($accountId);

            if($account->getCompanyId()!=null && !empty(trim($right))){
                if (!Accounts::checkUserHavePermissionToCompany($userId, $account->getCompanyId(), $right)) {
                    throw new Http403Exception('Permission error');
                }
            }

        } else {
            $account = Accounts::findForUserDefaultAccount($userId);
        }

        return $account;
    }

    public function checkPermissionToCompany($userId, $companyId, $right = ''){
        if ($companyId!=false && $companyId != null && SupportClass::checkInteger($companyId)) {
            if (!Accounts::checkUserHavePermissionToCompany($userId, $companyId, $right)) {
                throw new Http403Exception('Permission error');
            }

            return true;
        }

        throw new Http403Exception('Permission error');
        //return false;
    }
}
