<?php
require_once __DIR__ . '/mainController.php';
require_once __DIR__ . '/../models/repositories/CompraRepository.php';
require_once __DIR__ . '/../models/repositories/ClienteRepository.php';
require_once __DIR__ . '/../models/repositories/CocheRepository.php';


class CompraController extends MainController{
    private CompraRepository $compraRepo;
    private ClienteRepository $clienteRepo;
    private CocheRepository $cocheRepo;

    public function __construct(){
        parent::__construct();
        $this->compraRepo = new CompraRepository();
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
                $this->redirect('index.php?c=compra&action=create');
        }
    }

    private function create(){
        $error = null;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            [$datos, $error] = $this->validarFormulario();
            if($error === null){
                $compra = new Compra(
                    0,
                    $datos['id_coche'],
                    $datos['id_cliente'],
                    $this->user->getId(),   // el trabajador que ha iniciado sesión
                    $datos['precio'],
                    $datos['metodo_pago'],
                    null,                   // la fecha la pone la BD
                    $datos['tenia_revision']
                );
                if($this->compraRepo->createCompra($compra)){
                    // Se vuelve a los coches, donde está el que se acaba de comprar
                    $this->redirect('index.php?c=coche&action=list');
                }
                $error = "Ese coche ya tiene una compra registrada.";
            }
        }
        // Para los desplegables del formulario
        $clientes = $this->clienteRepo->getAllClientes();
        $coches = $this->cocheRepo->getAllCoches();
        require __DIR__ . '/../view/compraView.phtml';
    }

    // Lee y comprueba el formulario de compra.
    // Devuelve [datos, error]; si todo está bien, error es null.
    private function validarFormulario(): array{
        $datos = [
            'id_cliente'     => (int)($_POST['id_cliente'] ?? 0),
            'id_coche'       => (int)($_POST['id_coche'] ?? 0),
            'precio'         => (float)($_POST['precio'] ?? 0),
            'metodo_pago'    => $_POST['metodo_pago'] ?? '',
            // Un checkbox sin marcar no llega en el $_POST
            'tenia_revision' => isset($_POST['tenia_revision']),
        ];

        $error = null;
        if($datos['id_cliente'] <= 0 || $datos['id_coche'] <= 0){
            $error = "Elige el cliente y el coche.";
        } elseif($datos['precio'] <= 0){
            $error = "El precio tiene que ser mayor que 0.";
        } elseif(!in_array($datos['metodo_pago'], ['efectivo', 'tarjeta', 'transferencia'])){
            $error = "El método de pago no es válido.";
        } elseif($this->clienteRepo->getClienteById($datos['id_cliente']) === null){
            $error = "Ese cliente no existe.";
        } elseif($this->cocheRepo->getCocheById($datos['id_coche']) === null){
            $error = "Ese coche no existe.";
        }
        return [$datos, $error];
    }
}
