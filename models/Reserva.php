<?php
class Reserva {
    private $id;
    private $id_coche;
    private $id_cliente;
    private $id_usuario;
    private $fecha_reserva;
    private $estado;
    private $fecha_cancelacion;

    public function __construct(
        int $id,
        int $id_coche,
        int $id_cliente,
        ?int $id_usuario = null,
        ?string $fecha_reserva = null,
        string $estado = 'activa',
        ?string $fecha_cancelacion = null
    ){
        $this->id = $id;
        $this->id_coche = $id_coche;
        $this->id_cliente = $id_cliente;
        $this->id_usuario = $id_usuario;
        $this->fecha_reserva = $fecha_reserva;
        $this->setEstado($estado);
        $this->fecha_cancelacion = $fecha_cancelacion;
    }

    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;
    }
    public function getIdCoche(){
        return $this->id_coche;
    }
    public function setIdCoche($id_coche){
        $this->id_coche = $id_coche;
    }
    public function getIdCliente(){
        return $this->id_cliente;
    }
    public function setIdCliente($id_cliente){
        $this->id_cliente = $id_cliente;
    }
    public function getIdUsuario(){
        return $this->id_usuario;
    }
    public function setIdUsuario($id_usuario){
        $this->id_usuario = $id_usuario;
    }
    public function getFechaReserva(){
        return $this->fecha_reserva;
    }
    public function setFechaReserva($fecha_reserva){
        $this->fecha_reserva = $fecha_reserva;
    }
    public function getEstado(){
        return $this->estado;
    }
    public function setEstado($estado){
        if (!in_array($estado, ['activa', 'cancelada', 'completada'])) {
            throw new InvalidArgumentException("Estado inválido: debe ser 'activa', 'cancelada' o 'completada'.");
        }
        $this->estado = $estado;
    }
    public function getFechaCancelacion(){
        return $this->fecha_cancelacion;
    }
    public function setFechaCancelacion($fecha_cancelacion){
        $this->fecha_cancelacion = $fecha_cancelacion;
    }
}
?>
