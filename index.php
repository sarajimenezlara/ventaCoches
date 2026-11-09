<?php
session_start();
require_once 'db.php';
require_once 'models/repositories/CocheRepository.php';
require_once 'models/repositories/ClienteRepository.php';

// TEMPORAL: mientras los controladores están vacíos, el index enseña
// las vistas directamente con ?v=... y les pasa los datos que necesitan.
// Cuando estén los controladores, esto se cambia por el ?c=...&action=...
$v = isset($_GET['v']) ? $_GET['v'] : 'main';

// TEMPORAL: ClienteRepository aún no tiene getAllClientes()
function todosLosClientes(): array {
    $repoClientes = new ClienteRepository();
    $clientes = [];
    $result = (new db())->connect()->query("SELECT * FROM cliente ORDER BY nombre");
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $repoClientes->mapRow($row);
    }
    return $clientes;
}

switch ($v) {
    case 'login':
        require 'view/loginView.phtml';
        break;
    case 'register':
        require 'view/registerView.phtml';
        break;
    case 'newCoche':
        require 'view/newCocheView.phtml';
        break;
    case 'compra':
        $coches = (new CocheRepository())->getAllCoches();
        $clientes = todosLosClientes();
        require 'view/compraView.phtml';
        break;
    case 'reserva':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $coche = (new CocheRepository())->getCocheById($id);
        $clientes = todosLosClientes();
        require 'view/newReservaView.phtml';
        break;
    default:
        $coches = (new CocheRepository())->getCochesDisponibles();
        require 'view/mainView.phtml';
        break;
}
?>
