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
class Cookie {

	private function __construct() {
	}

	private function __clone() {
	}

	public static function setVar( $name, $value, $hours ) {
		setcookie($name, $value, $hours * 3600 );
	}

	public static function getVar( $name ) {
		if( isset( $_COOKIE[$name] ) ) {
			return $_COOKIE[$name];
		}

		return false;
	}

	public static function deleteVar( $name ){
		setcookie($name, "", time() - 3600);
	}
}