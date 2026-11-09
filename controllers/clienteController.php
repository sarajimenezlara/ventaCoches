<?php
require_once __DIR__ . '/mainController.php';
require_once __DIR__ . '/../models/repositories/ClienteRepository.php';
class ClienteController extends MainController{
    private ClienteRepository $clienteRepo;

    public function __construct(){
        parent::__construct();
        $this->clienteRepo = new ClienteRepository();
    }

    public function run(string $action){
        // Cualquier trabajador (o el admin) puede gestionar clientes
        $this->onlyLogin();
        switch($action){
            case 'list':
                $this->list();
                break;
            case 'create':
                $this->create();
                break;
            case 'edit':
                $this->edit();
                break;
            default:
                $this->redirect('index.php?c=cliente&action=list');
        }
    }

    private function list(){
        $clientes = $this->clienteRepo->getAllClientes();
        require __DIR__ . '/../view/mainView.phtml';
    }

    private function create(){
        $error = null;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            [$datos, $error] = $this->validarFormulario();

            if($error === null){
                $cliente = new Cliente(
                    0,
                    $datos['tipo'],
                    $datos['nombre'],
                    $datos['apellidos'],
                    $datos['dni'],
                    $datos['telefono'],
                    $datos['email'],
                    $datos['direccion']
                );
                if($this->clienteRepo->createCliente($cliente)){
                    $this->redirect('index.php?c=cliente&action=list');
                }
                $error = "Ya existe un cliente con ese DNI o email.";
            }
        }
        require __DIR__ . '/../view/newClienteView.phtml';
    }

    private function edit(){
        $error = null;
        $id = (int)($_GET['id'] ?? 0);
        $cliente = $this->clienteRepo->getClienteById($id);
        if(!$cliente){
            $this->redirect('index.php?c=cliente&action=list');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            [$datos, $error] = $this->validarFormulario();

            if($error === null){
                $cliente->setTipo($datos['tipo']);
                $cliente->setNombre($datos['nombre']);
                $cliente->setApellidos($datos['apellidos']);
                $cliente->setDni($datos['dni']);
                $cliente->setTelefono($datos['telefono']);
                $cliente->setEmail($datos['email']);
                $cliente->setDireccion($datos['direccion']);

                if($this->clienteRepo->updateCliente($cliente)){
                    $this->redirect('index.php?c=cliente&action=list');
                }
                $error = "Ya existe otro cliente con ese DNI o email.";
            }
        }
        require __DIR__ . '/../view/editClienteView.phtml';
    }

    // Lee y comprueba el formulario de cliente (lo usan create y edit).
    // Devuelve [datos, error]; si todo está bien, error es null.
    private function validarFormulario(): array {
        $datos = [
            'tipo'      => $_POST['tipo'] ?? 'particular',
            'nombre'    => trim($_POST['nombre'] ?? ''),
            'dni'       => strtoupper(trim($_POST['dni'] ?? '')),
            'telefono'  => trim($_POST['telefono'] ?? ''),
            // Los opcionales vacíos se guardan como NULL: así dos clientes
            // sin email no chocan con el UNIQUE del email
            'apellidos' => trim($_POST['apellidos'] ?? '') ?: null,
            'email'     => trim($_POST['email'] ?? '') ?: null,
            'direccion' => trim($_POST['direccion'] ?? '') ?: null,
        ];

        $error = null;
        if($datos['nombre'] === '' || $datos['dni'] === '' || $datos['telefono'] === ''){
            $error = "Rellena nombre, DNI y teléfono.";
        } elseif(!in_array($datos['tipo'], ['particular', 'empresa'])){
            $error = "El tipo de cliente no es válido.";
        } elseif($datos['email'] !== null && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)){
            $error = "El email no es válido.";
        }
        return [$datos, $error];
    }
}
?>
