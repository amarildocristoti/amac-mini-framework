<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

// Fornece uma conexão PDO única por requisição HTTP.
final class Database
{
    // Guarda a conexão já criada para não abrir várias conexões desnecessárias.
    private static ?PDO $instance = null;

    // Impede a instanciação direta da classe.
    private function __construct()
    {
    }

    // Impede clonagem para preservar uma única conexão.
    private function __clone()
    {
    }

    // Retorna a conexão existente ou cria uma nova na primeira chamada.
    public static function connection(): PDO
    {
        // Reaproveita a conexão quando ela já foi criada.
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        // Monta o DSN com charset explícito para evitar problemas de encoding.
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        // Configura o PDO para falhar com exceção e retornar arrays associativos.
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            // Abre a conexão somente dentro do bloco protegido.
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $exception) {
            // Registra o erro técnico no log interno.
            error_log('Falha ao conectar ao banco: ' . $exception->getMessage());
            // Lança uma mensagem neutra para o handler global.
            throw new RuntimeException('Não foi possível conectar ao banco de dados.', 0, $exception);
        }

        // Devolve a instância pronta para os models.
        return self::$instance;
    }

    // Mantém compatibilidade com o nome usado pelo framework original.
    public static function getConnection(): PDO
    {
        // Encaminha para o método com nome mais semântico.
        return self::connection();
    }
}
