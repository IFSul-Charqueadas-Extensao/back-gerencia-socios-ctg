<?php

namespace Database;

use PDO;
use PDOException;
use Error\APIException;

class Database
{
	// Lê as configurações do .env na raiz do projeto
	private static function config(): array
	{
		$env = parse_ini_file(__DIR__ . '/../../.env');
		return [
			'host'     => $env['DB_HOST']     ?? 'localhost',
			'port'     => $env['DB_PORT']     ?? '3306',
			'dbname'   => $env['DB_NAME']     ?? 'ctg',
			'user'     => $env['DB_USER']     ?? 'ctg_user',
			'password' => $env['DB_PASSWORD'] ?? '1234',
		];
	}

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
				$cfg = self::config();
				// Cria a conexão uma única vez
				$dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4";
				self::$connection = new PDO($dsn, $cfg['user'], $cfg['password']);

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
