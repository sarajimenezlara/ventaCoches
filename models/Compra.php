<?php
class Compra {
    private int $id;
    private int $id_coche;
    private int $id_cliente;
    private int $id_usuario;
    private float $precio;
    private string $metodo_pago;
    private ?string $fecha;
    private bool $tenia_revision;

    public function __construct(
        int $id,
        int $id_coche,
        int $id_cliente,
        int $id_usuario,
        float $precio,
        string $metodo_pago,
        ?string $fecha,
        bool $tenia_revision
    ) {
        $this->id = $id;
        $this->id_coche = $id_coche;
        $this->id_cliente = $id_cliente;
        $this->id_usuario = $id_usuario;
        $this->precio = $precio;
        $this->setMetodoPago($metodo_pago);
        $this->fecha = $fecha;
        $this->tenia_revision = $tenia_revision;
    }

    public function getId(): int {
        return $this->id;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }
    public function getIdCoche(): int {
        return $this->id_coche;
    }
    public function setIdCoche(int $id_coche): void {
        $this->id_coche = $id_coche;
    }
    public function getIdCliente(): int {
        return $this->id_cliente;
    }
    public function setIdCliente(int $id_cliente): void {
        $this->id_cliente = $id_cliente;
    }
    public function getIdUsuario(): int {
        return $this->id_usuario;
    }
    public function setIdUsuario(int $id_usuario): void {
        $this->id_usuario = $id_usuario;
    }
    public function getPrecio(): float {
        return $this->precio;
    }
    public function setPrecio(float $precio): void {
        $this->precio = $precio;
    }
    public function getMetodoPago(): string {
        return $this->metodo_pago;
    }
    public function setMetodoPago(string $metodo_pago): void {
        if (!in_array($metodo_pago, ['efectivo', 'tarjeta', 'transferencia'])) {
            throw new InvalidArgumentException("Método de pago inválido: debe ser 'efectivo', 'tarjeta' o 'transferencia'.");
        }
        $this->metodo_pago = $metodo_pago;
    }
    public function getFecha(): ?string {
        return $this->fecha;
    }
    public function setFecha(?string $fecha): void {
        $this->fecha = $fecha;
    }
    public function getTeniaRevision(): bool {
        return $this->tenia_revision;
    }
    public function setTeniaRevision(bool $tenia_revision): void {
        $this->tenia_revision = $tenia_revision;
    }
}
?>
