<?php

namespace Database;

use PDO;
use PDOException;
use Error\APIException;

// há várias estratégias para implementar a conexão com o banco de dados
// para diferenciar das outras estratégias já utilizadas na disciplina, 
// aqui vamos adotar o padrão Singleton

class Database
{
	// Configurações para acesso ao banco MySQL
	private static string $host = 'localhost';
	private static string $dbname = 'ctg'; // Mude para o nome do seu banco de dados
	private static string $user = 'ctg_user'; // Mude para o seu usuário do banco
	private static string $password = '1234'; // Mude para a sua senha do banco
	private static string $port = '3306'; // Porta padrão do MySQL
	
	// Instância única da conexão (Singleton)
	private static ?PDO $connection = null;

	// poderíamos usar um construtor privado para impedir
	// private function __construct(): void { }

	// e evitar a clonagem da instância
	// private function __clone(): void { }


	// Método estático para obter a conexão
	public static function getConnection(): PDO
	{
		// se ainda não existe uma conexão, cria uma
		if (self::$connection === null) {
			try {
				// Cria a conexão uma única vez
				$dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbname . ";charset=utf8mb4";
				self::$connection = new PDO($dsn, self::$user, self::$password);

				// Configurações da conexão para gerar exceções e retornar arrays associativos
				self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			} catch (PDOException $e) {
				throw new APIException("Erro ao conectar ao banco de dados: " . $e->getMessage(), 500);	
			}
		}

		// Retorna sempre a mesma instância
		return self::$connection;
	}
}
