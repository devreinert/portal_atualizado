<?php
class Database {
    //  Configurações do banco de dados
    private static $host = "localhost";
    private static $dbName = "portal_compras";
    private static $username = "root";
    private static $password = "root";
    private static $port = "3307";

    public static function connect() {
        try {
            //  DSN = Data Source Name (define driver, host, banco e charset)
            $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbName . ";charset=utf8mb4";


            //  Opções do PDO (melhores práticas)
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // lança exceções em erros
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // resultados como array associativo
                PDO::ATTR_EMULATE_PREPARES => false, // evita emulação de prepared statements
            ];

            //  Cria a conexão PDO
            $pdo = new PDO($dsn, self::$username, self::$password, $options);

            return $pdo;
        } catch (PDOException $e) {
            //  Caso haja erro na conexão
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
}
?>
