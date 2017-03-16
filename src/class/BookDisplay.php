<?php 

use Doctrine\DBAL\Connection;

class BookDisplay { 
    
    private $conn;
    private $user;
    private $action;
        
    public function __construct(Connection $conn, $user, $action){
        $this->conn = $conn;
        $this->user = $user;
        $this->action = $action;
    }
    
    public function getBooks($order, $mode) {
        $books = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner= '$this->user' ORDER BY $order $mode");
        return $books;
    }
    public function getItem($id) {
        $bookItem = $this->conn->fetchAssoc(" SELECT * FROM lib_books WHERE id='$id' ");
        return $bookItem;
    }
    
    public function getCount() {
        $books = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner= '$this->user' ");
        $readed = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner= '$this->user' AND readed='1' ");
        $favourite = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner= '$this->user' AND favourite='1' ");
        $borrow = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner= '$this->user' AND borrow_check='1' ");
        $count['books'] = count($books);
        $count['readed'] = count($readed);
        $count['favourite'] = count($favourite);
        $count['borrow'] = count($borrow);
        return $count;
    }
    
    public function search($search_query, $session) {
            $search = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner= '$this->user' AND (name LIKE '%$search_query%' OR author LIKE '%$search_query%' OR publish LIKE '%$search_query%') ");
            $searchCount = count($search);
            $session->set('book_search', $search);
            $session->set('search_count', $searchCount);
    }
}