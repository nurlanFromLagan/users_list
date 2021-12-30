<?php


namespace App\model;


use Delight\Auth\Auth;
use PDO;

class Users
{

    private $auth;
    private $db;
    private $pdo;


    public function __construct(PDO $pdo, Auth $auth, QueryBuilder $db)
    {
        $this->auth = $auth;
        $this->db = $db;
        $this->pdo = $pdo;
    }



    /**
     * Проверяет авторизован ли пользователь
     */
    public function  isLogin () {

        if (!$this->auth->isLoggedIn()) {
            redirect_to('/login');
        }
    }


    /**
     * Проверяет является ли текущий пользователь админом
     */
    public function isAdmin () {

        $userId = $this->auth->getUserId();

        if ($this->auth->admin()->doesUserHaveRole($userId, \Delight\Auth\Role::ADMIN)) {
            return true;
        }
        return false;
    }


    /**
     * Проверяет является ли пользователь текущим или админом
     * @param int $id - id пользователя
     * @param int $currentUserId - id текущего пользователя
     */
    public function checkAccess (int $id, int $currentUserId) {

        $this->isLogin();

        if (!$this->isAdmin() AND $id != $currentUserId) {
            redirect_to('/users');
        }
    }


    /**
     * Создает пользователя от имени админа
     * @param $email
     * @param $password
     * @param $username
     * @return int
     */
    public function adminCreateUser ($email, $password, $username) {

        try {
            $userId = $this->auth->admin()->createUser($email, $password, $username);

            return $userId;
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->message('Invalid email address', 'error');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->message('Invalid password', 'error');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->message('User already exists', 'error');
        }

        redirect_to('/create');
    }


    /**
     * Загружает картинку
     * @param $filename - название файла
     * @param $destination - путь, куда сохранять файл
     * @param $currentImage - текущая картинка
     * @param array $data - ассоц.массив с названием колонки в БД и значением
     * @param $id - id пользователя
     * @return bool
     */
    public function uploadImage ($filename, $destination, $currentImage, array $data, int $id) {

        if (move_uploaded_file($filename, $destination)) {

            unlink($currentImage);//удаляю старую фотографию(аватар) из сервера
            $this->db->update('users', $data, $id);//загружаю новое название аватара в бд

            return true;
        }
        return false;
    }


    /**
     * Изменение email текущим пользователем
     * @param $newEmail - новый email
     * @param int $id - id пользователя
     * @return bool
     */
    public function userChangeEmail ($newEmail, int $id) {

        try {
            $this->auth->changeEmail($newEmail, function ($selector, $token) {
//                echo 'Send ' . $selector . ' and ' . $token . ' to the user (e.g. via email to the *new* address)';
//                echo '  For emails, consider using the mail(...) function, Symfony Mailer, Swiftmailer, PHPMailer, etc.';
//                echo '  For SMS, consider using a third-party service and a compatible SDK';

            });

            return true;
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->message('Invalid email address', 'error');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->message('Email address already exists', 'error');
        }
        catch (\Delight\Auth\EmailNotVerifiedException $e) {
            flash()->message('Account not verified', 'error');
        }
        catch (\Delight\Auth\NotLoggedInException $e) {
            flash()->message('Not logged in', 'error');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->message('Too many requests', 'error');
        }

        redirect_to('/security/' . $id);
    }


    /**
     * Изменение пароля пользователем
     * @param $oldPassword
     * @param $newPassword
     * @param int $id
     * @return bool
     */
    public function userChangePassword ($oldPassword, $newPassword, int $id) {

        try {
            $this->auth->changePassword($oldPassword, $newPassword);
            return true;
        }
        catch (\Delight\Auth\NotLoggedInException $e) {
            flash()->message('Not logged in', 'error');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->message('Invalid password(s)', 'error');
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->message('Too many requests', 'error');
        }

        redirect_to('/security/' . $id);
    }


    /**
     * Изменение пароля админом
     * @param $newPassword
     * @param int $id
     * @return bool
     */
    public function adminChangePassword ($newPassword, int $id) {

        try {
            $this->auth->admin()->changePasswordForUserById($id, $newPassword);
            return true;
        }
        catch (\Delight\Auth\UnknownIdException $e) {
            flash()->message('Invalid password', 'error');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->message('Invalid password', 'error');
        }

        redirect_to('/security/' . $id);
    }
}