<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/repositories/UserRepository.php';

class MainController{
    protected UserRepository $userRepo;
    protected ?User $user = null;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }
    public function run(string $action){}

    protected function redirect(string $url)
    {
        header("Location: $url");
        exit();
    }

    protected function onlyLogin(){
        if(isset($_SESSION['user_id'])){
            $this->user = $this->userRepo->getUserById($_SESSION['user_id']);
        }
        if(!$this->user){
            session_unset();
            $this->redirect('index.php?c=user&action=login');
        }
    }
    protected function onlyAdmin() {
        $this->onlyLogin();
        if ($this->user->getRol() !== 'admin') {
            $this->redirect('index.php');                
        }

    }

}
?>