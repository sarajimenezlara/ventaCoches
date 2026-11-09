<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../User.php';

class UserRepository {
    private $db;

    public function __construct() {
        $this->db = (new db())->connect();
    }

    public function mapRow(array $row): User {
        return new User(
            (int)$row['id'],
            $row['username'],
            $row['nombre'],
            $row['apellidos'],
            $row['email'],
            $row['password'],
            $row['rol'],
            $row['fecha_alta']
        );
    }

    public function getUserById(int $id): ?User {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    public function getUserByEmail(string $email): ?User {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    public function getUserByUsername(string $username): ?User {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $this->mapRow($row);
        }
        return null;
    }

    // Devuelve false si el username o el email ya existen
    public function createUser(User $user): bool {
        $stmt = $this->db->prepare("INSERT INTO usuario (username, nombre, apellidos, email, password, rol) VALUES (?, ?, ?, ?, ?, ?)");
        $username = $user->getUsername();
        $nombre = $user->getNombre();
        $apellidos = $user->getApellidos();
        $email = $user->getEmail();
        $hash = password_hash($user->getPassword(), PASSWORD_DEFAULT);
        $rol = $user->getRol();
        $stmt->bind_param("ssssss", $username, $nombre, $apellidos, $email, $hash, $rol);
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
}
?>
