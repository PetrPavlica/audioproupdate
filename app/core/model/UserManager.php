<?php

namespace App\Core\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Utils\Strings;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use App\Core\Model\Facade\UserFacade;
use App\Core\Model\Facade\PermisionRuleFacade;
use Intra\Model\Facade\CustomerFacade;

/**
 * Users management.
 */
class UserManager implements Nette\Security\IAuthenticator {

    const
            TABLE_NAME = 'user',
            COLUMN_ID = 'id',
            COLUMN_NAME = 'login',
            COLUMN_PASSWORD_HASH = 'password',
            COLUMN_ROLE = 'group',
            COLUMN_LAST_LOGON = 'last_logon';

    /** @var UserFacade */
    private $userFac;

    /** @var PermisionRuleFacade */
    private $permisionRuleFac;

    /** @var CustomerFacade */
    private $cusFacade;

    public function __construct(UserFacade $userFac, PermisionRuleFacade $permisionRuleFac, CustomerFacade $cusFacade) {
        $this->userFac = $userFac;
        $this->permisionRuleFac = $permisionRuleFac;
        $this->cusFacade = $cusFacade;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {

        list($username, $password) = $credentials;
        $isCustomer = false;
        $isFbLogin = false;
        if (is_array($credentials[0])) {
            $username = $credentials[0][0];
            $isCustomer = $credentials[0][1];
            if(isset($credentials[0][2])) {
                $isFbLogin = $credentials[0][2];
            }
        }

        $row = NULL;
        if ($isCustomer === true) {
            if($isFbLogin){
                $row = $this->cusFacade->get()->findOneBy(['fbId' => $username]);
            } else {
                $row = $this->cusFacade->get()->findOneBy(['email' => $username]);
            }
        } else {
            $row = $this->userFac->get()->findOneBy([self::COLUMN_NAME => $username]);
        }

        if(!$isFbLogin) {
            if (!$row) {
                throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
            } elseif (!Passwords::verify($password, $row->password)) {
                throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
            } elseif (Passwords::needsRehash($row->password)) {
                $row->setPassword(Passwords::hash($password));
                $this->userFac->save();
            }
        }

        $arr = $row->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);

        $permisionArr = 'visitor';
        if ($isCustomer !== true) {
            $permisionArr = $this->permisionRuleFac->getPermisionArray($row->group->id);
        }

        return new Nette\Security\Identity($row->id, $permisionArr, $arr);
    }

}
