<?php 

use Doctrine\DBAL\Connection;

class UserRegister { 
    
    public $username;
    public $fullname;
    private $password;
    private $rpassword;
    public $email;  
    public $remail;  
    
    public $err_username;
    public $err_fullname;
    public $err_password;
    public $err_email;
    
    public function __construct($username, $fullname, $password, $rpassword, $email, $remail){
        $this->username = strtolower($username);
        $this->fullname = $fullname;
        $this->password = $password;
        $this->rpassword = $rpassword;
        $this->email = strtolower($email);
        $this->remail = strtolower($remail);
        $this->regulations = $regulations;
    }    
    public function getPass() {
        return $this->password;
    }
    public function inputFilter($item) {
        $original = $item;
        $item = preg_replace('/[\-]+/', '-', $item);
        $item = str_replace(".", "", $item);
        $item = str_replace("-", "", $item);
        if($original == $item) {
            return $item;
        }
        else {return null;}
    }
    
    public function validate(Connection $conn){
        
        $error_flag = false;
        
        //username
        if(!($this->inputFilter($this->username))) {
            $this->err_username = "Nazwa użytkownika zawiera niedozwolone znaki.";
            $valid_flag = true;    
        }  
        if(strlen($this->username) < 5) {
            $this->err_username = "Nazwa użytkownika musi składać się co najmniej z 5 znaków.";
            $valid_flag = true;
        };
        $checker = $conn->fetchAll('SELECT * FROM lib_users WHERE username = ?', array(strtolower($this->username)));
        if(count($checker)) {
            $this->err_username = "Wpisana zazwa użytkownika jest już zajeta.";
            $valid_flag = true;
        }
        
        //fullname
        if(strlen($this->fullname) < 6) {
            $this->err_fullname = "Imię i nazwisko musi składać się co najmniej z 7 znaków.";
            $valid_flag = true;
        };
        
        //password  
        if(!($this->inputFilter($this->password))) {
            $this->err_password = "Hasło zawiera niedozwolone znaki.";
            $valid_flag = true;    
        }
        if(strlen($this->password) < 6) {
            $this->err_password = "Hasło musi składać się co najmniej z 6 znaków.";
            $valid_flag = true;
        };
        if($this->password !== $this->rpassword) {
            $this->err_password = "Wpisane hasła różnią się.";
            $valid_flag = true;
        };
        
        //email
        if($this->email !== $this->remail) {
            $this->err_email = "Wpisane adresy e-mail różnią się.";
            $valid_flag = true;
        };
        $checker = $conn->fetchAll('SELECT * FROM lib_users WHERE email = ?', array(strtolower($this->email)));
        if(count($checker)) {
            $this->err_email = "Użytkownik o tym e-mailu jest już zarejestrowany.";
            $valid_flag = true;
        }
        
        if($valid_flag) {
            $this->password = null;
            $this->rpassword = null;
            return false;
        }
        else {return true;}
    }
}