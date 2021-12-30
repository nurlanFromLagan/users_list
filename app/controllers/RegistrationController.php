<?php


namespace App\controllers;

use Delight\Auth\Auth;
use League\Plates\Engine;
use PDO;
use SimpleMail;
use Tamtamchik\SimpleFlash\Flash;



class RegistrationController
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


    public function registration () {

        // Render a template
        echo $this->templates->render('register', []);
    }


    public function registrationUser () {

        try {
            $userId = $this->auth->register($_POST['email'], $_POST['password'], $_POST['username'], function ($selector, $token) {

                $email = $_POST['email'];
                $name = $_POST['username'];
                $fromEmail = 'admin@mail';
                $fromName = 'admiN';
                $subject = 'Подтверждение пользователя';
                $message = "Для подтверждения перейдите по ссылке http://exam/verification?selector={$selector}&token={$token}";

                SimpleMail::make()
                    ->setTo($email, $name)
                    ->setFrom($fromEmail, $fromName)
                    ->setSubject($subject)
                    ->setMessage($message)
                    ->send();
            });

            flash()->message('Регистрация прошла успешно! Подтвердите email!', 'success');
            redirect_to('/login');
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->message('<strong>Уведомление!</strong> Некорректно введен email!', 'error');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->message('<strong>Уведомление!</strong> Некорректно введен пароль!', 'error');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->message('<strong>Уведомление!</strong> Этот эл. адрес уже занят другим пользователем!', 'error');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->message('<strong>Уведомление!</strong> Слишком много запросов!', 'error');
        }

        redirect_to('/reg');
    }


}