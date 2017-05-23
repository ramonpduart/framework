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
 *
 * @package MaiaFW\Lib\Type
 * @category Types
 * @version 1.0
 */
class File {
	private $_path;

	public function __construct ( $path ) {
		self::isValid( $path );

		if ( is_file($path) )
			$this->_path = $path;
		else
			throw 'O arquivo informado não é válido ou não existe.';
	}
	public function __destruct () {}

	/**
	 * Método mágico para retornar e utilizar o caminho do arquivos como string.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->_path;
	}

	public function open () {}
	public function close () {}
	public function delete() {}
	public function rename( $name ) {
		rename( $this->_path, $name );
	}

	public function getType() {}
	public function getMimeType() {}

	public function getContents() {}
	public function setContents() {}
	public function addContents() {}

	public static function isValid( $path ) {
		self::_checkSecurity( $path );
		if( file_exists( $path ) && is_file($path) )
			return true;
		return false;
	}

	private static function _checkSecurity( $path ) {
		if( is_string($path) && preg_match('/[^a-z0-9\\/\\\\_.-]/i', $path ) ) {
			throw new FwException( 'O caminho "'. $path . '" contém caracteres inválidos.' );
		}
		return true;
	}
}