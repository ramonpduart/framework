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
 * Define as configurações básicas para executar uma aplicação.
 *
 * @package MaiaFW\Config
 * @category Configurations
 * @version 1.0
 */
class ConfigCore extends Config {
	public static function getInstance() {
		return parent::getInstance(__CLASS__);
	}

	public function setDebug( $value ) {
		$this->debug = $value;
	}
	public function getDebug() {
		return $this->debug;
	}

	public function setEnvironment( $value ) {
		$this->environment = $value;
	}
	public function getEnvironment() {
		return $this->environment;
	}

	public function setAppName( $value ) {
		$this->appName = $value;
	}
	public function getAppName() {
		return $this->appName;
	}

	public function setAppVersion( $value ) {
		$this->appVersion = $value;
	}
	public function getAppVersion() {
		return $this->appVersion;
	}

	public function setAppLanguage( $value ) {
		$this->appLanguage = $value;
	}
	public function getAppLanguage() {
		return $this->appLanguage;
	}

	public function setAppCharset( $value ) {
		$this->appCharset = $value;
	}
	public function getAppCharset() {
		return $this->appCharset;
	}

	public function setAppBaseUrl( $value ) {
		if( $value instanceof Uri ) {
			$this->baseUrl = $value;
		} else {
			$this->baseUrl = new Uri( $value );
		}
	}
	public function getAppBaseUrl() {
		return $this->baseUrl;
	}

	public function setAppIndex( $value ) {
		$this->appIndex = $value;
	}
	public function getAppIndex() {
		return $this->appIndex;
	}

	public function setAppAdmin( $value ) {
		$this->appAdmin = $value;
	}
	public function getAppAdmin() {
		return $this->appAdmin;
	}

	public function setSecuritySalt( $value ) {
		$this->securitySalt = $value;
	}
	public function getSecuritySalt() {
		return $this->securitySalt;
	}

	public function setUseTranslations( $value ) {
		$this->translations = (bool) $value;
	}
	public function getUseTranslations() {
		return $this->translations;
	}

	public function setReport( $value ) {
		$this->report = $value;
	}
	public function getReport() {
		return $this->report;
	}

	public function setPathLogs( $value ) {
		$this->pathLogs = $value;
	}
	public function getPathLogs() {
		return $this->pathLogs;
	}

	public function setReportEmailSubject( $value ) {
		$this->emailSubject = $value;
	}
	public function getReportEmailSubject() {
		return $this->emailSubject;
	}

	public function addReportEmail( $value ) {
		FwException::addEmail( $value );
	}
	public function getReportEmails() {
		return FwException::addEmail();
	}
}