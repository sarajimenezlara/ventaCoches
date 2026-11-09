<?php
require_once __DIR__ . '/mainController.php';
require_once __DIR__ . '/../models/repositories/VentaRepository.php';
require_once __DIR__ . '/../models/repositories/ReservaRepository.php';
require_once __DIR__ . '/../models/repositories/ClienteRepository.php';
require_once __DIR__ . '/../models/repositories/CocheRepository.php';

// El concesionario vende un coche a un cliente.
// Si el coche está reservado, la venta es para el cliente de la reserva.
class VentaController extends MainController{
    private VentaRepository $ventaRepo;
    private ReservaRepository $reservaRepo;
    private ClienteRepository $clienteRepo;
    private CocheRepository $cocheRepo;

    public function __construct(){
        parent::__construct();
        $this->ventaRepo = new VentaRepository();
        $this->reservaRepo = new ReservaRepository();
        $this->clienteRepo = new ClienteRepository();
        $this->cocheRepo = new CocheRepository();
    }

    public function run(string $action){
        $this->onlyLogin();
        switch($action){
            case 'create':
                $this->create();
                break;
            default:
                $this->redirect('index.php?c=coche&action=list');
        }
    }

    // Se llega desde el botón "Vender" de un coche: ?c=venta&action=create&id=X
    private function create(){
        $error = null;
        $idCoche = (int)($_POST['id_coche'] ?? $_GET['id'] ?? 0);
        $coche = $this->cocheRepo->getCocheById($idCoche);
        if(!$coche || $coche->getSituacion() === 'vendido'){
            $this->redirect('index.php?c=coche&action=list');
        }

        // Si está reservado, se vende al cliente de la reserva
        $reserva = $this->reservaRepo->getReservaActivaByCoche($idCoche);
        $clienteReserva = $reserva ? $this->clienteRepo->getClienteById($reserva->getIdCliente()) : null;

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            [$datos, $error] = $this->validarFormulario();
            if($error === null){
                $venta = new Venta(
                    0,
                    $idCoche,
                    $reserva ? $reserva->getIdCliente() : $datos['id_cliente'],
                    $this->user->getId(),               // el trabajador que vende
                    $reserva ? $reserva->getId() : null,
                    $datos['precio'],
                    $datos['metodo_pago'],
                    ''                                  // la fecha la pone la BD
                );
                try {
                    // createVenta pone el coche en 'vendido' y la reserva en 'completada'
                    if($this->ventaRepo->createVenta($venta)){
                        $this->redirect('index.php?c=coche&action=list');
                    }
                    $error = "No se ha podido guardar la venta.";
                } catch (InvalidArgumentException $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $clientes = $this->clienteRepo->getAllClientes();
        require __DIR__ . '/../view/newVentaView.phtml';
    }

    private function validarFormulario(): array{
        $datos = [
            'id_cliente'  => (int)($_POST['id_cliente'] ?? 0),
            'precio'      => (float)($_POST['precio'] ?? 0),
            'metodo_pago' => $_POST['metodo_pago'] ?? '',
        ];

        $error = null;
        if($datos['precio'] <= 0){
            $error = "El precio tiene que ser mayor que 0.";
        } elseif(!in_array($datos['metodo_pago'], ['efectivo', 'tarjeta', 'transferencia'])){
            $error = "El método de pago no es válido.";
        } elseif(!isset($_POST['tiene_reserva']) && $this->clienteRepo->getClienteById($datos['id_cliente']) === null){
            // Sin reserva hay que elegir el cliente
            $error = "Elige el cliente.";
        }
        return [$datos, $error];
    }
}
