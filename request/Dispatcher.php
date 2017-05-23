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
 * Classe responsável pela e identificação dos parâmetros da requisição e informar ao
 * sistema do framework como agir.
 *
 * Classe responsável por analisar a requisição, solicitar o carregamento do
 * controller necessário e executar a Action solicitada passando os parâmetros
 * recebidos.
 *
 * @package MaiaFW\Request
 * @category Core
 * @version 1.0
 */
class Dispatcher {
	/**
	 * Armazena a instância de um objeto único desta classe.
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Objeto Uri da requisição atual.
	 *
	 * @var Uri
	 */
	private $_requestUri;

	/**
	 * Nome do esquema utilizado para requisições HTTP
	 */
	const SCHEME_HTTP  = 'http';

	/**
	 * Nome do esquema utilizado para requisições HTTP utilizando conexão segura.
	 */
	const SCHEME_HTTPS = 'https';

	/**
	 * Este objeto não pode ser instânciado, por fazer uso do padrão Singleton.
	 * Sendo assim o construtor está setado como privado, para evitar que seja
	 * instânciado fora desta classe.
	 *
	 * @return void
	 */
	private function __construct() {
		$this->_requestUri = $this->getRequestUri();
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
	 * Realiza a interpretação da requisição e inicia a chamada para a ação desejada.
	 *
	 * @return void
	 */
	public function dispatch() {
		$ConfigCore		= ConfigCore::getInstance();
		$Mapper			= Mapper::getInstance();

		$parseBase		= parse_url( $ConfigCore->getAppBaseUrl() );
		if( $parseBase['host'] !== $this->getHost() || (isset($parseBase['scheme']) === true && $parseBase['scheme'] === 'https' && $_SERVER['HTTPS'] !== 'on') ) {
            if (isset($parseBase['scheme']) === true) {
                $url = $parseBase['scheme'] . '://' . $parseBase['host'] . $this->getUri();
            } else {
                $url = $this->getScheme() . '://' . $parseBase['host'] . $this->getUri();
            }

			header( 'HTTP/1.1 301 Moved Permanently' );
			header( 'Location: ' . $url );
			exit();
		}

		// Obtem o mapeamento da URL
		$Mapper->parser( $this->_requestUri );
		$language		= $Mapper->getLanguage();
		$module			= $Mapper->getModule();
		$controller		= $Mapper->getController();
		$action			= $Mapper->getAction();
		$params			= $Mapper->getParams();

		Controller::loadController( $module, $controller, $action, $params, $language );
	}

	/**
	 * Obtem o tipo do Schema da requisição (HTTP ou HTTPS)
	 *
	 * @return string
	 */
	public function getScheme() {
		if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
			return self::SCHEME_HTTPS;
		}

		return self::SCHEME_HTTP;
	}

	/**
	 * Obtem o host da requisição.
	 *
	 * @return string
	 */
	public function getHost() {
		return $_SERVER['SERVER_NAME'];
	}

	/**
	 * Obtem a porta utilizada pela requisição.
	 *
	 * @return integer
	 */
	public function getPort() {
		return $_SERVER['SERVER_PORT'];
	}

	/**
	 * Obtem o caminho do arquivo da requisição.
	 *
	 * @return string
	 */
	public function getPath() {
		return $_SERVER['SCRIPT_NAME'];
	}

	/**
	 * Obtem o trecho URI da requisição.
	 *
	 * @return string
	 */
	public function getUri() {
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Obtem os fragmentos da requisição.
	 *
	 * @return String
	 */
	public function getFragment() {
		return false;
	}

	/**
	 * Verifica se a requisição é segura.
	 *
	 * @return boolean
	 */
	public function isSecure() {
		return ( $this->getScheme() === self::SCHEME_HTTPS );
	}

	/**
	 * Obtém o status de redirecionamento.
	 *
	 * @return string
	 */
	public function getRedirectStatus() {
		return $_SERVER['REDIRECT_STATUS'];
	}

	/**
	 * Obtém a hora em que a requisição foi realizada.
	 *
	 * @return string
	 */
	public function getRequestTime() {
		return $_SERVER['REQUEST_TIME'];
	}

	/**
	 * Obtém o endereço IP do cliente.
	 *
	 * @return string
	 */
	public function getUserIp() {
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Obtém o nome de usuário informado na requisição.
	 *
	 * @return string
	 */
	public function getUserName() {
		return isset( $_SERVER['PHP_AUTH_USER'] ) ? $_SERVER['PHP_AUTH_USER'] : false;
	}

	/**
	 * Obtém a senha de autenticação informado na requisição.
	 *
	 * @return string
	 */
	public function getUserPassword() {
		return isset( $_SERVER['PHP_AUTH_PW'] ) ? $_SERVER['PHP_AUTH_PW'] : false;
	}

	/**
	 * Obtem uma instáncia de um objeto Uri com os dados da requisição.
	 *
	 * @return Uri
	 */
	public function getRequestUri() {
		// Caso não exista a variável URL, provavelmente não existe o htaccess ou
		// os parâmetros concidiram com o nome do arquivo de bootstrap, exemplo:
		// http://localhost/index onde o arquivo de bootstrap chama index.php
		// neste caso o arquivo será executado e os outros parâmetros não serão
		// reconhecidos. Para resolver este problema, é feito o tratamento sobre
		// a URL original e a URL Base pré-definida.
		if( !isset( $_GET['url'] ) ) {
			$ConfigCore		= ConfigCore::getInstance();
			$request		= $_SERVER['REQUEST_URI'];
			$replace		= array(
								'index.php/',
								'index.php',
								$ConfigCore->getAppBaseUrl()->path
							);
			$request		= str_replace( $replace, '', $request );

			$_GET['url']	= $request;
		}

		// Monta o Objeto URI que será utilizado em toda a estrutura da framework,
		// para manipulação da URL de requisição.
		$Uri			= new Uri();
		$Uri->scheme	= $this->getScheme();
		$Uri->user		= $this->getUserName();
		$Uri->password	= $this->getUserPassword();
		$Uri->host		= $this->getHost();
		$Uri->port		= $this->getPort();
		$Uri->path		= $this->getPath();
		$Uri->addQueryArray( $_GET );
		$Uri->fragment	= $this->getFragment();

		return $Uri;
	}
}