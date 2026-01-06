<?php

class Minder2_Model_Mapper_SysUser {
    /**
     * @param string $userId
     * @return Minder2_Model_SysUser
     */
    public function find($userId) {
        $userData = Minder::getInstance()->getSysUserData($userId);

        if (false === $userData) {
            $user = new Minder2_Model_SysUser();
            $user->existed = false;
        } else {
            $user = new Minder2_Model_SysUser($userData);
            $user->existed = true;
        }

        return $user;
    }
}