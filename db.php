<?php
class db {
    public function connect() {
        if (!getenv('DB_HOST') || !getenv('DB_USER') || !getenv('DB_PASS') || !getenv('DB_NAME')) {
            if (!file_exists(__DIR__.'/.env')) {
                die('Falta el archivo .env (copia .env.example y rellénalo)');
            }
            $env = parse_ini_file(__DIR__.'/.env');
            foreach ($env as $key => $value) {
                putenv("$key=$value");
            }
        }
        $conn = new mysqli(
            getenv('DB_HOST'), 
            getenv('DB_USER'), 
            getenv('DB_PASS'), 
            getenv('DB_NAME'));
        // Para que tildes y ñ se guarden y se lean bien
        $conn->set_charset('utf8mb4');
        return $conn;
    }
}