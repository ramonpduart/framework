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
 * Armazena as configurações de acesso a banco de dados todos os ambientes
 * necessários.
 *
 * @package MaiaFW\Config
 * @category Configurations
 * @version 1.0
 */
class ConfigDatabase extends Config {
	/**
	 * Obtém a instância ativa do objeto. Caso não exista nenhum objeto criado ainda,
	 * cria e armazena no respectivo atributo. Este método é uma parte da
	 * implementação do padrão de projeto Singleton.
	 *
	 * @return object
	 */
	public static function getInstance() {
		return parent::getInstance(__CLASS__);
	}

	/**
	 * Adiciona uma nova configuração para conexão com o banco de dados.
	 *
	 * @param string $environment Ambiente de trabalho.
	 * @param string $driver Driver de conexão.
	 * @param string $dsn Caminho e configuração para conexão com o servidor de banco de dados.
	 * @param string $user Usuário de conexão com o banco de dados.
	 * @param string $password Senha para conexão com o banco de dados.
	 * @param string $prefix Prefixo das tabelas utilizadas nas apilicações.
	 * @param array $driver_options Opções para estrabelecer conexão com o banco de dados.
	 * @param string $encoding Codificação dos caracteres.
	 */
	public function addDatabase( $environment, $driver, $dsn, $user,
								 $password, $prefix = false, $driver_options = array(),
								 $encoding = 'utf8' ) {
		$this->$environment = array(
			'driver'		=> $driver,
			'dsn'			=> $dsn,
			'user'			=> $user,
			'password'		=> $password,
			'prefix'		=> $prefix,
			'driver_options'=> $driver_options,
			'encoding'		=> $encoding
		);
	}

	/**
	 * Obtem a configuração do ambiente desejado.
	 *
	 * @param string $environment Ambiente de trabalho desejado.
	 */
	public function getDatabase( $environment ) {
		return $this->$environment;
	}
}