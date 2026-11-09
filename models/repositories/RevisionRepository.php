<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../Revision.php';

class RevisionRepository {
    private $db;

    public function __construct() {
        $this->db = (new db())->connect();
    }

    public function mapRow(array $row): Revision {
        return new Revision(
            (int)$row['id'],
            (int)$row['id_coche'],
            $row['id_reserva'] !== null ? (int)$row['id_reserva'] : null,
            (int)$row['id_usuario'],
            $row['fecha'],
            $row['resultado']
        );
    }

    public function getRevisionById(int $id): ?Revision {
        $stmt = $this->db->prepare("SELECT * FROM revision WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    // Historial de revisiones de un coche
    public function getRevisionesByCoche(int $id_coche): array {
        $stmt = $this->db->prepare("SELECT * FROM revision WHERE id_coche = ? ORDER BY fecha DESC");
        $stmt->bind_param("i", $id_coche);
        $stmt->execute();
        $result = $stmt->get_result();
        $revisiones = [];
        while ($row = $result->fetch_assoc()) {
            $revisiones[] = $this->mapRow($row);
        }
        return $revisiones;
    }

    public function createRevision(Revision $revision): bool {
        $stmt = $this->db->prepare("INSERT INTO revision (id_coche, id_reserva, id_usuario, resultado) VALUES (?, ?, ?, ?)");
        $id_coche = $revision->getIdCoche();
        $id_reserva = $revision->getIdReserva();
        $id_usuario = $revision->getIdUsuario();
        $resultado = $revision->getResultado();
        $stmt->bind_param("iiis", $id_coche, $id_reserva, $id_usuario, $resultado);
        return $stmt->execute();
    }

    // Cuando se termina la revisión: 'apto' o 'no_apto'
    public function setResultado(int $id, string $resultado): bool {
        if (!in_array($resultado, ['pendiente', 'apto', 'no_apto'])) {
            throw new InvalidArgumentException("Resultado inválido: debe ser 'pendiente', 'apto' o 'no_apto'.");
        }
        $stmt = $this->db->prepare("UPDATE revision SET resultado = ? WHERE id = ?");
        $stmt->bind_param("si", $resultado, $id);
        return $stmt->execute();
    }
}
?>
