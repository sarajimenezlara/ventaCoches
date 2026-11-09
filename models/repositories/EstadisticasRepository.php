<?php
require_once __DIR__ . '/../../db.php';

// Solo para el admin (el controlador comprueba el rol con onlyAdmin)
class EstadisticasRepository {
    private $db;

    public function __construct() {
        $this->db = (new db())->connect();
    }

    // Cuánto se ha ganado vendiendo, cuánto se ha gastado comprando y la diferencia
    public function getTotales(): array {
        $ventas = $this->db->query("SELECT COUNT(*) AS num, COALESCE(SUM(precio), 0) AS total FROM venta")->fetch_assoc();
        $compras = $this->db->query("SELECT COUNT(*) AS num, COALESCE(SUM(precio), 0) AS total FROM compra")->fetch_assoc();
        return [
            'num_ventas'  => (int)$ventas['num'],
            'ventas'      => (float)$ventas['total'],
            'num_compras' => (int)$compras['num'],
            'compras'     => (float)$compras['total'],
            'beneficio'   => (float)$ventas['total'] - (float)$compras['total'],
        ];
    }

    // Lo que ha vendido y comprado cada trabajador (también salen los que llevan 0)
    public function getPorTrabajador(): array {
        $sql = "SELECT u.username, u.nombre, u.apellidos, u.rol,
                       (SELECT COUNT(*) FROM venta v WHERE v.id_usuario = u.id) AS coches_vendidos,
                       (SELECT COALESCE(SUM(v.precio), 0) FROM venta v WHERE v.id_usuario = u.id) AS total_vendido,
                       (SELECT COUNT(*) FROM compra c WHERE c.id_usuario = u.id) AS coches_comprados,
                       (SELECT COALESCE(SUM(c.precio), 0) FROM compra c WHERE c.id_usuario = u.id) AS total_comprado
                FROM usuario u
                ORDER BY total_vendido DESC";
        $result = $this->db->query($sql);
        $filas = [];
        while ($row = $result->fetch_assoc()) {
            $filas[] = $row;
        }
        return $filas;
    }
}
