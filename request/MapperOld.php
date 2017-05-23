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
 * [en]
 *
 * [pt-br]
 * Faz o mapeamento através de uma instância de uma URI, e aplica as rotas definidas,
 * extraindo todo o necessário para identificar a ação que deve ser executados, como
 * o controller, action e parâmetros.
 *
 * @package MaiaFW\Request
 * @category Configurations
 * @version 1.0
 */
class Mapper {

	/**
	 * Armazena a instância de um objeto único desta classe.
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Armazena os mapeamento personalizados de URL.
	 *
	 * Permite indicar como será tratado uma determinada URL, predefinindo os
	 * parametros padrões como language, conttroller, action.
	 *
	 * @var $routes array
	 */
	private $_routes = array();

	/**
	 * Armazena a URL com o mapeamento aplicado.
	 *
	 * @var $map array
	 */
	private $_map;

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
	 * Extrai as informações de uma URL necessárias para a execução da requisição.
	 *
	 * @param Uri $Uri
	 * @return void
	 */
	public function parser( Uri $Uri ) {
		// Extrai as informações de acessos.
		$url		= $Uri->getQueryVar( 'url' );
		if ( substr( $url, -1 ) == '/' ) {
			$url = substr( $url, 0, -1 );
		}
		$url		= str_replace( '//', '/', $url);

		// Obtem as configurações da aplicação.
		$ConfigCore		= ConfigCore::getInstance();
		$ConfigRoutes	= ConfigRoutes::getInstance();
		$routes			= $ConfigRoutes->getRoutes();
		$nameActiveRoute= false;
		$routeValues	= array();
		$keywords		= array(
			'/',
			':module',
			':language',
			':controller',
			':action',
			':params'
		);

		$keywordsPattern= array(
			'\/',
			'(?P<language>[\w\-]{2,6})',
			'(?P<module>[\w_\-]+)',
			'(?P<controller>[\w_\-]+)',
			'(?P<action>[\w_\-]+)',
			'(?P<params>.+)'
		);

		$matches = array();
		foreach ($routes as $route => $config) {
			$map = '/^' . str_replace( $keywords, $keywordsPattern, $config['path']) . '\/?$/i';
			if( preg_match( $map, $url, $matches ) === 1) {
				$nameActiveRoute = $route;
				$routeValues = $matches;
				break;
			}
		}

		// Se for encontrado uma rota válida a utiliza
		if( $nameActiveRoute != false ) {
			$activeRoute = $routes[$nameActiveRoute];

			// Obtem os valores passados por URL
			if( isset( $routeValues['language'] ) ) {
				$this->setLanguage($routeValues['language']);
			}

			if( isset( $routeValues['module'] ) ) {
				$this->setModule($routeValues['module']);
			}

			if( isset( $routeValues['controller'] ) ) {
				$this->setController($routeValues['controller']);
			}

			if( isset( $routeValues['action'] ) ) {
				$this->setAction($routeValues['action']);
			}

			if( isset( $routeValues['params'] ) ) {
				$params = explode('/', $routeValues['params']);
				$this->setParams($params);
			}

			// Obtem configurações fixas de rotas
			if( isset( $activeRoute['module'] ) ) {
				$this->setModule($activeRoute['module']);
			}

			if( isset( $activeRoute['controller'] ) ) {
				$this->setController($activeRoute['controller']);
			}

			if( isset( $activeRoute['action'] ) ) {
				$this->setAction($activeRoute['action']);
			}

		// Caso não seja encontrada nenhuma rota válida, faz o roteamento padrão
		} else {
			$params		= explode('/', $url);
			// Inicia o mapeamento.
			// Verifica se utiliza sistema de tradução.
			if( $ConfigCore->getUseTranslations() === true ) {
				$language = array_shift( $params );
				$this->setLanguage($language);
			}

			$modules = $ConfigRoutes->getModules();
			if( count( $modules ) > 0 ) {
				$module = current( $params );
				if( in_array( $module, $modules ) ) {
					$this->setModule( $module );
					array_shift( $params );
				}
			}

			// Obtem informação sobre o controller a ser chamado.
			$controller = array_shift( $params );
			$this->setController($controller);

			// Obtem o nome do método.
			$action = array_shift( $params );
			$this->setAction($action);

			// Obtem os parametros que serão informados ao método.
			$this->setParams($params);
		}
	}

	public function setLanguage( $value ) {
		if( preg_match( '/[\(\)\[\]\{\}\|#$%&*@!\+=\:;\.,?\\\\<>^~´`\'"]/', $value ) ) {
			throw new FwException( 'Caracteres inválidos na URL. (' . $value . ')' );
		}
		$this->_map['language'] = $value;
	}

	public function setController( $value ) {
		if( preg_match( '/[\(\)\[\]\{\}\|#$%&*@!\+=\:;\.,?\\\\<>^~´`\'"-]/', $value ) ) {
			throw new FwException( 'Caracteres inválidos na URL. (' . $value . ')' );
		}

		if( $value === null || $value === '' ) {
			$ConfigCore		= ConfigCore::getInstance();
			$value = $ConfigCore->getAppIndex();
		}

		$this->_map['controller'] = $value;
	}

	public function setModule( $value ) {
		if( preg_match( '/[\(\)\[\]\{\}\|#$%&*@!\+=\:;\.,?\\\\<>^~´`\'"-]/', $value ) ) {
			throw new FwException( 'Caracteres inválidos na URL. (' . $value . ')' );
		}

		if( $value === null || $value === '' ) {
			$value = false;
		}

		$this->_map['module'] = $value;
	}

	public function setAction( $value ) {
		if( preg_match( '/[\(\)\[\]\{\}\|#$%&*@!\+=\:;\.,?\\\\<>^~´`\'"-]/', $value ) ) {
			throw new FwException( 'Caracteres inválidos na URL. (' . $value . ')' );
		}
		$this->_map['action'] = $value;
	}

	public function setParams( $value ) {
		$this->_map['params'] = $value;
	}

	/**
	 * Retorna a linguagem informada na URL que foi mapeada.
	 *
	 * @return string
	 */
	public function getLanguage() {
		if( isset( $this->_map['language'] ) ) {
			return $this->_map['language'];
		}
		return null;
	}

	/**
	 * Retorna o controller requisitado na URL que foi mapeada. Caso nenhum
	 * controller tenha sido informado na URL, retorna o controller "index".
	 *
	 * @return string
	 */
	public function getController() {
		if( isset( $this->_map['controller'] ) && $this->_map['controller'] != '' ) {
			return $this->_map['controller'];
		}
		return 'index';
	}

	/**
	 * Retorna o modulo requisitado na URL que foi mapeada. Caso nenhum
	 * modulo tenha sido informado na URL, retorna o mosulo como nulo".
	 *
	 * @return string
	 */
	public function getModule() {
		if( isset( $this->_map['module'] ) && $this->_map['module'] != '' ) {
			return $this->_map['module'];
		}
		return false;
	}

	/**
	 * Retorna a ação do controller que deve ser executada pela requisição da URL
	 * mapeada. Caso nenhuma ação tenha sido informada na URL, retorna a ação
	 * "index".
	 *
	 * @return string
	 */
	public function getAction() {
		if( isset( $this->_map['action'] ) && $this->_map['action'] != '' ) {
			return $this->_map['action'];
		}
		return 'index';
	}

	/**
	 * Obtém os parâmetros informados na URL que foi mapeada.
	 *
	 * @return array
	 */
	public function getParams() {
		if( isset( $this->_map['params'] ) && $this->_map['params'] != '' ) {
			return $this->_map['params'];
		}
		return array();
	}
}