<?php
class Revision {
    private $id;
    private $id_coche;
    private $id_reserva;
    private $id_usuario;
    private $fecha;
    private $resultado;

    public function __construct(
        int $id,
        int $id_coche,
        ?int $id_reserva,
        int $id_usuario,
        string $fecha,
        string $resultado
    ) {
        $this->id = $id;
        $this->id_coche = $id_coche;
        $this->id_reserva = $id_reserva;
        $this->id_usuario = $id_usuario;
        $this->fecha = $fecha;
        $this->resultado = $resultado;
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

    public function getIdReserva(): ?int {
        return $this->id_reserva;
    }

    public function setIdReserva(?int $id_reserva): void {
        $this->id_reserva = $id_reserva;
    }

    public function getIdUsuario(): int {
        return $this->id_usuario;
    }

    public function setIdUsuario(int $id_usuario): void {
        $this->id_usuario = $id_usuario;
    }

    public function getFecha(): string {
        return $this->fecha;
    }

    public function setFecha(string $fecha): void {
        $this->fecha = $fecha;
    }

    public function getResultado(): string {
        return $this->resultado;
    }

    public function setResultado(string $resultado): void {
        if (!in_array($resultado, ['pendiente', 'apto', 'no_apto'])) {
            throw new InvalidArgumentException("Resultado inválido: debe ser 'pendiente', 'apto' o 'no_apto'.");
        }
        $this->resultado = $resultado;
    }
}
?>