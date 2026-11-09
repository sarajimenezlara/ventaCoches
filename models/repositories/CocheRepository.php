<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../Coche.php';

class CocheRepository {
    private $db;

    public function __construct() {
        $this->db = (new db())->connect();
    }

    public function mapRow(array $row): Coche {
        return new Coche(
            (int)$row['id'],
            $row['matricula'],
            $row['marca'],
            $row['modelo'],
            (int)$row['anio'],
            (int)$row['kilometros'],
            $row['color'],
            $row['combustible'],
            $row['situacion'],
            (float)$row['precio_venta'],
            $row['descripcion'],
            $row['imagen'],
            $row['fecha_alta']
        );
    }

    public function getCocheById(int $id): ?Coche {
        $stmt = $this->db->prepare("SELECT * FROM coche WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    public function getAllCoches(): array {
        $stmt = $this->db->prepare("SELECT * FROM coche");
        $stmt->execute();
        $result = $stmt->get_result();
        $coches = [];
        while ($row = $result->fetch_assoc()) {
            $coches[] = $this->mapRow($row);
        }
        return $coches;
    }

    // Los coches que salen en la página principal
    public function getCochesDisponibles(): array {
        $stmt = $this->db->prepare("SELECT * FROM coche WHERE situacion = 'disponible' ORDER BY fecha_alta DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $coches = [];
        while ($row = $result->fetch_assoc()) {
            $coches[] = $this->mapRow($row);
        }
        return $coches;
    }

    // Devuelve false si la matrícula ya existe
    public function createCoche(Coche $coche): bool {
        $stmt = $this->db->prepare("INSERT INTO coche (matricula, marca, modelo, anio, kilometros, color, combustible, situacion, precio_venta, descripcion, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $matricula = $coche->getMatricula();
        $marca = $coche->getMarca();
        $modelo = $coche->getModelo();
        $anio = $coche->getAnio();
        $kilometros = $coche->getKilometros();
        $color = $coche->getColor();
        $combustible = $coche->getCombustible();
        $situacion = $coche->getSituacion();
        $precio = $coche->getPrecioVenta();
        $descripcion = $coche->getDescripcion();
        $imagen = $coche->getImagen();
        $stmt->bind_param("sssiisssdss",
            $matricula,
            $marca,
            $modelo,
            $anio,
            $kilometros,
            $color,
            $combustible,
            $situacion,
            $precio,
            $descripcion,
            $imagen
        );
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            // 1062 = valor repetido en un campo UNIQUE
            if ($e->getCode() === 1062) {
                return false;
            }
            throw $e;
        }
    }

    // Actualiza los datos del coche. La situación NO se toca aquí:
    // la cambian las reservas y las ventas.
    // Devuelve false si la nueva matrícula ya la tiene otro coche
    public function updateCoche(Coche $coche): bool {
        $stmt = $this->db->prepare("UPDATE coche SET matricula = ?, marca = ?, modelo = ?, anio = ?, kilometros = ?, color = ?, combustible = ?, precio_venta = ?, descripcion = ?, imagen = ? WHERE id = ?");
        $matricula = $coche->getMatricula();
        $marca = $coche->getMarca();
        $modelo = $coche->getModelo();
        $anio = $coche->getAnio();
        $kilometros = $coche->getKilometros();
        $color = $coche->getColor();
        $combustible = $coche->getCombustible();
        $precio = $coche->getPrecioVenta();
        $descripcion = $coche->getDescripcion();
        $imagen = $coche->getImagen();
        $id = $coche->getId();
        $stmt->bind_param("sssiissdssi",
            $matricula,
            $marca,
            $modelo,
            $anio,
            $kilometros,
            $color,
            $combustible,
            $precio,
            $descripcion,
            $imagen,
            $id
        );
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
