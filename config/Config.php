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
 * Esta classe é utilizada para armazenar configurações do sistema.
 * Caso haja a necessidade de criar variáveis de configuração de um algum modulo do
 * sistema, extenda esta classe e utilize métodos fixos, isto é, métodos não mágicos
 * para cada get e set desejados.
 *
 * @package MaiaFW\Config
 * @category Configurations
 * @version 1.0
 */
class Config {
	/**
	 * Armazena a instância de um objeto único desta classe.
	 *
	 * @var object
	 */
	private static $instances = array();

	/**
	 * Aramazena as variáveis de configuração.
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Este objeto não pode ser instânciado, por fazer uso do padrão Singleton.
	 * Sendo assim o construtor está setado como privado, para evitar que seja
	 * instânciado fora desta ou de classes filhas.
	 *
	 * @return void
	 */
	final private function __construct() {
	}

	/**
	 * Esta classe não pode ser instânciada externamente, por fazer uso do padrão
	 * Singleton. Sendo assim, a instância existente não pode ser clonada.
	 *
	 * @return void
	 */
	final private function __clone() {
	}

	/**
	 * Obtém a instância ativa do objeto. Caso não exista nenhum objeto criado ainda,
	 * cria e armazena no respectivo atributo. Este método é uma parte da
	 * implementação do padrão de projeto Singleton.
	 *
	 * @return object
	 */
	protected static function getInstance( $className ) {
		if(!isset( self::$instances[$className] ) ) {
			self::$instances[$className] = new $className();
		}

		return self::$instances[$className];
	}

	/**
	 * Atribui um valor a uma variável.
	 *
	 * @param string $attribute Nome do atributo.
	 * @param mixed $value Novo valor para o atributo.
	 * @return void
	 */
	final public function __set($attribute, $value) {
		$this->params[$attribute] = $value;
	}

	/**
	 * Obtem o valor de uma variável.
	 *
	 * @param string $attribute Nome do atributo.
	 * @return mixed Valor do atributo
	 */
	final public function __get($attribute) {
		if ( array_key_exists( $attribute, $this->params ) ) {
			return $this->params[$attribute];
		}
		return false;
	}
}