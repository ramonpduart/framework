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
class Mailler {
	/**
	 *
	 * @var type
	 */
	protected $_boundary;

	/**
	 *
	 * @var type
	 */
	protected $_charset = 'utf-8';

	/**
	 *
	 * @var type
	 */
	protected $_isHtml;

	/**
	 *
	 * @var type
	 */
	protected $_header;

	/**
	 *
	 * @var type
	 */
	protected $_body;

	/**
	 *
	 * @var type
	 */
	protected $_subject;

	/**
	 *
	 * @var type
	 */
	protected $_priority = 3;

	/**
	 *
	 * @var type
	 */
	protected $_files = array();

	protected $_fromMail;

	/**
	 *
	 * @var type
	 */
	protected $_fromName;

	/**
	 *
	 * @var type
	 */
	protected $_replyTo;

	/**
	 *
	 * @var type
	 */
	protected $_to = array();

	/**
	 *
	 * @var type
	 */
	protected $_cc = array();

	/**
	 *
	 * @var type
	 */
	protected $_bcc = array();

	/**
	 *
	 * @var type
	 */
	protected $_isSmtp;

	/**
	 *
	 * @var type
	 */
	protected $_host;

	/**
	 *
	 * @var type
	 */
	protected $_port;

	/**
	 *
	 * @var type
	 */
	protected $_username;

	/**
	 *
	 * @var type
	 */
	protected $_password;

	/**
	 *
	 */
	public function __construct( $subject, $message, $fromMail, $fromName, $isHtml = false ) {
		$this->_body		= $message;
		$this->_subject		= $subject;
		$this->_fromMail	= $fromMail;
		$this->_fromName	= $fromName;
		$this->_isHtml		= (bool) $isHtml;

		$this->_boundary	= 'XYZ-' . date("dmYis") . '-ZYX';
	}

	/**
	 *
	 * @param type $attribute
	 */
	public function __get( $attribute ) {
		switch ( $attribute ) {
			/*case '_to':
				$return = '';
				foreach ( $this->_to as $mail ) {
					$return .= $this->_to . ', ';
				}
				return $return;
				break;*/

			default:
				return $this->$attribute;
				break;
		}
	}

	/**
	 *
	 * @param type $attribute
	 * @param type $value
	 */
	public function __set( $attribute, $value ) {

	}

	/**
	 *
	 * @param type $file
	 */
	public function addAttachment( $fileName, $filePath ) {
		$this->_files[] = array(
			'name' => $fileName,
			'path' => $filePath
		);
	}

