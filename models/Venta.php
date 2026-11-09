<?php
class Venta {
    private $id;
    private $id_coche;
    private $id_cliente;
    private $id_usuario;
    private $id_reserva;
    private $precio;
    private $metodo_pago;
    private $fecha_venta;

    public function __construct(
        int $id,
        int $id_coche,
        int $id_cliente,
        int $id_usuario,
        ?int $id_reserva,
        float $precio,
        string $metodo_pago,
        string $fecha_venta
    ) {
        $this->id = $id;
        $this->id_coche = $id_coche;
        $this->id_cliente = $id_cliente;
        $this->id_usuario = $id_usuario;
        $this->id_reserva = $id_reserva;
        $this->precio = $precio;
        $this->metodo_pago = $metodo_pago;
        $this->fecha_venta = $fecha_venta;
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
    public function getIdReserva(): ?int {
        return $this->id_reserva;
    }
    public function setIdReserva(?int $id_reserva): void {
        $this->id_reserva = $id_reserva;
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

    public function getFechaVenta(): string {
        return $this->fecha_venta;
    }

    public function setFechaVenta(string $fecha_venta): void {
        $this->fecha_venta = $fecha_venta;
    }
}
?>