<?php
/**
 * MaiaFW - Copyright (c) Marcus Maia (http://marcusmaia.com.br)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author     Marcus Maia (contato@marcusmaia.com.br)
 * @copyright  Copyright (c) Marcus Maia (http://marcusmaia.com.br)
 * @link       http://maiafw.marcusmaia.com.br MaiaFW
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Classe responsável por configurar e criar o ambiente para execução.
 *
 * Classe responsável por configurar e criar o ambiente necesário para a execução da
 * aplicação. Inicia URLs, configura as formas de log e debug, inicia a conexão com o
 * banco de dados, entre outros.
 *
 * @package MaiaFW\Environment
 * @category Core
 * @version 1.0
 */
class Environment {
	/**
	 * Nome do ambiente.
	 *
	 * @var string
	 */
	private static $environment;

	/**
	 * Instância do banco de dados que será utilizado no ambiente configurado.
	 *
	 * @var Database
	 */
	private static $database = null;

	/**
	 * Configura e inicia um ambiente específico.
	 *
	 * Configura o ambiente desejado e configura todos os parâmetros necessários.
	 *
	 * @param string $environment
	 * @return void
	 */
	public static function initialize( $environment ) {
		$ConfigDatabase		= ConfigDatabase::getInstance();
		$config				= $ConfigDatabase->getDatabase( $environment );
		if( $config !== false ) {
			self::$environment = $environment;
			self::$database = new Database(
				$config['driver'],
				$config['dsn'],
				$config['user'],
				$config['password'],
				$config['prefix'],
				$config['driver_options'],
				$config['encoding']
			);
		}
	}

	/**
	 * Obtem a instância da conexão com o banco de dados do ambiente atual.
	 *
	 * @return Database Instância da conexão com o banco de dados
	 */
	public static function getDatabase () {
		return self::$database;
	}
}