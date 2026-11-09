<?php
class User {
    private int $id;
    private string $username;
    private string $nombre;
    private string $apellidos;
    private string $email;
    private string $password;
    private string $rol;
    private ?string $fecha_alta;

    public function __construct(
        int $id,
        string $username,
        string $nombre,
        string $apellidos,
        string $email,
        string $password,
        string $rol = 'trabajador',
        ?string $fecha_alta = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->email = $email;
        $this->password = $password;
        $this->setRol($rol);
        $this->fecha_alta = $fecha_alta;
    }

    public function getId(): int {
        return $this->id;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }
    public function getUsername(): string {
        return $this->username;
    }
    public function setUsername(string $username): void {
        $this->username = $username;
    }
    public function getNombre(): string {
        return $this->nombre;
    }
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }
    public function getApellidos(): string {
        return $this->apellidos;
    }
    public function setApellidos(string $apellidos): void {
        $this->apellidos = $apellidos;
    }
    public function getEmail(): string {
        return $this->email;
    }
    public function setEmail(string $email): void {
        $this->email = $email;
    }
    public function getPassword(): string {
        return $this->password;
    }
    public function setPassword(string $password): void {
        $this->password = $password;
    }
    public function getRol(): string {
        return $this->rol;
    }
    public function setRol(string $rol): void {
        if (!in_array($rol, ['admin', 'trabajador'])) {
            throw new InvalidArgumentException("Rol inválido: debe ser 'admin' o 'trabajador'.");
        }
        $this->rol = $rol;
    }
    public function getFechaAlta(): ?string {
        return $this->fecha_alta;
    }
    public function setFechaAlta(?string $fecha_alta): void {
        $this->fecha_alta = $fecha_alta;
    }
}
?>
