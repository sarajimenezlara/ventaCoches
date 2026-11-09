<?php
require_once __DIR__ . '/mainController.php';
class UserController extends MainController{
    public function run(string $action){
        switch($action){
            case 'login':
                $this->login();
                break;
            case 'register':
                $this->register();
                break;
            case 'logout':
                $this->logout();
                break;
            default:
                $this->redirect('index.php');
        }
    }

    private function login(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = $this->userRepo->getUserByUsername($username);
            if($user && password_verify($password, $user->getPassword())){
                $_SESSION['user_id'] = $user->getId();
                $this->redirect('index.php');
            } else {
                $error = "Usuario o contraseña incorrectos.";
                require __DIR__ . '/../view/loginView.phtml';
            }
        } else {
            require __DIR__ . '/../view/loginView.phtml';
        }
    }

    private function register(){
        $error = null;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $username = trim($_POST['username'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if($username === '' || $nombre === '' || $apellidos === '' || $email === '' || $password === ''){
                $error = "Rellena todos los campos.";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $error = "El email no es válido.";
            } else {
                $user = new User(0, $username, $nombre, $apellidos, $email, $password, 'trabajador');
                if($this->userRepo->createUser($user)){
                    $this->redirect('index.php?c=user&action=login');
                } else {
                    // devuelve false si el username o el email ya existen
                    $error = "Ese usuario o ese email ya están registrados.";
                }
            }
        }
        require __DIR__ . '/../view/registerView.phtml';
    }

    private function logout(){
        session_unset();
        session_destroy();
        $this->redirect('index.php');
    }
}
?>
