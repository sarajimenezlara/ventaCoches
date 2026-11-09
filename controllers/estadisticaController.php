<?php
require_once __DIR__ . '/mainController.php';
require_once __DIR__ . '/../models/repositories/EstadisticasRepository.php';

// Estadísticas: solo las ve el admin (el trabajador no)
class EstadisticaController extends MainController{
    private EstadisticasRepository $estadisticasRepo;

    public function __construct(){
        parent::__construct();
        $this->estadisticasRepo = new EstadisticasRepository();
    }

    public function run(string $action){
        $this->onlyAdmin();
        $totales = $this->estadisticasRepo->getTotales();
        $trabajadores = $this->estadisticasRepo->getPorTrabajador();
        require __DIR__ . '/../view/estadisticasView.phtml';
    }
}
