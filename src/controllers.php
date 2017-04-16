<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));


//HOMEPAGE 
//----------------------------------------------------------------------    
$app->get('/', function (Request $request) use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('homepage');

$app->get('/main', function (Request $request) use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('main');


//USER PAGE
//----------------------------------------------------------------------
$app->get('/user', function (Request $request) use ($app) {
    return $app['twig']->render('user/user.html.twig', array());
})
->bind('user');

//VERSION
//----------------------------------------------------------------------
$app->get('/version', function (Request $request) use ($app) {
    return $app['twig']->render('version.html.twig', array());
})
->bind('version');


//BOOKLIST 
//----------------------------------------------------------------------
$app->match('/user/page{page}-show{id}-{action}', function (Request $request, $id, $action, $page) use($app) {
    
    //get username & user id
    $User = new UserExtended($app['db'], $app['security.token_storage']);
    $user = $User->getID();
    
    //forms - add, edit book
    $BookForm = new BookForms($app['db'], $app['form.factory'], $action, $app['session']);
    //search form
    $searchForm = $BookForm->searchForm();
    
    //delete method
    $flag = $BookForm->delete($id, $app['url_generator']);
    if($flag) {$app->redirect($flag); }
    
    //edit method set data
    $BookForm->setData($id);
    $form = $BookForm->makeForms();
    
    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();
        $BookForm->upload($form, $request, $data['image_delete']);
        $BookForm->database($data, $user, $id);
        
        $url = $app['url_generator']->generate('user-books', array('id' => $id));
        return $app->redirect($url); 
    }

    //books display & search
    //get from DB list of books, favourites, readed and borrowed count
    $Books = new BookDisplay($app['db'], $user, $action, $app['session']);
    
    //get DisplaySettings from SESSION, else get from DB and save in SESSION
    $settings = $app['session']->get('settings');
    if(!($settings)) {
        $temp = new BookDisplay($app['db'], $user, $action, $app['session']);
        $temp->setDisplay();
        unset($temp);
        $settings = $app['session']->get('settings');
    }
    

    $bookList = $Books->getBooks($settings['order'], $settings['mode'], $settings['number'], $page); //show books
    $bookItem = $Books->getItem($id); //show selected book
    $bookCount = $Books->getCount(); //get number of books
    $pageAll = $Books->getPagesCount($settings['number']); //get number of pages
    
    $searchForm->handleRequest($request);
    if ($searchForm->isValid()) {
        $searchData = $searchForm->getData();
        $searchQuery = $searchData['search_text'];
        $Books->search($searchQuery);
        $url = $app['url_generator']->generate('user-books', array('id' => 0, 'action' => 'search'));
        return $app->redirect($url); 
    }
        
    return $app['twig']->render('user/index.html.twig', array(
        'page_no'               => $page, //actual page
        'page_all'              => $pageAll, //number of pages
        'display_scope'         => $settings['number'], // number of displayed books
        'book_list'             => $bookList, //book list
        'book_count'            => $bookCount, // books & its status count
        'book_item'             => $bookItem, //show book
        'book_edit_flag'        => $editFlag, //is book edited
        'action'                => $action, // information about form action
        'id'                    => $id, // information about form action
        'form'                  => $form->createView(), //form
        'search'                => $searchForm->createView(), //form
    ));
    
})
->method('GET|POST')
->bind('user-books')
->value('page', 1)
->value('id', 0)
->value('action', 'default')
->assert('page', '\d+')
->assert('id', '\d+');
    
//DISPLAY
//----------------------------------------------------------------------
$app->match('/user/display', function (Request $request) use ($app) {
    
    //get username & user id
    $User = new UserExtended($app['db'], $app['security.token_storage']);
    $user = $User->getID();
    
    //show display settings form
    $Display = new BookDisplay($app['db'], $user, $action, $app['session']);
    $displayForm = $Display->displayForm($app['form.factory']);
    
    $displayForm->handleRequest($request);
    if ($displayForm->isValid()) {
        $data = $displayForm->getData();
        $Display->setDisplayDB($data); //write data in DB
            
        $url = $app['url_generator']->generate('user-books');
        return $app->redirect($url); 
    }
    
    return $app['twig']->render('user/display.html.twig', array(
        'display' => $displayForm->createView(), //form
    ));
})
->method('GET|POST')
->bind('display');

//REGISTER
//----------------------------------------------------------------------
$app->get('/register', function (Request $request) use($app) {
    
    $regForm = new UserRegister($_POST['username'], $_POST['fullname'], $_POST['password'], $_POST['rpassword'], $_POST['email'], $_POST['remail']);
    
    //register form
    if($request->isMethod('POST')) {
        if($regForm->validate($app['db'])) {
            $password=$app['security.encoder.bcrypt']->encodePassword($regForm->getPass(),'');
            $app['db']->insert('lib_users', array(
                'username'  => $regForm->username,
                'full_name' => $regForm->fullname,
                'password'  => $password,
                'email'     => $regForm->email,
            ));
            
            //sending confirmation
            $send_subject = "Księgozbiór - rejestracja zakończona powodzeniem";
            $send_message = "Witaj {$regForm->fullname}! \n\nRejestracja zakończona powodzeniem. \n\nŚwietnie, że do nas dołączyłeś. Zanim będziesz mógł zalogować się na swoje konto, musisz poczekać na weryfikację przesłanych danych i aktywację konta przez administratora. Z uwagi na to, iż każde zgłoszenie rozpatrywane jest indywidualnie, może to potrwać nawet kilka godzin.\n\nZ poważaniem\nTomek Kaliński";
              
            $message = \Swift_Message::newInstance()
                ->setSubject($send_subject)
                ->setFrom($app['swiftmailer.options']['username'], 'KSIĘGOZBIÓR - Twój Menedżer Książek')
                ->setTo(array($regForm->email))
                ->setBody($send_message);
            $app['mailer']->send($message);
            
            $send_subject = "Księgozbiór - rejestracja";
            $send_message = "Rejestracja: \n\nUżytkownik: {$regForm->username}\n\nImię i nazwisko: {$regForm->fullname}\n\nE-mail: {$regForm->email}\n\n";
            
            $message = \Swift_Message::newInstance()
                ->setSubject($send_subject)
                ->setFrom($app['swiftmailer.options']['username'], 'KSIĘGOZBIÓR - Twój Menedżer Książek')
                ->setTo(array('tomkalon@op.pl'))
                ->setBody($send_message);
            $app['mailer']->send($message);

            $regForm = null;
            
            $url = $app['url_generator']->generate('registered');
            return $app->redirect($url); 
        }
    }
    return $app['twig']->render('login/register.html.twig', array(
        'regform' => array(
            'username'          => $regForm->username,
            'fullname'          => $regForm->fullname,
            'email'             => $regForm->email,
            'regulations'       => $regulations,
            'err_username'      => $regForm->err_username,
            'err_password'      => $regForm->err_password,
            'err_fullname'      => $regForm->err_fullname,
            'err_email'         => $regForm->err_email,
            'err_regulations'   => $regForm->err_regulations,
        )
    ));
})
->bind('register')
->method('GET|POST');


$app->get('/registered', function() use ($app) {
    return $app['twig']->render('login/register_done.html.twig', array(
    ));
})
->bind('registered');


//LOGIN
//----------------------------------------------------------------------
$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login/login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})
->bind('login');

$app->get('/logged', function() use ($app) {

    return $app['twig']->render('login/login_done.html.twig', array(
    ));
})
->bind('logged');

//ERROR
//----------------------------------------------------------------------
$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );
    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});


