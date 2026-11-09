<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../Cliente.php';

class ClienteRepository {
    private $db;

    public function __construct() {
        $this->db = (new db())->connect();
    }

    public function mapRow(array $row): Cliente {
        return new Cliente(
            (int)$row['id'],
            $row['tipo'],
            $row['nombre'],
            $row['apellidos'],
            $row['dni'],
            $row['telefono'],
            $row['email'],
            $row['direccion'],
            $row['fecha_alta']
        );
    }

    public function getClienteById(int $id): ?Cliente {
        $stmt = $this->db->prepare("SELECT * FROM cliente WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    public function getClienteByEmail(string $email): ?Cliente {
        $stmt = $this->db->prepare("SELECT * FROM cliente WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    // Devuelve false si el DNI o el email ya existen
    public function createCliente(Cliente $cliente): bool {
        $stmt = $this->db->prepare("INSERT INTO cliente (tipo, nombre, apellidos, dni, telefono, email, direccion) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $tipo = $cliente->getTipo();
        $nombre = $cliente->getNombre();
        $apellidos = $cliente->getApellidos();
        $dni = $cliente->getDni();
        $telefono = $cliente->getTelefono();
        $email = $cliente->getEmail();
        $direccion = $cliente->getDireccion();
        $stmt->bind_param("sssssss",
            $tipo,
            $nombre,
            $apellidos,
            $dni,
            $telefono,
            $email,
            $direccion
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

    // Todos los clientes, ordenados por nombre (para el listado)
    public function getAllClientes(): array {
        $stmt = $this->db->prepare("SELECT * FROM cliente ORDER BY nombre, apellidos");
        $stmt->execute();
        $result = $stmt->get_result();
        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $this->mapRow($row);
        }
        return $clientes;
    }

    // Actualiza los datos del cliente.
    // Devuelve false si el nuevo DNI o email ya los tiene otro cliente
    public function updateCliente(Cliente $cliente): bool {
        $stmt = $this->db->prepare("UPDATE cliente SET tipo = ?, nombre = ?, apellidos = ?, dni = ?, telefono = ?, email = ?, direccion = ? WHERE id = ?");
        $tipo = $cliente->getTipo();
        $nombre = $cliente->getNombre();
        $apellidos = $cliente->getApellidos();
        $dni = $cliente->getDni();
        $telefono = $cliente->getTelefono();
        $email = $cliente->getEmail();
        $direccion = $cliente->getDireccion();
        $id = $cliente->getId();
        $stmt->bind_param("sssssssi",
            $tipo,
            $nombre,
            $apellidos,
            $dni,
            $telefono,
            $email,
            $direccion,
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
