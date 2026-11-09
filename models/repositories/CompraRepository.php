<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../Compra.php';

class CompraRepository {
    private $db;

    public function __construct() {
        $this->db = (new db())->connect();
    }

    public function mapRow(array $row): Compra {
        return new Compra(
            (int)$row['id'],
            (int)$row['id_coche'],
            (int)$row['id_cliente'],
            (int)$row['id_usuario'],
            (float)$row['precio'],
            $row['metodo_pago'],
            $row['fecha'],
            (bool)$row['tenia_revision']
        );
    }

    public function getCompraById(int $id): ?Compra {
        $stmt = $this->db->prepare("SELECT * FROM compra WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    public function getAllCompras(): array {
        $stmt = $this->db->prepare("SELECT * FROM compra ORDER BY fecha DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $compras = [];
        while ($row = $result->fetch_assoc()) {
            $compras[] = $this->mapRow($row);
        }
        return $compras;
    }

    // Devuelve false si ese coche ya tenía una compra (id_coche es UNIQUE)
    public function createCompra(Compra $compra): bool {
        $stmt = $this->db->prepare("INSERT INTO compra (id_coche, id_cliente, id_usuario, precio, metodo_pago, tenia_revision) VALUES (?, ?, ?, ?, ?, ?)");
        $id_coche = $compra->getIdCoche();
        $id_cliente = $compra->getIdCliente();
        $id_usuario = $compra->getIdUsuario();
        $precio = $compra->getPrecio();
        $metodo_pago = $compra->getMetodoPago();
        // En la BD es TINYINT: se guarda 1 o 0
        $tenia_revision = $compra->getTeniaRevision() ? 1 : 0;
        $stmt->bind_param("iiidsi", $id_coche, $id_cliente, $id_usuario, $precio, $metodo_pago, $tenia_revision);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                return false;
            }
            throw $e;
        }
    }
}
?>
