<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../Venta.php';

class VentaRepository {
    private $db;

    public function __construct() {
        $this->db = (new db())->connect();
    }

    public function mapRow(array $row): Venta {
        return new Venta(
            (int)$row['id'],
            (int)$row['id_coche'],
            (int)$row['id_cliente'],
            (int)$row['id_usuario'],
            $row['id_reserva'] !== null ? (int)$row['id_reserva'] : null,
            (float)$row['precio'],
            $row['metodo_pago'],
            // En la tabla la columna se llama 'fecha' (en el modelo, fecha_venta)
            $row['fecha']
        );
    }

    public function getVentaById(int $id): ?Venta {
        $stmt = $this->db->prepare("SELECT * FROM venta WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    public function getAllVentas(): array {
        $stmt = $this->db->prepare("SELECT * FROM venta ORDER BY fecha DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $ventas = [];
        while ($row = $result->fetch_assoc()) {
            $ventas[] = $this->mapRow($row);
        }
        return $ventas;
    }

    // Vende un coche. Comprueba antes:
    //   - que el coche existe y no está vendido
    //   - si está reservado, que la venta es para esa reserva y ese cliente
    //   - que tiene una revisión 'apto' (si viene de una reserva, la de esa reserva)
    // Si algo no cuadra lanza InvalidArgumentException con el motivo (para enseñarlo en la vista).
    // Al vender: el coche pasa a 'vendido' y la reserva (si hay) a 'completada'.
    public function createVenta(Venta $venta): bool {
        $id_coche = $venta->getIdCoche();
        $id_cliente = $venta->getIdCliente();
        $id_usuario = $venta->getIdUsuario();
        $id_reserva = $venta->getIdReserva();
        $precio = $venta->getPrecio();
        $metodo_pago = $venta->getMetodoPago();

        // 1. El coche
        $stmt = $this->db->prepare("SELECT situacion FROM coche WHERE id = ?");
        $stmt->bind_param("i", $id_coche);
        $stmt->execute();
        $coche = $stmt->get_result()->fetch_assoc();
        if (!$coche) {
            throw new InvalidArgumentException("El coche no existe.");
        }
        if ($coche['situacion'] === 'vendido') {
            throw new InvalidArgumentException("Este coche ya está vendido.");
        }

        // 2. La reserva
        if ($coche['situacion'] === 'reservado') {
            if ($id_reserva === null) {
                throw new InvalidArgumentException("El coche está reservado: la venta tiene que ser de esa reserva.");
            }
            $stmt = $this->db->prepare("SELECT id_cliente FROM reserva WHERE id = ? AND id_coche = ? AND estado = 'activa'");
            $stmt->bind_param("ii", $id_reserva, $id_coche);
            $stmt->execute();
            $reserva = $stmt->get_result()->fetch_assoc();
            if (!$reserva) {
                throw new InvalidArgumentException("No hay una reserva activa de este coche con ese id.");
            }
            if ((int)$reserva['id_cliente'] !== $id_cliente) {
                throw new InvalidArgumentException("El coche está reservado por otro cliente.");
            }
        }

        // 3. La revisión (hay que revisar el coche antes de nada)
        if ($id_reserva !== null) {
            $stmt = $this->db->prepare("SELECT id FROM revision WHERE id_coche = ? AND id_reserva = ? AND resultado = 'apto'");
            $stmt->bind_param("ii", $id_coche, $id_reserva);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM revision WHERE id_coche = ? AND resultado = 'apto'");
            $stmt->bind_param("i", $id_coche);
        }
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new InvalidArgumentException("El coche no tiene ninguna revisión 'apto'.");
        }

        // 4. Se guarda todo junto (transacción): venta + coche vendido + reserva completada
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO venta (id_coche, id_cliente, id_usuario, id_reserva, precio, metodo_pago) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiids", $id_coche, $id_cliente, $id_usuario, $id_reserva, $precio, $metodo_pago);
            $stmt->execute();

            $stmt = $this->db->prepare("UPDATE coche SET situacion = 'vendido' WHERE id = ?");
            $stmt->bind_param("i", $id_coche);
            $stmt->execute();

            if ($id_reserva !== null) {
                $stmt = $this->db->prepare("UPDATE reserva SET estado = 'completada' WHERE id = ?");
                $stmt->bind_param("i", $id_reserva);
                $stmt->execute();
            }

            $this->db->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}