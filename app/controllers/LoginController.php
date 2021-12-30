<?php


namespace App\controllers;

use Delight\Auth\Auth;
use League\Plates\Engine;
use PDO;
use Tamtamchik\SimpleFlash\Flash;
use App\model\QueryBuilder;



class LoginController
{
    private $templates;
    private $auth;
    private $pdo;

    public function __construct(Engine $templates, Auth $auth, PDO $pdo)
    {
        $this->templates = $templates;
        $this->auth = $auth;
        $this->pdo = $pdo;
    }



    public function login () {

        echo $this->templates->render('login',[]);
    }


    public function loginUser () {

        try {
            $this->auth->login($_POST['email'], $_POST['password']);

            redirect_to('/users');
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->message('Wrong email address', 'error');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->message('Wrong password', 'error');
        }
        catch (\Delight\Auth\EmailNotVerifiedException $e) {
            flash()->message('Email not verified', 'error');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->message('Too many requests', 'error');
        }

        echo 'dddd';
        redirect_to('/login');
    }


    public function verification () {

        try {
            $this->auth->confirmEmail($_GET['selector'], $_GET['token']);

            flash()->message('Email address has been verified', 'success');
        }
        catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            flash()->message('Invalid token', 'error');
        }
        catch (\Delight\Auth\TokenExpiredException $e) {
            flash()->message('Token expired', 'error');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->message('Email address already exists', 'error');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->message('Too many requests', 'error');
        }

        redirect_to('/login');
    }


    public function logout () {

        $this->auth->logOut();

        flash()->message('Вы вышли из аккаунта!', 'error');
        redirect_to('/login');
    }


}