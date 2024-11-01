<?php

namespace app\lib;

use \app\lib\Response;
use \app\lib\Session;
use \app\lib\UserManager as Users;
use \app\lib\Validators;
use \app\lib\Notes;

class Controller
{

    protected Response $response;
    protected Session $session;
    protected Users $users;
    protected Validators $validators;
    protected Notes $notesManager;

    public function __construct()
    {
        $this->response = new Response();
        $this->session = new Session();
        $this->users = new Users();
        $this->validators = new Validators();
        $this->notesManager = new Notes();
    }
    /**
     * transferring data to the page and displaying Index
     * @return void
     */
    public function index(): void
    {
        $this->response->render('index',['errors' => []]);
    }

    /**
     * User registration
     * data reading, validation and saving
     *  Redirect to note_page.php || or on error /index_page.php
     * @return void
     */
    public function addUser() : void
    {
        $data = [
            'login' => filter_input(INPUT_POST, 'reglogin'),
            'password' => filter_input(INPUT_POST, 'regpass'),
            'repassword' => filter_input(INPUT_POST, 'reregpass'),
        ];

        $errors = $this->validators->validateInfoUser($data);
        if (!empty($errors)) {
            $this->response->render('index', ['errorsAdd' => $errors]);
        }else {
            $res = $this->users->validationAddUser($data['login']);
            if (!empty($res)){
                $this->response->render('index', ['errorsAdd' => $res]);
            }else {
                $this->users->addUser([
                    'login' => $data['login'],
                    'password' => $data['password'],
                ]);
                $this->session->write('login', $data['login']);
                $this->response->render('note', [
                    'login' => $data['login'],
                ]);
            }
        }
    }

    /**
     * User login
     *  data reading, validation and saving
     *   Redirect to note_page.php || or on error /index_page.php
     * @return void
     */
    public function loginUser() : void
    {
        $data = [
            'login' => filter_input(INPUT_POST, 'login'),
            'password' => filter_input(INPUT_POST, 'pass'),
            ];
        if (empty($data['login'])) {
            $this->response->render('index',[
                'messageNoUser' => 'No user entered',
            ]);
        }else {
            $res = $this->users->validationLoginUser($data['login'],$data['password']);
            if (!empty($res)){
                $this->response->render('index', ['errorsLogin' => $res]);
            }else {
                $this->session->write('login', $data['login']);
                $notes = $this->notesManager->notesUser($data['login']);
                $this->response->render('note', [
                    'login' => $data['login'],
                    'notes' => $notes,
                ]);
            }
        }
    }

    /**
     * @return void
     */
    public function signOutUser(): void
    {
        $this->session->delete('login');
        $this->index();
    }

    /**
     * @return void
     */
    public function note(): void
    {
        $login = $this->session->get('login');
        $notes = $this->notesManager->notesUser($login);
        $this->response->render('note',[
            'login' => $login,
            'notes' => $notes,
        ]);
    }

    /**
     * @return void
     */
    public function addNote() : void
    {
        $note = filter_input(INPUT_POST, 'note');
        if(!empty($note)) {
            $login = $this->session->get('login');

            $this->notesManager->addNote($login,$note);
        }
        $this->response->redirect(Router::url());
    }

    /**
     * @return void
     */
    public function delNote() : void
    {
        $id = filter_input(INPUT_POST, 'idDel');
        if(is_numeric($id)) {
            $login = $this->session->get('login');
            $this->notesManager->delNote($login,$id);
        }
        $this->response->redirect(Router::url());
    }
}