<?php
require_once __DIR__ . '/mainController.php';
require_once __DIR__ . '/../models/repositories/ReservaRepository.php';

class ReservaController extends MainController{
    private ReservaRepository $reservaRepo;

    public function __construct(){
        parent::__construct();
        $this->reservaRepo = new ReservaRepository();
    }

    public function run(string $action){
        $this->onlyLogin();
        switch($action){
            case 'create':
                $this->create();
                break;
            default:
                $this->redirect('index.php?c=reserva&action=create');
        }
    }

    private function create(){
        $error = null;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            [$datos, $error] = $this->validarFormulario();
            if($error === null){
                $reserva = new Reserva(
                    0,
                    $datos['id_cliente'],
                    $datos['id_coche'],
                    $datos['fecha_reserva'],
                    $datos['hora_reserva']
                );
                if($this->reservaRepo->createReserva($reserva)){
                    $this->redirect('index.php?c=reserva&action=list');
                }
                $error = "Error al crear la reserva.";
            }
        }
        // Para los desplegables del formulario
        $clientes = $this->clienteRepo->getAllClientes();
        $coches = $this->cocheRepo->getAllCoches();
        require __DIR__ . '/../view/reservaView.phtml';
    }

    // Lee y comprueba el formulario de reserva.
    // Devuelve [datos, error]; si todo está bien, error es null.
    private function validarFormulario(): array{
        $datos = [
            'id_cliente'     => (int)($_POST['id_cliente'] ?? 0),
            'id_coche'       => (int)($_POST['id_coche'] ?? 0),
            'fecha_reserva'  => $_POST['fecha_reserva'] ?? '',
            'hora_reserva'   => $_POST['hora_reserva'] ?? '',
        ];

        $error = null;
        if($datos['id_cliente'] <= 0 || $datos['id_coche'] <= 0){
            $error = "Elige el cliente y el coche.";
        } elseif($datos['fecha_reserva'] === ''){
            $error = "Introduce la fecha de la reserva.";
        } elseif($datos['hora_reserva'] === ''){
            $error = "Introduce la hora de la reserva.";
        }
        return [$datos, $error];
    }
}
