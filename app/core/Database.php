<?php

namespace App\core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    // Construtor privado para evitar criação direta
    private function __construct()
    {  
        $port = $_ENV['DB_PORT'];
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_DATABASE'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
   


        try {
            // Criação da conexão
            $this->connection = new PDO(
                "pgsql:host=$host;port=$port;dbname=$dbname",
                $username,
                $password
            );
            // Configuração para exceções no PDO
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Definir o schema padrão
            $this->connection->exec('SET search_path TO sigmat, public');
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            $debug = isset($_ENV['APP_DEBUG']) && filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN);
            die($debug ? 'Erro de conexão: ' . $e->getMessage() : 'Erro de conexão com o banco de dados.' . $e->getMessage());
        }
    }

    // Método para obter a instância única da classe
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}