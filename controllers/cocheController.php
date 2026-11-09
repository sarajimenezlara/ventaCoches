<?php
require_once __DIR__ . '/mainController.php';
require_once __DIR__ . '/../models/repositories/CocheRepository.php';

class CocheController extends MainController{
    private CocheRepository $cocheRepo;

    // aqui se guardan las imagenes que suban los usuarios
    private const CARPETA_IMAGENES = __DIR__ . '/../public/image/';

    public function __construct(){
        parent::__construct();
        $this->cocheRepo = new CocheRepository();
    }

    public function run(string $action){
        $this->onlyLogin();
        switch($action){
            case 'list':
                $this->list();
                break;
            case 'create':
                $this->create();
                break;
            case 'edit':
                $this->edit();
                break;
            default:
                $this->redirect('index.php?c=coche&action=list');
        }
    }

    private function list(){
        $coches = $this->cocheRepo->getAllCoches();
        require __DIR__ . '/../view/mainView.phtml';
    }

    private function create(){
        $error = null;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            [$datos, $error] = $this->validarFormulario();

            $imagen = null;
            if($error === null){
                [$imagen, $error] = $this->guardarImagen();
            }

            if($error === null){
                $coche = new Coche(
                    0,
                    $datos['matricula'],
                    $datos['marca'],
                    $datos['modelo'],
                    $datos['anio'],
                    $datos['kilometros'],
                    $datos['color'],
                    $datos['combustible'],
                    'disponible',          // un coche nuevo siempre empieza disponible
                    $datos['precio_venta'],
                    $datos['descripcion'],
                    $imagen
                );
                if($this->cocheRepo->createCoche($coche)){
                    $this->redirect('index.php?c=coche&action=list');
                }
                // No se ha guardado: se borra la foto que acabamos de subir
                $this->borrarImagen($imagen);
                $error = "Ya existe un coche con esa matrícula.";
            }
        }
        require __DIR__ . '/../view/newCocheView.phtml';
    }

    private function edit(){
        $error = null;
        $id = (int)($_GET['id'] ?? 0);
        $coche = $this->cocheRepo->getCocheById($id);
        if(!$coche){
            $this->redirect('index.php?c=coche&action=list');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            [$datos, $error] = $this->validarFormulario();

            $imagenNueva = null;
            if($error === null){
                [$imagenNueva, $error] = $this->guardarImagen();
            }

            if($error === null){
                $imagenAntigua = $coche->getImagen();

                $coche->setMatricula($datos['matricula']);
                $coche->setMarca($datos['marca']);
                $coche->setModelo($datos['modelo']);
                $coche->setAnio($datos['anio']);
                $coche->setKilometros($datos['kilometros']);
                $coche->setColor($datos['color']);
                $coche->setCombustible($datos['combustible']);
                $coche->setPrecioVenta($datos['precio_venta']);
                $coche->setDescripcion($datos['descripcion']);
                // Si no suben foto nueva, se queda la que tenía
                if($imagenNueva !== null){
                    $coche->setImagen($imagenNueva);
                }

                if($this->cocheRepo->updateCoche($coche)){
                    // Ya guardado: si se cambió la foto, se borra la vieja
                    if($imagenNueva !== null){
                        $this->borrarImagen($imagenAntigua);
                    }
                    $this->redirect('index.php?c=coche&action=list');
                }
                $this->borrarImagen($imagenNueva);
                $coche->setImagen($imagenAntigua);
                $error = "Ya existe otro coche con esa matrícula.";
            }
        }
        require __DIR__ . '/../view/editCocheView.phtml';
    }

    // Lee y comprueba el formulario del coche (lo usan create y edit).
    // Devuelve [datos, error]; si todo está bien, error es null.
    private function validarFormulario(): array {
        $datos = [
            // Sin espacios ni guiones y en mayúsculas: "1234-abc" -> "1234ABC"
            'matricula'    => strtoupper(str_replace([' ', '-'], '', $_POST['matricula'] ?? '')),
            'marca'        => trim($_POST['marca'] ?? ''),
            'modelo'       => trim($_POST['modelo'] ?? ''),
            'anio'         => (int)($_POST['anio'] ?? 0),
            'kilometros'   => (int)($_POST['kilometros'] ?? -1),
            'combustible'  => $_POST['combustible'] ?? '',
            'precio_venta' => (float)($_POST['precio_venta'] ?? 0),
            // Opcionales: vacíos se guardan como NULL
            'color'        => trim($_POST['color'] ?? '') ?: null,
            'descripcion'  => trim($_POST['descripcion'] ?? '') ?: null,
        ];

        $error = null;
        if($datos['matricula'] === '' || $datos['marca'] === '' || $datos['modelo'] === ''){
            $error = "Rellena matrícula, marca y modelo.";
        } elseif($datos['anio'] < 1950 || $datos['anio'] > (int)date('Y')){
            $error = "El año no es válido.";
        } elseif($datos['kilometros'] < 0){
            $error = "Los kilómetros no son válidos.";
        } elseif(!in_array($datos['combustible'], ['gasolina', 'diesel', 'hibrido', 'electrico'])){
            $error = "El combustible no es válido.";
        } elseif($datos['precio_venta'] <= 0){
            $error = "El precio tiene que ser mayor que 0.";
        }
        return [$datos, $error];
    }

    // Guarda la foto subida en public/image/.
    // Devuelve [nombreDelArchivo, error]. Si no han subido foto: [null, null]
    private function guardarImagen(): array {
        $archivo = $_FILES['imagen'] ?? null;
        if(!$archivo || $archivo['error'] === UPLOAD_ERR_NO_FILE){
            return [null, null];
        }
        if($archivo['error'] !== UPLOAD_ERR_OK){
            return [null, "No se ha podido subir la foto."];
        }
        if($archivo['size'] > 5 * 1024 * 1024){
            return [null, "La foto no puede pesar más de 5 MB."];
        }

        // Se mira el contenido real del archivo, no la extensión (que se puede falsear)
        $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($archivo['tmp_name']);
        if(!isset($tipos[$mime])){
            return [null, "La foto tiene que ser JPG, PNG o WEBP."];
        }

        if(!is_dir(self::CARPETA_IMAGENES)){
            mkdir(self::CARPETA_IMAGENES, 0775, true);
        }
        // Nombre único para que dos fotos no se pisen
        $nombre = 'coche-' . uniqid() . '.' . $tipos[$mime];
        if(!move_uploaded_file($archivo['tmp_name'], self::CARPETA_IMAGENES . $nombre)){
            return [null, "No se ha podido guardar la foto."];
        }
        return [$nombre, null];
    }

    private function borrarImagen(?string $nombre){
        if($nombre !== null && is_file(self::CARPETA_IMAGENES . basename($nombre))){
            unlink(self::CARPETA_IMAGENES . basename($nombre));
        }
    }
}
