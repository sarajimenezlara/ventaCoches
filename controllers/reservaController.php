<?php
require_once __DIR__ . '/mainController.php';
require_once __DIR__ . '/../models/repositories/ReservaRepository.php';
require_once __DIR__ . '/../models/repositories/ClienteRepository.php';
require_once __DIR__ . '/../models/repositories/CocheRepository.php';

// La reserva la apunta un trabajador (no se paga nada).
// Después se revisa el coche y, si el cliente se lo queda, se hace la venta.
class ReservaController extends MainController{
    private ReservaRepository $reservaRepo;
    private ClienteRepository $clienteRepo;
    private CocheRepository $cocheRepo;

    public function __construct(){
        parent::__construct();
        $this->reservaRepo = new ReservaRepository();
        $this->clienteRepo = new ClienteRepository();
        $this->cocheRepo = new CocheRepository();
    }

    public function run(string $action){
        $this->onlyLogin();
        switch($action){
            case 'list':
                $this->list();
                break;
            case 'create':
                $this->create();
                break;
            case 'cancel':
                $this->cancel();
                break;
            default:
                $this->redirect('index.php?c=reserva&action=list');
        }
    }

    // Reservas pendientes (de revisar el coche o de que el cliente decida)
    private function list(){
        $reservas = $this->reservaRepo->getReservasActivas();
        require __DIR__ . '/../view/mainView.phtml';
    }

    // Se llega desde el botón "Reservar" de un coche: ?c=reserva&action=create&id=X
    private function create(){
        $error = null;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            [$datos, $error] = $this->validarFormulario();
            if($error === null){
                $reserva = new Reserva(
                    0,
                    $datos['id_coche'],
                    $datos['id_cliente'],
                    $this->user->getId()    // el trabajador que la apunta
                    // la fecha la pone la BD y el estado empieza en 'activa'
                );
                if($this->reservaRepo->createReserva($reserva)){
                    $this->redirect('index.php?c=reserva&action=list');
                }
                // createReserva devuelve false si el coche ya no estaba disponible
                $error = "Ese coche ya no está disponible.";
            }
            $idCoche = (int)($_POST['id_coche'] ?? 0);
        } else {
            $idCoche = (int)($_GET['id'] ?? 0);
        }

        // La vista enseña ese coche y un desplegable con los clientes
        $coche = $this->cocheRepo->getCocheById($idCoche);
        $clientes = $this->clienteRepo->getAllClientes();
        require __DIR__ . '/../view/newReservaView.phtml';
    }

    // El cliente ya no lo quiere: la reserva pasa a 'cancelada' y el coche vuelve a 'disponible'.
    // Solo por POST, para que no se cancele nada solo con abrir un enlace
    private function cancel(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $id = (int)($_POST['id'] ?? 0);
            $this->reservaRepo->cancelarReserva($id);
        }
        $this->redirect('index.php?c=reserva&action=list');
    }

    // Lee y comprueba el formulario de reserva.
    // Devuelve [datos, error]; si todo está bien, error es null.
    private function validarFormulario(): array{
        $datos = [
            'id_cliente' => (int)($_POST['id_cliente'] ?? 0),
            'id_coche'   => (int)($_POST['id_coche'] ?? 0),
        ];

        $error = null;
        if($datos['id_cliente'] <= 0 || $datos['id_coche'] <= 0){
            $error = "Elige el cliente.";
        } elseif($this->clienteRepo->getClienteById($datos['id_cliente']) === null){
            $error = "Ese cliente no existe.";
        } elseif($this->cocheRepo->getCocheById($datos['id_coche']) === null){
            $error = "Ese coche no existe.";
        }
        return [$datos, $error];
    }
}
