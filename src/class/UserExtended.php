<?php 

use Doctrine\DBAL\Connection;

class UserExtended { 
    
    private $user;
    private $conn;

    public function __construct(Connection $conn, $security){
        $this->conn = $conn;
        $token = $security->getToken();
        if (null !== $token) {
            $this->user = $token->getUser();
        }
    }
    
    //get username
    public function getName(){
        return $this->user;
    }
    
    //get user id
    public function getID(){
        $query = $this->conn->fetchColumn("SELECT id FROM lib_users WHERE username=? ", array($this->user), 0);
        return $query;
    }
}