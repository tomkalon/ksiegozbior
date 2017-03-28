<?php 

use Doctrine\DBAL\Connection;

class UserID { 
    
    private $user;
    private $conn;

    public function __construct(Connection $conn, $security){
        $this->conn = $conn;
        $token = $security->getToken();
        if (null !== $token) {
            $this->user = $token->getUsername();
        }
    }
    public function getData($type) {
        if($type == 'id') {
            $id = $this->conn->fetchAssoc(" SELECT id FROM lib_users WHERE username='$this->user' ");
            return $id;
        }
        if($type == 'username') {
            return $this->user;
        }
        else { return NULL;}
    }    
    
}