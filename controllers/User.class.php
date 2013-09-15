<?php
$base = dirname(__FILE__);
require_once($base . "/../bootstrap.php");

class User
{
    public function authenticateUser($credentials)
    {
        $user_resultset = Doctrine::getTable('Users')->findOneByUsername($credentials['username']);
        if ($user_resultset) {
            $user = $user_resultset->toArray();
            if ($user['password'] == $credentials['password']) return true;
            else return false;
        }
        return false;
    }

    public function getUsers()
    {
        $users = Doctrine::getTable('Users')->findAll();
        return $users->toArray();
    }

    public function addUser($data)
    {
        $checkUser = Doctrine::getTable('Users')->findOneByUsername($data['username']);
        if ($checkUser) return false;

        $user = new Users();
        if ($data['id']) $user->id = $data['id'];
        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->password = $data['password'];
        $user->save();

        $checkUser = Doctrine::getTable('Users')->findOneByUsername($data['username']);
        if ($checkUser) return true;
    }
}
