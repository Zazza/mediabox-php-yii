<?php

class UserIdentity extends CUserIdentity
{
    protected $_id;

    public function authenticate(){
        $criteria = new EMongoCriteria();
        $criteria->username = strtolower($this->username);
        $user = User::model()->find($criteria);
        if(($user===null) || (md5($this->password)!==$user->password)) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else {
            $this->_id = $user->_id;

            $this->errorCode = self::ERROR_NONE;
        }
        return !$this->errorCode;
    }
 
    public function getId(){
        return $this->_id;
    }
}