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
        
    public function __construct(Connection $conn, $user, $action, $session){
        $this->conn = $conn;
        $this->user = $user;
        $this->action = $action;
        $this->session = $session;
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
    
    public function search($search_query) {
            $search = $this->conn->fetchAll(" SELECT * FROM lib_books WHERE owner= '$this->user' AND (name LIKE '%$search_query%' OR author LIKE '%$search_query%' OR publish LIKE '%$search_query%') ");
            $searchCount = count($search);
            $this->session->set('book_search', $search);
            $this->session->set('search_count', $searchCount);
    }
    private function readDisplay() {
        $data = $this->conn->fetchAssoc(" SELECT display FROM lib_users WHERE username='$this->user' ");
        $display = $data['display'];
        
        $number = strstr($display, '|', true);
        if($number !== false) {
            $display = strstr($display, '|');
            $display = str_replace("|", "", $display);
                if($display > 1000) {
                    $type = "ASC";
                } 
                else {
                    $type = "DESC";
                }
        }
        else {
            $number = 0;
            $name = 0;
            $type = 0;
        }

        $display = $display % 20;
        
        switch ($display) {
            case 0:
                $order = 'data';
            break;
            case 1:
                $order = 'name';
            break;
            case 2:
                $order = 'author';
            break;
            case 3:
                $order = 'publish';
            break;
            case 4:
                $order = 'year';
            break;
            case 5:
                $order = 'pages';
            break;
            case 6:
                $order = 'categories';
            break;
            case 7:
                $order = 'readed';
            break;
            case 8:
                $order = 'favourite';
            break;
            case 9:
                $order = 'marked';
            break;
            case 10:
                $order = 'borrow';
            break;
            case 11:
                $order = 'sell';
            break;
            case 12:
                $order = 'private';
            break;
        }
        
        switch ($number) {
            case 0:
                $number = 25;
            break;
            case 1:
                $number = 50;
            break;
            case 2:
                $number= 100;
            break;
            case 3:
                $number = 200;
            break;
            case 4:
                $number = 500;
            break;
            case 5:
                $number = 1000;
            break;
        }
            
        $settings = array($order, $type, $number);
        return $settings;
    }
    
    public function displayForm($form) {
        $settings = $this->readDisplay();
        $displayForm = $form->createNamedBuilder('display', FormType::class)
            ->add('order', ChoiceType::class, array(
                'label'         => 'Sortuj wg:',
                'choices'       => array(
                    'data'          => 'Daty dodania',
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
            ->add('order_type', ChoiceType::class, array(
                'label'         => 'Kolejnosć:',
                'choices'       => array(
                    'DESC'     => 'Malejąco',
                    'ASC'      => 'Rosnąco'
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
                    1000  => "1000"
                ),
                'preferred_choices' => array($settings[2]),
            ))
            ->add('display_btn', SubmitType::class, array(
                'label' => 'Ustaw',
                'attr'  => array('class' => 'btn btn-md btn-green pull-right')
            ))
            ->getForm();
        return $displayForm;
    }
    
    public function setDisplay(){
        
    }
}