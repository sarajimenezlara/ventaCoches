<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../Reserva.php';

class ReservaRepository {
    private $db;

    public function __construct() {
        $this->db = (new db())->connect();
    }

    public function mapRow(array $row): Reserva {
        return new Reserva(
            (int)$row['id'],
            (int)$row['id_coche'],
            (int)$row['id_cliente'],
            $row['id_usuario'] !== null ? (int)$row['id_usuario'] : null,
            $row['fecha_reserva'],
            $row['estado'],
            $row['fecha_cancelacion']
        );
    }

    public function getReservaById(int $id): ?Reserva {
        $stmt = $this->db->prepare("SELECT * FROM reserva WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    public function getAllReservas(): array {
        $stmt = $this->db->prepare("SELECT * FROM reserva ORDER BY fecha_reserva DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $this->mapRow($row);
        }
        return $reservas;
    }

    // Las que están pendientes de que el cliente decida
    public function getReservasActivas(): array {
        $stmt = $this->db->prepare("SELECT * FROM reserva WHERE estado = 'activa' ORDER BY fecha_reserva");
        $stmt->execute();
        $result = $stmt->get_result();
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $this->mapRow($row);
        }
        return $reservas;
    }

    // La reserva activa de un coche (o null si no tiene)
    public function getReservaActivaByCoche(int $id_coche): ?Reserva {
        $stmt = $this->db->prepare("SELECT * FROM reserva WHERE id_coche = ? AND estado = 'activa'");
        $stmt->bind_param("i", $id_coche);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    // Apunta la reserva (sin pago) y marca el coche como reservado.
    // Devuelve false si el coche no estaba disponible.
    public function createReserva(Reserva $reserva): bool {
        $id_coche = $reserva->getIdCoche();
        $id_cliente = $reserva->getIdCliente();
        $id_usuario = $reserva->getIdUsuario();

        // Las dos cosas van juntas: si una falla, se deshace todo (transacción)
        $this->db->begin_transaction();
        try {
            // Solo se puede reservar si está disponible (así no se reserva dos veces)
            $stmt = $this->db->prepare("UPDATE coche SET situacion = 'reservado' WHERE id = ? AND situacion = 'disponible'");
            $stmt->bind_param("i", $id_coche);
            $stmt->execute();
            if ($stmt->affected_rows === 0) {
                $this->db->rollback();
                return false;
            }

            $stmt = $this->db->prepare("INSERT INTO reserva (id_coche, id_cliente, id_usuario) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $id_coche, $id_cliente, $id_usuario);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            // Por ejemplo, un id_cliente que no existe
            $this->db->rollback();
            return false;
        }
    }

    // El cliente ya no lo quiere: se cancela y el coche vuelve a estar disponible
    public function cancelarReserva(int $id): bool {
        $reserva = $this->getReservaById($id);
        if ($reserva === null || $reserva->getEstado() !== 'activa') {
            return false;
        }
        $id_coche = $reserva->getIdCoche();
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("UPDATE reserva SET estado = 'cancelada', fecha_cancelacion = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $stmt = $this->db->prepare("UPDATE coche SET situacion = 'disponible' WHERE id = ?");
            $stmt->bind_param("i", $id_coche);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    // El cliente se queda el coche. VentaRepository::createVenta ya lo hace solo;
    // esta función es por si hace falta marcarla a mano
    public function completarReserva(int $id): bool {
        $stmt = $this->db->prepare("UPDATE reserva SET estado = 'completada' WHERE id = ? AND estado = 'activa'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows === 1;
    }
}
?>
