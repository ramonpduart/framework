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
class Session {

	private function __construct() {
	}

	private function __clone() {
	}

	public static function setVar( $name, $value ) {
		self::init();
		$_SESSION[$name] = $value;
		self::close();
	}

	public static function deleteVar( $name ) {
		self::init();
		unset( $_SESSION[$name] );
		self::close();
	}

	public static function getVar( $name ) {
		if( isset( $_SESSION[$name] ) ) {
			return $_SESSION[$name];
		}

		return false;
//		throw new FwException( 'A variável de sessão "' . $name .
//								'" não existe.' );
	}

	public static function init() {
		session_start();
		//session_regenerate_id();
	}

	public static function destroy() {
		session_unset();
		session_destroy();
	}

	public static function close() {
		session_write_close();
	}

	public static function started() {
		return isset($_SESSION) && session_id();
	}
}