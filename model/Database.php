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
 * Esta classe é responsável por manter e fornecer uma conexão com banco de dados
 *
 * @package MaiaFW\Model
 * @category Database
 * @version 1.0
 */
class Database  {
	/**
	 * Driver utilizado para conexão com o banco de dados.
	 *
	 * @var string
	 */
	private $_driver;

	/**
	 * String de conexão com o endereço do servidor e nome do banco de dados.
	 *
	 * @var string
	 */
	private $_dsn;

	/**
	 * Usuário para conexão com o banco de dados.
	 *
	 * @var string
	 */
	private $_user;

	/**
	 * Senha de conexão com o banco de dados.
	 *
	 * @var string
	 */
	private $_password;

	/**
	 * Indica se a conexão é permanente ou não.
	 *
	 * @var boolean
	 */
	private $_driverOptions;

	/**
	 * Prefixo utilizado nas tabelas da aplicação.
	 *
	 * @var string
	 */
	private $_prefix;

	/**
	 * Codificação padrão para a conexão.
	 *
	 * @var string
	 */
	private $_encoding;

	/**
	 * Aramazena a conexão ativa para utilização posterior.
	 *
	 * @var PDO
	 */
	private $_connection = false;

	/**
	 * Inicia a conexão e todos os atibutos necesários.
	 *
	 * @param string $dsn String de conexão com o endereço do servidor e nome do
	 * banco de dados.
	 * @param string $user Usuário para conexão com o banco de dados.
	 * @param string $password Senha de conexão com o banco de dados.
	 * @param array $driver_options Indica se a conexão é permanente ou não.
	 * @return void
	 */
	public function __construct( $driver, $dsn, $user, $password, $prefix = false, $driver_options = array(), $encoding = 'utf8' ) {
		// Inicia atributos para conexão.
		$this->_driver			= $driver;
		$this->_dsn				= $dsn;
		$this->_user			= $user;
		$this->_password		= $password;
		$this->_prefix			= $prefix;
		$this->_driverOptions	= $driver_options;
		$this->_encoding		= $encoding;

		// Chama método de conexão com o banco de dados.
		$this->connect();
	}

	/**
	 * Cuida da finalização do objeto limpando toda a memória utilizada e terminando
	 * as conexões ativas.
	 *
	 * @return void
	 */
	public function __destruct() {
		$this->disconnect();
	}

	/**
	 * Inicia uma conexão com o banco de dados com as informações passadas durante a
	 * instanciação de um objeto desta classe.
	 *
	 * @return void
	 */
	private function connect() {
		try {
			// Estabelece conexão com o banco de dados utilizando a abstracão de funções
			// de conexão com banco de dados do PHP chamada PDO.
			if(is_array($this->_driverOptions)) {
				if( isset( $this->_driverOptions[PDO::MYSQL_ATTR_INIT_COMMAND] ) ) {
					$this->_driverOptions[PDO::MYSQL_ATTR_INIT_COMMAND] .= ";SET NAMES " . $this->_encoding;
				} else {
					$this->_driverOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . $this->_encoding;
				}
			} else {
				$this->_driverOptions = array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->_encoding );
			}

			$this->_connection = new PDO(	$this->_dsn,
											$this->_user,
											$this->_password,
											$this->_driverOptions );
		} catch ( FwException $e ) {

		}
	}

	/**
	 * Finaliza a conexão ativa.
	 *
	 * @return void
	 */
	private function disconnect() {
		// Finaliza a conexão ativa com o banco de dados.
		$this->_connection = false;
	}

	/**
	 * Obtem o prefixo utilizado nas tabelas da aplicação.
	 *
	 * @return string
	 */
	public function getPrefix() {
		return $this->_prefix;
	}

	/**
	 * Obtém a conexão ativa.
	 *
	 * @param string $name Nome da conexão desejada
	 * @return PDO
	 */
	public function getConnection( $name = false ) {
		// Verifica se existe uma conexão ativa com banco de dados, caso exista, a
		// retorna, caso contrário dispara uma exceção.
		if ( $this->_connection !== false )
			return $this->_connection;
		throw new FwException( 'Nenhuma conexão com banco de dados foi iniciada.' );
	}
}