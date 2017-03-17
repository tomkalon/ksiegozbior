<?php 

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Connection;

class BookForms { 
    
    public $action;
    public $form;
    public $name;
    public $author;
    public $publisher;
    public $year;
    public $pages;
    public $favourite;
    public $readed;
    public $marked;
    public $private_book;
    public $borrow;
    public $borrow_check;
    public $sell;
    public $image;
    public $display;
    public $comment;
    public $submit;
    public $session;
    
    private $conn;
    
    public function __construct(Connection $conn, $form, $action, $session){
        $this->conn = $conn;
        $this->form = $form;
        $this->action = $action;
        $this->session = $session;
    }
    
    public function upload($form, $request, $delete){
        if($delete) { $this->image = NULL; }
        else {
            $path = __DIR__.'/../../web/upload/';
            $files = $request->files->get($form->getName());
            if($files['image']) {
                $filename = $files['image']->getClientOriginalName();
                $filename = $this->inputFilter($filename).rand(1,9999);
                $file = $path.$filename;
                $flag = file_exists ($file);

                while ($flag == true) {
                    $filename = $this->inputFilter($filename).rand(1,99);
                    $file = $path.$filename;
                    $flag = file_exists ($file);
                }
                $files['image']->move($path,$filename);
                $this->image = $filename;
            }
        }
    }
    
    public function delete($id, $generator) {
            if($this->action == 'delete-conf') {
                $this->imageDelete($id);
                $this->conn->delete('lib_books', array('id' => $id));
                $this->session->set('message', 'Książka została usunięta.');
                $url = $generator->generate('user-books');
                return $url;
            }
            else { return false; }
    }
    
    public function imageDelete($id) {
        $date = $this->conn->fetchAssoc(" SELECT image FROM lib_books WHERE id='$id' ");
        if($date) {
            $path = __DIR__.'/../../web/upload/';
            $file = $path.$date['image']; 
            if(file_exists ($file)){
                unlink($file);
            }
        }
    }
        
    public function database($data, $user, $id){
        if($data['borrowcheck']  === false) {
            $data['borrowtext'] = '';
        };
        
        if($this->action == 'add') {
            $this->conn->insert('lib_books', array(
                'name' => $data['name'],
                'author' => $data['author'],
                'publish' => $data['publisher'],
                'year' => $data['year'],
                'pages' => $data['pages'],
                'categories' => $data['categories'],
                'borrow_check' => $data['borrowcheck'],
                'borrow' => $data['borrowtext'],
                'sell' => $data['sell'],
                'private' => $data['private'],
                'marked' => $data['marked'],
                'readed' => $data['readed'],
                'favourite' => $data['favourite'],
                'owner' => $user,
                'image' => $this->image,
                'date' => date('Y-m-d H:i:s')
            ));
            $this->session->set('message', 'Książka została dodana.');
        }
        if($this->action == 'edit') {
            if($this->image) {$this->imageDelete($id);}
            $this->conn->update('lib_books', array(
                'name' => $data['name'],
                'author' => $data['author'],
                'publish' => $data['publisher'],
                'year' => $data['year'],
                'pages' => $data['pages'],
                'categories' => $data['categories'],
                'borrow_check' => $data['borrowcheck'],
                'borrow' => $data['borrowtext'],
                'sell' => $data['sell'],
                'private' => $data['private'],
                'marked' => $data['marked'],
                'readed' => $data['readed'],
                'favourite' => $data['favourite'],
                'image' => $this->image,
                'comment' => $data['comment'],
            ), array('id' => $id));
            $this->session->set('message', 'Dane książki zostały zaktualizowane.');
        }
    }
    
    public function setData($id){
        if($this->action == 'edit') {
            $data = $this->conn->fetchAssoc(" SELECT * FROM lib_books WHERE id='$id' ");
            $this->name = $data['name'];
            $this->author = $data['author'];
            $this->publisher = $data['publish'];
            $this->year = $data['year'];
            $this->pages = $data['pages'];
            $this->categories = $data['categories'];
            $this->comment = $data['comment']; 
            
            $this->favourite = $data['favourite'];
            if($this->favourite) { $this->favourite = true; } else { $this->favourite = false; }
            
            $this->readed = $data['readed'];
            if($this->readed) { $this->readed = true; } else { $this->readed = false; }
            
            $this->marked = $data['marked'];
            if($this->marked) { $this->marked = true; } else { $this->marked = false; }
            
            $this->private_book = $data['private'];
            if($this->private_book) { $this->private_book = true; } else { $this->private_book = false; }
            
            $this->sell = $data['sell'];
            if($this->sell) { $this->sell = true; } else { $this->sell = false; }
            
            $this->borrow_check = $data['borrow_check'];
            if($this->borrow_check) { $this->borrow_check = true; } else { $this->borrow_check = false; }
           
            $this->borrow = $data['borrow'];
            $this->submit = 'Zmień';
            
            $this->image = $data['image'];
            $this->display ='';
        }
        else {
            $this->display = 'display:none!important; width: 0; height:0;';
            $this->submit = 'Dodaj';
        }
    }
    
