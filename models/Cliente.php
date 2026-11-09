<?php
class Cliente {
    private $id;
    private $tipo;
    private $nombre;
    private $apellidos;
    private $dni;
    private $telefono;
    private $email;
    private $direccion;
    private $fecha_alta;

    public function __construct(
        int $id,
        string $tipo,
        string $nombre,
        ?string $apellidos,
        string $dni,
        string $telefono,
        ?string $email,
        ?string $direccion,
        ?string $fecha_alta = null
    ) {
        $this->id = $id;
        $this->setTipo($tipo);
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->dni = $dni;
        $this->telefono = $telefono;
        $this->email = $email;
        $this->direccion = $direccion;
        $this->fecha_alta = $fecha_alta;
    }

    public function getId(): int {
        return $this->id;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }
    public function getTipo(): string {
        return $this->tipo;
    }
    public function setTipo(string $tipo): void {
        if (!in_array($tipo, ['particular', 'empresa'])) {
            throw new InvalidArgumentException("Tipo de cliente inválido: debe ser 'particular' o 'empresa'.");
        }
        $this->tipo = $tipo;
    }
    public function getNombre(): string {
        return $this->nombre;
    }
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }
    public function getApellidos(): ?string {
        return $this->apellidos;
    }
    public function setApellidos(?string $apellidos): void {
        $this->apellidos = $apellidos;
    }
    public function getDni(): string {
        return $this->dni;
    }
    public function setDni(string $dni): void {
        $this->dni = $dni;
    }
    public function getTelefono(): string {
        return $this->telefono;
    }
    public function setTelefono(string $telefono): void {
        $this->telefono = $telefono;
    }
    public function getEmail(): ?string {
        return $this->email;
    }
    public function setEmail(?string $email): void {
        $this->email = $email;
    }
    public function getDireccion(): ?string {
        return $this->direccion;
    }
    public function setDireccion(?string $direccion): void {
        $this->direccion = $direccion;
    }
    public function getFechaAlta(): ?string {
        return $this->fecha_alta;
    }
    public function setFechaAlta(?string $fecha_alta): void {
        $this->fecha_alta = $fecha_alta;
    }
}
?>
