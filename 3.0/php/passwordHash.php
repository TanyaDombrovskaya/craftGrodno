<?php
// Файл: /php/passwordHash.php

class PasswordHash {
    private $cost = 12;
    
    public function hash($password) {
        $options = ['cost' => $this->cost];
        $hash = password_hash($password, PASSWORD_BCRYPT, $options);
        
        if ($hash === false) {
            throw new Exception("Ошибка хеширования пароля");
        }
        
        return $hash;
    }
    
    public function verify($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }
}

$passwordHash = new PasswordHash();
?>