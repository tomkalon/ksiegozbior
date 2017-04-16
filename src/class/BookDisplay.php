<?php 

use Doctrine\DBAL\Connection;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BookDisplay { 
    
    private $conn;
    private $user;
    private $action;
    private $session;
    private $count_all;
        
    public function __construct(Connection $conn, $user, $action, $session){
        $this->conn = $conn;
        $this->user = $user;
        $this->action = $action;
        $this->session = $session;
    }
    
    public function getBooks($order, $mode, $number, $page) {
        $offset = ($page - 1) * $number;
        $books = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner = ? ORDER BY $order $mode LIMIT $number OFFSET $offset", array($this->user));
        return $books;
    }
    public function getItem($id) {
        $bookItem = $this->conn->fetchAssoc(" SELECT * FROM lib_books WHERE id=? AND owner = ?", array($id, $this->user));
        return $bookItem;
    }
    
    public function getCount() {
        $books = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner=? ", array($this->user));
        $readed = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner=? AND readed='1' ", array($this->user));
        $favourite = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner=? AND favourite='1' ", array($this->user));
        $borrow = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner=? AND borrow_check='1' ", array($this->user));
        $count['books'] = count($books);
        $count['readed'] = count($readed);
        $count['favourite'] = count($favourite);
        $count['borrow'] = count($borrow);
        $this->count_all = $count['books'];
        return $count;
    }
    
    public function getPagesCount($number) {
        $all_pages = ceil($this->count_all / $number);
        return $all_pages;
    }
    
    public function search($search_query) {
            $search = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner=? AND (name LIKE '%$search_query%' OR author LIKE '%$search_query%' OR publish LIKE '%$search_query%') ", array($this->user));
            $searchCount = count($search);
            $this->session->set('book_search', $search);
            $this->session->set('search_count', $searchCount);
    }
    
    private function decoder($encoded) {
        $data = array();
        for($i = 0; $i<99; $i++){
            if(!($i)) {
                $position = strpos($encoded, '|');
                if(!($position)) {return false;}
                $data[$i] = substr($encoded, 0, $position);
            }
            else 
            {
                $previous_position = $position;
                $position = strpos($encoded, '|', ++$previous_position);
                if($position) {
                    $sub = $position - $previous_position;
                    $data[$i] = substr($encoded, $previous_position, $sub);   
                }
                else {
                    $data[$i] = substr($encoded, $previous_position); 
                    $i=100;
                }
            }
        }
        return $data;
    }
    
    public function readDisplay() {
        $data = $this->conn->fetchAssoc(" SELECT display FROM lib_users WHERE id=? ", array($this->user));
        $settings = $data['display'];
        $settings = $this->decoder($settings);
        return $settings;
    }
    
    public function displayForm($form) {
        $settings = $this->readDisplay();
        $displayForm = $form->createNamedBuilder('display', FormType::class)
            ->add('order', ChoiceType::class, array(
                'label'    => 'Sortuj wg:',
                'choices'  => array(
                    'date'          => 'Daty dodania',
                    'name'          => 'Nazwy ksiązki',
                    'author'        => 'Nazwy autora',
                    'publish'       => 'Nazwy wydawnictwa',
                    'year'          => 'Roku wydania',
                    'pages'         => 'Ilości stron',
                    'categories'    => 'Kategorii',
                    'readed'        => 'Przeczytane',
                    'favourite'     => 'Ulubione',
                    'marked'        => 'Wyróżnione',
                    'borrow'        => 'Pożyczone',
                    'sell'          => 'Na sprzedaż',
                    'private'       => 'Prywatne'
                ),
                'preferred_choices' => array($settings[0]),
            ))
            ->add('mode', ChoiceType::class, array(
                'label'         => 'Kolejnosć:',
                'choices'       => array(
                    'DESC'   => 'Malejąco',
                    'ASC'   => 'Rosnąco'
                ),
                'preferred_choices' => array($settings[1]),
            ))
            ->add('number', ChoiceType::class, array(
                'label'         => 'Ilosć wyświetlanych książek:',
                'choices'       => array(
                    25    => "25",
                    50    => "50",
                    100   => "100",
                    200   => "200",
                    500   => "500",
                    1000  => "1000",
                    999999  => "wszystkie"
                ),
                'preferred_choices' => array(intval($settings[2])),
            ))
            ->add('display_btn', SubmitType::class, array(
                'label' => 'Ustaw',
                'attr'  => array('class' => 'btn btn-md btn-blue pull-right')
            ))
            ->getForm();
        return $displayForm;
    }
    
    public function setDisplayDB($data){
        $this->session->set('settings', $data);
        if($data['number'] > 200) {$data['number'] = 200;}
        $settings = $data['order'].'|'.$data['mode'].'|'.$data['number'];
        $this->conn->update('lib_users', array(
            'display' => $settings,
        ), array('id' => $this->user));
        $this->session->set('message', 'Zmiany zapisane.');
    }
    public function setDisplay(){
        $settings = $this->readDisplay();
        if($settings) {
            $data['order'] = $settings[0];
            $data['mode'] = $settings[1];
            $data['number'] = $settings[2];
        }
        else {
            $data['order'] = 'date';
            $data['mode'] = 'DESC';
            $data['number'] = '25';
        }
        $this->session->set('settings', $data);
    }
}