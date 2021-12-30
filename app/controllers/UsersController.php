<?php


namespace App\controllers;

use App\model\QueryBuilder;
use App\model\Users;
use Delight\Auth\Auth;
use JasonGrimes\Paginator;
use League\Plates\Engine;
use PDO;


class UsersController
{

    private $user;
    private $db;
    private $templates;
    private $auth;
    private $pdo;

    public function __construct(Users $user, QueryBuilder $db, Engine $templates, Auth $auth, PDO $pdo)
    {
        $this->user = $user;
        $this->db = $db;
        $this->templates = $templates;
        $this->auth = $auth;
        $this->pdo = $pdo;
    }


    public function showUsers()
    {
        $this->user->isLogin();

        $currentUser = $this->auth->getEmail(); //обозначил текущего пользователя
        $admin = $this->user->isAdmin(); //проверка на админа
        $users = $this->db->setPaging('users', 3);

        $allUsers = $this->db->getAll('users');

        $totalUsers = count($allUsers);
        $usersPerPage = 3;
        $currentPage = $_GET['page'] ?? 1;
        $urlPattern = '?page=(:num)';

        $paginator = new Paginator($totalUsers, $usersPerPage, $currentPage, $urlPattern);

        echo $this->templates->render('users', ['admin' => $admin, 'currentUser' => $currentUser, 'users' => $users, 'paginator' => $paginator]);

    }


    public function create()
    {

        $this->user->isLogin();

        if (!$this->user->isAdmin()) {
            redirect_to('/users');
        }

        echo $this->templates->render('create', []);
    }


    public function createUser()
    {

        $data = ['online' => $_POST['online'], 'work' => $_POST['work'], 'phone' => $_POST['phone'], 'adress' => $_POST['adress'], 'vkontakte' => $_POST['vkontakte'], 'telegram' => $_POST['telegram'], 'instagram' => $_POST['instagram']];
        $imageName = time() . $_FILES['image']['name']; //конкатенирую с функцией time() и получается уникальное имя картинки(аватара)
        $filename = $_FILES['image']['tmp_name'];
        $destination = 'images/avatars/' . $imageName;
        $image = ['image' => $imageName];

        $userId = $this->user->adminCreateUser($_POST['email'], $_POST['password'], $_POST['username']);
        $this->db->update('users', $data, $userId);

        if (!$this->user->uploadImage($filename, $destination, $currentImage = null, $image, $userId)) {
            flash()->message('Файл не скопирован на сервер!', 'error');
            redirect_to('/create');
        }

        flash()->message('Пользователь успешно добален! Подтвердите email!', 'success');
        redirect_to('/users');
    }


    public function edit($vars)
    {

        $id = $vars['id'];
        $currentUserId = $this->auth->getUserId();

        $this->user->checkAccess($id, $currentUserId);

        $user = $this->db->getOne('users', $id);

        echo $this->templates->render('edit', ['user' => $user[0]]);
    }


    public function editUser($vars)
    {

        $data = ['username' => $_POST['username'], 'work' => $_POST['work'], 'phone' => $_POST['phone'], 'adress' => $_POST['adress']];

        $this->db->update('users', $data, $vars['id']);

        flash()->message('Данные успешно обновлены!', 'success');
        redirect_to('/users');
    }


    public function security($vars)
    {

        $id = $vars['id'];
        $currentUserId = $this->auth->getUserId();

        $this->user->checkAccess($id, $currentUserId);

        $user = $this->db->getOne('users', $id);

        echo $this->templates->render('security', ['user' => $user[0]]);
    }


    public function securityUser($vars)
    {

        $id = $vars['id'];

        if ($this->user->isAdmin()) {

            $this->user->adminChangePassword($_POST['password'], $id);
        } else {

            $this->user->userChangeEmail($_POST['email'], $id);

            $this->user->userChangePassword($_POST['password'], $_POST['password2'], $id);
        }

        flash()->message('Данные успешно изменены!', 'success');
        redirect_to('/users');

    }


    public function status($vars)
    {

        $id = $vars['id'];
        $currentUserId = $this->auth->getUserId();

        $this->user->checkAccess($id, $currentUserId);

        $user = $this->db->getOne('users', $id);

        echo $this->templates->render('status', ['user' => $user[0]]);
    }


    public function statusUser($vars)
    {

        $data = ['online' => $_POST['online']];

        $this->db->update('users', $data, $vars['id']);

        flash()->message('Данные успешно изменены!', 'success');
        redirect_to('/users');

    }


    public function profile($vars)
    {

        $id = $vars['id'];

        $this->user->isLogin();
        $user = $this->db->getOne('users', $id);

        echo $this->templates->render('profile', ['user' => $user[0]]);
    }


    public function avatar($vars)
    {

        $id = $vars['id'];
        $currentUserId = $this->auth->getUserId();

        $this->user->checkAccess($id, $currentUserId);

        $user = $this->db->getOne('users', $id);

        echo $this->templates->render('avatar', ['user' => $user[0]]);
    }


    public function uploadAvatar($vars)
    {

        $id = $vars['id'];
        $user = $this->db->getOne('users', $id);

        $currentImage = 'images/avatars/' . $user[0]['image'];//нынешняя фотография(аватар) пользователя
        $imageName = time() . $_FILES['image']['name']; //конкатенирую с функцией time() и получается уникальное имя картинки(аватара)
        $filename = $_FILES['image']['tmp_name'];
        $destination = 'images/avatars/' . $imageName;
        $data = ['image' => $imageName];

        if ($this->user->uploadImage($filename, $destination, $currentImage, $data, $id)) {

            flash()->message('Данные успешно изменены!', 'success');
            redirect_to('/users');
        }

        flash()->message('Файл не скопирован на сервер!', 'error');
        redirect_to('/avatar');
    }


    public function deleteUser($vars)
    {

        $id = $vars['id'];
        $currentUserId = $this->auth->getUserId();
        $user = $this->db->getOne('users', $id);

        $currentImage = 'images/avatars/' . $user[0]['image'];//нынешняя фотография(аватар) пользователя

        $this->user->checkAccess($id, $currentUserId);

        $this->db->delete('users', $id);
        unlink($currentImage);//удаляю фотографию(аватар) из сервера

        if (!$this->user->isAdmin()) {
            redirect_to('/reg');
        }

        flash()->message('Пользователь удален!', 'error');
        redirect_to('/users');

    }

}

