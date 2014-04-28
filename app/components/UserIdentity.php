<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    protected $_id;

    public function authenticate(){
        // Производим стандартную аутентификацию, описанную в руководстве.
        $user = User::model()->find('LOWER(username)=?', array(strtolower($this->username)));
        if(($user===null) || (md5($this->password)!==$user->password)) {
        //if(($user===null) || ($this->password!==$user->password)) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else {
            // В качестве идентификатора будем использовать id, а не username,
            // как это определено по умолчанию. Обязательно нужно переопределить
            // метод getId(см. ниже).
            $this->_id = $user->id;

            $this->errorCode = self::ERROR_NONE;
        }
        return !$this->errorCode;
    }
 
    public function getId(){
        return $this->_id;
    }
}