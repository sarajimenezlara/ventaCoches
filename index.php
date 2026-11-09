<?php
session_start();
require_once 'db.php';

// ?c=controlador&action=accion
$c = isset($_GET['c']) ? $_GET['c'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($c) {
    case 'user':
        require_once 'controllers/userController.php';
        $controller = new UserController();
        break;
    case 'cliente':
        require_once 'controllers/clienteController.php';
        $controller = new ClienteController();
        break;
    case 'coche':
        require_once 'controllers/cocheController.php';
        $controller = new CocheController();
        break;
    case 'compra':
        require_once 'controllers/compraController.php';
        $controller = new CompraController();
        break;
    case 'reserva':
        require_once 'controllers/reservaController.php';
        $controller = new ReservaController();
        break;
    case 'venta':
        require_once 'controllers/ventaController.php';
        $controller = new VentaController();
        break;
    case 'estadistica':
        require_once 'controllers/estadisticaController.php';
        $controller = new EstadisticaController();
        break;
    default:
        // Página principal (pública): los coches disponibles
        require_once 'models/repositories/CocheRepository.php';
        $coches = (new CocheRepository())->getCochesDisponibles();
        require 'view/mainView.phtml';
        exit();
}

$controller->run($action);
