<?php
class Coche {
    private $id;
    private $matricula;
    private $marca;
    private $modelo;
    private $anio;
    private $kilometros;
    private $color;
    private $combustible;
    private $situacion;
    private $precio_venta;
    private $descripcion;
    private $imagen;
    private $fecha_alta;

    public function __construct(
        int $id, 
        string $matricula, 
        string $marca, 
        string $modelo, 
        int $anio, 
        int $kilometros,
        ?string $color,
        string $combustible,
        string $situacion,
        float $precio_venta,
        ?string $descripcion,
        ?string $imagen,
        ?string $fecha_alta = null
        ){
        $this->id = $id;
        $this->matricula = $matricula;
        $this->marca = $marca;
        $this->modelo = $modelo;
        $this->anio = $anio;
        $this->kilometros = $kilometros;
        $this->color = $color;
        $this->setCombustible($combustible);
        $this->setSituacion($situacion);
        $this->precio_venta = $precio_venta;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;
        $this->fecha_alta = $fecha_alta;
    }
    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;    
    }
    public function getMatricula(){
        return $this->matricula;
    }
    public function setMatricula($matricula){
        $this->matricula = $matricula;
    }
    public function getMarca(){
        return $this->marca;
    }
    public function setMarca($marca){
        $this->marca = $marca;
    }
    public function getModelo(){
        return $this->modelo;
    }
    public function setModelo($modelo){
        $this->modelo = $modelo;
    }
    public function getAnio(){
        return $this->anio;
    }
    public function setAnio($anio){
        $this->anio = $anio;
    }
    public function getKilometros(){
        return $this->kilometros;
    }
    public function setKilometros($kilometros){
        $this->kilometros = $kilometros;
    }
    public function getColor(){
        return $this->color;
    }
    public function setColor($color){
        $this->color = $color;
    }
    public function getCombustible(){
        return $this->combustible;
    }
    public function setCombustible($combustible){
        if (!in_array($combustible, ['gasolina', 'diesel', 'hibrido', 'electrico'])) {
            throw new InvalidArgumentException("Combustible inválido: debe ser 'gasolina', 'diesel', 'hibrido' o 'electrico'.");
        }
        $this->combustible = $combustible;
    }
    public function getSituacion(){
        return $this->situacion;
    }
    public function setSituacion($situacion){
        if (!in_array($situacion, ['disponible', 'reservado', 'vendido'])) {
            throw new InvalidArgumentException("Situación inválida: debe ser 'disponible', 'reservado' o 'vendido'.");
        }
        $this->situacion = $situacion;
    }
    public function getPrecioVenta(){
        return $this->precio_venta;
    }
    public function setPrecioVenta($precio_venta){
        $this->precio_venta = $precio_venta;
    }
    public function getDescripcion(){
        return $this->descripcion;
    }
    public function setDescripcion($descripcion){
        $this->descripcion = $descripcion;
    }
    public function getImagen(){
        return $this->imagen;
    }
    public function setImagen($imagen){
        $this->imagen = $imagen;
    }
    public function getFechaAlta(){
        return $this->fecha_alta;
    }
    public function setFechaAlta($fecha_alta){
        $this->fecha_alta = $fecha_alta;
    }
    
}
?>