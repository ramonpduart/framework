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
 * Esta classe é utilizada para verificações e testes de desempenho, contabilizando
 * o tempo que um trecho de algoritmo leva para ser executado.
 *
 * @package MaiaFW\Lib\Report
 * @category Reports
 * @version 1.0
 */
final class Benchmark  {
	/**
	 * Armazena todos os marcos de tempo que se deseja calcular.
	 */
	private $marks = array();

	/**
	 * Armazena a instância de um objeto único desta classe.
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Este objeto não pode ser instânciado, por fazer uso do padrão Singleton.
	 * Sendo assim o construtor está setado como privado, para evitar que seja
	 * instânciado fora desta classe.
	 *
	 * @return void
	 */
	private function __construct() {
	}

	/**
	 * Esta classe não pode ser instânciada externamente, por fazer uso do padrão
	 * Singleton. Sendo assim, a instância existente não pode ser clonada.
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * Obtém a instância ativa do objeto. Caso não exista nenhum objeto criado ainda,
	 * cria e armazena no respectivo atributo.
	 *
	 * @return object
	 */
	public static function getInstance() {
		if( self::$_instance === null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Registra um novo marco guardando a hora em microsegundos do momento em que o
	 * método foi chamado.
	 *
	 * @param $name Nome do marco.
	 * @return void
	 */
	public function start( $name ) {
		if( array_key_exists( $name, $this->marks ) && isset($this->marks[$name]['start']) ) {
			throw new FwException( 'O marco "' . $name . '" já foi registrado.' );
		}

		$this->marks[$name]['start'] = microtime(true);
	}

	/**
	 * Registra o tempo de finalização de um marco guardando a hora em microsegundos
	 * do momento em que o método foi chamado.
	 *
	 * @param $name Nome do marco.
	 * @return void
	 */
	public function stop( $name ) {
		if( array_key_exists( $name, $this->marks ) ) {
			$this->marks[$name]['stop']		= microtime(true);
			$valor_start					= round( $this->marks[$name]['start'], 4);
			$valor_stop						= round( $this->marks[$name]['stop'], 4);
			$this->marks[$name]['total']	= $valor_stop - $valor_start;
		} else {
			throw new FwException( 'O marco "' . $name . '" não existe.' );
		}
	}

	/**
	 * Obtem os valores de um determinado marco.
	 *
	 * @param $name Nome do marco.
	 * @return array
	 */
	public function getMark( $name ) {
		if( array_key_exists( $name, $this->marks ) ) {
			return $this->marks[$name];
		} else {
			throw new FwException( 'O marco "' . $name . '" não existe.' );
		}
	}

	/**
	 * Retorna todos os marcos de tempo registrados.
	 *
	 * @return array
	 */
	public function getAllMarks() {
		return $this->marks;
	}
}