	public function setReplayTo( $mail ) {
		$mail = self::sanitizeMail ( $mail );
		if( self::validadeMail( $mail ) )
			$this->_replyTo = $mail;
		else
			throw new FwException( 'Error to trying add invalid mail at list of
									Address' );
	}

	/**
	 *
	 * @param type $mail
	 */
	public function addAddress ( $mail ) {
		$mail = self::sanitizeMail ( $mail );
		if( self::validadeMail( $mail ) )
			$this->_to[] = $mail;
		else
			throw new FwException( 'Error to trying add invalid mail at list of
									Address' );
	}

	/**
	 *
	 * @param type $mail
	 */
	public function removeAddress ( $mail ) {
		$index = array_search( $mail, $this->_to );
		if( $index !== false ) {
			unset( $this->_to[$index] );
			return true;
		}
		return false;
	}

	/**
	 *
	 */
	public function clearAddresses() {
		$this->_to = array();
	}

	/**
	 *
	 */
	public function getAddresses() {
		$return = '';
		$added = 0;
		$count = count( $this->_to );
		foreach ( $this->_to as $mail ) {
			$return .= $mail;
			$added++;
			if( $added < $count )
				$return.= ', ';
		}
		return $return;
	}

	/**
	 *
	 * @param type $mail
	 */
	public function addCC ( $mail ) {
		$mail = self::sanitizeMail ( $mail );
		if( self::validadeMail( $mail ) )
			$this->_cc[] = $mail;
		else
			throw new FwException( 'Error to trying add invalid mail at list of CC' );
	}

	/**
	 *
	 * @param type $mail
	 */
	public function removeCC ( $mail ) {
		$index = array_search( $mail, $this->_cc );
		if( $index !== false ) {
			unset( $this->_cc[$index] );
			return true;
		}
		return false;
	}

	/**
	 *
	 */
	public function clearCC(){
		$this->_cc = array();
	}

	/**
	 *
	 */
	public function getCC() {
		$return = '';
		$added = 0;
		$count = count( $this->_cc );
		foreach ( $this->_cc as $mail ) {
			$return .= $mail;
			$added++;
			if( $added < $count )
				$return.= ', ';
		}
		return $return;
	}

	/**
	 *
	 * @param type $mail
	 */
	public function addBCC ( $mail ) {
		$mail = self::sanitizeMail ( $mail );
		if( self::validadeMail( $mail ) )
			$this->_bcc[] = $mail;
		else
			throw new FwException( 'Error to trying add invalid mail at list of BCC' );
	}

	/**
	 *
	 * @param type $mail
	 */
	public function removeBCC ( $mail ) {
		$index = array_search( $mail, $this->_bcc );
		if( $index !== false ) {
			unset( $this->_bcc[$index] );
			return true;
		}
		return false;
	}

	/**
	 *
	 */
	public function clearBCC(){
		$this->_bcc = array();
	}

	/**
	 *
	 */
	public function getBCC() {
		$return = '';
		$added = 0;
		$count = count( $this->_bcc );
		foreach ( $this->_bcc as $mail ) {
			$return .= $mail;
			$added++;
			if( $added < $count )
				$return.= ', ';
		}
		return $return;
	}

	/**
	 *
	 */
	public function clearAllRecipients() {
		$this->clearAddresses();
		$this->claerCC();
		$this->clearBCC();
	}

	/**
	 *
	 * @param type $mail
	 * @return type
	 */
	public static function validadeMail( $mail ) {
		return filter_var( $mail , FILTER_VALIDATE_EMAIL ) != false;
	}

	/**
	 *
	 * @param type $mail
	 * @return type
	 */
	public static function sanitizeMail ( $mail ) {
		return filter_var( $mail , FILTER_SANITIZE_EMAIL );
	}

	/**
	 *
	 */
	private function _bodyDecorator() {
		$body  = '--' . $this->_boundary . "\n";
		$body .= "Content-Transfer-Encoding: 7bit;\n";
		$body .= 'Content-type: ' . $this->_getContentType() . "; ";
		$body .= 'charset=' . $this->_charset . ";\n\n";
		$body .= $this->_body;
		$body .= "\n\n--" . $this->_boundary . "\n";

		return $body;
	}

	private function _filesDecorator() {
		$filesContent = '';
		foreach( $this->_files as $file ) {
			$data = chunk_split( base64_encode( file_get_contents( $file['path'] ) ) );
			$filesContent .= 'Content-Type: "application/octet-stream";';
			$filesContent .= ' name="' . $file['name'] . '"' . "\n";
			$filesContent .= 'Content-Disposition: attachment;';
			$filesContent .= ' filename="' . $file['name'] . '"' . "\n";
			$filesContent .= 'Content-Transfer-Encoding: base64' . "\n\n" . $data . "\n\n";
			$filesContent .= "\n\n--" . $this->_boundary . "\n";
		}

		return $filesContent;
	}

	private function _getHeader() {
		$header	 = 'MIME-Version: 1.0' . PHP_EOL;
		$header	.= 'X-Mailer: PHP/' . phpversion() . PHP_EOL;
		$header	.= 'X-Priority: 3' . PHP_EOL;
		$header	.= 'Disposition-Notification-To:' . $this->_fromMail . PHP_EOL;
		$header	.= 'Return-Path:' . $this->_fromMail . PHP_EOL;
		$header	.= 'Message-ID: ' . md5( time() .  $_SERVER[ 'HTTP_HOST' ] . rand() ) . '_' . $_SERVER[ 'HTTP_HOST' ] . PHP_EOL;
		$header	.= "Content-type: multipart/mixed; ";
		$header	.= 'charset=' . $this->_charset . '; ';
		$header	.= 'boundary="' . $this->_boundary . "\"\n";
		$header	.= 'Date: ' . date("D, d M Y H:i:s O", time() - 3600) . "\n";


		$header	.= 'From: ' . $this->_fromName . '<' . $this->_fromMail . ">\n";

		if( $this->_replyTo )
			$header .= 'Reply-To: ' . $this->_replyTo . "\n";

		if( $this->_cc )
			$header .= 'CC: ' . $this->getCC() . "\n";

		if( $this->_bcc )
			$header .= 'BCC: ' . $this->getBCC() . "\n";

		return $header;
	}

	private function _getContentType() {
		if ( $this->_isHtml )
			return 'text/html';
		return 'text/plain';
	}

	/**
	 *
	 * @return type
	 */
	public function send() {
		if( $this->_isSmtp )
			return $this->_sendSMTP();
		return $this->_sendMail();
	}

	/**
	 *
	 */
	private function _sendMail() {
		$this->_header		= $this->_getHeader();
		$this->_body		= $this->_bodyDecorator();
		$this->_body		.= $this->_filesDecorator();

		if ( !mail( $this->getAddresses(), $this->_subject, $this->_body, $this->_header ) )
			throw new FwException( 'An error occurred when try to send mail.' );
		return true;
	}

	/**
	 *
	 */
	private function _sendSMTP() {

	}

}