    public function makeForms(){
        if(($this->action == 'add') or ($this->action == 'edit')) {
            $form = $this->form->createBuilder(FormType::class)
            ->add('name', TextType::class, array(
                'constraints'   => array(new Assert\NotBlank(), new Assert\Length(array('min' => 3))),
                'label'         => 'Nazwa:',
                'data'          => $this->name
            ))    
            ->add('author', TextType::class, array(
                'label'         => 'Autor:',
                'data'          => $this->author
            ))    
            ->add('publisher', TextType::class, array(
                'label'         => 'Wydawnictwo:',
                'data'          => $this->publisher
            ))    
            ->add('year', NumberType::class, array(
                'label'         => 'Rok wydania:',
                'required'      => false,
                'attr'          => array('class' => 'short'),
                'data'          => $this->year
            ))    
            ->add('pages', NumberType::class, array(
                'label'         => 'Liczba stron:',
                'required'      => false,
                'attr'          => array('class' => 'short'),
                'data'          => $this->pages
            ))    
            ->add('categories', TextType::class, array(
                'label'     => 'Kategorie:',
                'required'  => false,
                'data'      => $this->categories
            ))    
            ->add('private', CheckboxType::class, array(
                'label'    => 'Ukryta',
                'required' => false,
                'data'     => $this->private_book
            ))    
            ->add('borrowcheck', CheckboxType::class, array(
                'label'    => 'Pożyczona',
                'required' => false,
                'data'     => $this->borrow_check
            ))    
            ->add('sell', CheckboxType::class, array(
                'label'    => 'Na sprzedaż',
                'required' => false,
                'data'     => $this->sell
            ))
            ->add('borrowtext', TextType::class, array(
                'label'    => ' ',
                'required' => false,
                'attr'     => array('placeholder' => 'Komu pożyczona?'),
                'data'     => $this->borrow
            ))
            ->add('readed', CheckboxType::class, array(
                'label'    => 'Przeczytana',
                'required' => false,
                'data'     => $this->readed
            ))    
            ->add('favourite', CheckboxType::class, array(
                'label'    => 'Ulubiona',
                'required' => false,
                'data'     => $this->favourite
            ))    
            ->add('marked', CheckboxType::class, array(
                'label'    => 'Wyróżniona',
                'required' => false,
                'data'     => $this->marked
            ))
            ->add('image', FileType::class, array(
                'label'       => 'Zdjęcie',
                'required'    => false,
                'label_attr'  => array('class' => 'btn btn-sm btn-image'),
                'constraints' => array(new Assert\Image(
                    array(
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif'
                        ]
                    )
                ))
            ))
            ->add('image_delete', CheckboxType::class, array(
                'label'      => 'Usuń zdjęcie',
                'required'   => false,
                'label_attr' => array('style' => $this->display ),
                'attr'       => array('style' => $this->display ),
                
            ))
            ->add('comment', TextareaType::class, array(
                'label'      => 'Komentarz:',
                'required'   => false,
                'data'       => $this->comment,
                'attr'       => array('rows' => '5', 'cols' => '45', 'style' => $this->display),
                'label_attr' => array('style' => $this->display ),
            ))
            ->add('submit', SubmitType::class, array(
                'label' => $this->submit,
                'attr'  => array('class' => 'btn btn-md btn-blue pull-right')
            ))
            ->add('reset', ResetType::class, array(
                'label' => 'Wyczyść',
                'attr'  => array('class' => 'btn btn-md btn-lightred pull-right')
            ))   
            ->getForm();
            return $form;
        }
        else {
            $form = $this->form->createBuilder(FormType::class)  
            ->getForm();
            return $form;
        }
    }
    
    public function searchForm() {
        $searchForm = $this->form->createNamedBuilder('search', FormType::class)
            ->add('search_text', TextType::class, array(
                'constraints'   => array(new Assert\NotBlank()),
                'label'         => ' ',
            ))
            ->add('search_btn', SubmitType::class, array(
                'label' => 'Szukaj',
                'attr'  => array('style' => 'display:none;')
            ))
            ->getForm();
        return $searchForm;
    }
    
    private function inputFilter($item) {
        $item = mb_strtolower($item,"UTF-8");
        $item = preg_replace('/[^0-9a-z\-]+/', '', $item);
        $item = preg_replace('/[\-]+/', '-', $item);
        $item = str_replace(".", "", $item);
        $item = str_replace("-", "", $item);
        $item = str_replace("jpg", "", $item);
        $item = str_replace("JPG", "", $item);
        $item = str_replace("jpeg", "", $item);
        $item = str_replace("JPEG", "", $item);
        $item = str_replace("png", "", $item);
        $item = str_replace("PNG", "", $item);
        $item = str_replace("gif", "", $item);
        $item = str_replace("GIF", "", $item);
        return $item;
    }
}