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
class Directory {
	private $_path;

	public function __construct ( $path ) {
		if ( is_dir($path) )
			$this->_path = $path;
		else
			throw 'O diretório informado não é válido ou não existe.';
	}
	public function __destruct () {}

	public function open () {}
	public function close () {}
	public function delete() {}
	public function rename( $name ) {
		rename( $this->_path, $name );
	}

	public function getPathUp () {}
	public function getListFiles( $sort = 'asc' ) {
		if ( $sort == 'asc' )
			return scandir( $this->_path );

		return scandir( $this->_path, 1 );
	}
	public function getNumFiles() {}

	public function setPermission ( $permission ) {
		$permission;
	}

	public function searchFile( $name ) {
		$name;
	}

	public function createFolder( $name ) {
		$name;
	}
	public function createFile( File $File ) {
		$File;
	}
}