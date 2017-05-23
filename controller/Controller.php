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
 * Classe base para Controllers de aplicações.
 *
 * Em uma aplicação com arquitetura MVC utilizam controllers para literalmente
 * controlar uma determinada ação. O controle de uma ação são as lógicas e regras
 * envolvidas ao lidar com a interpretação dos dados de uma requisição,
 * utilizar modelos de dados conforme necessário, e fornecer as informações
 * necessárias para o retorno devido. Sendo assim esta classe é reponsável por
 * identificar a ação da aplicação correta que deve ser executada, capturar os dados
 * da requisição e iniciar o processo.
 *
 * Para criar um Controller de aplicação, basta criar uma classe com o sufixo
 * "Controller" e extender para esta classe. Após criado, todos os métodos públicos
 * pertencentes a ela serão dados como Actions, podendo ser chamados via URL.
 *
 * <code>
 * class AplicationController extends Controller {
 *     // Módulo ao qual o Controller pertence
 *     public $module = 'nomedomodulo';
 *
 *     public function index() {
 *         // Código
 *     }
 *
 *     public function anotherAction( $param1, $param2 ) {
 *         // Código
 *     }
 * }
 * </code>
 *
 * @package MaiaFW\Controller
 * @category Core
 * @version 1.0
 */
abstract class Controller extends Object {
	/**
	 * Define se a view será renderizada automaticamente. Ao definir este atributo
	 * como false, a view só será renderizada se receber o comando explícito para
	 * isto.
	 *
	 * @var boolean
	 */
	protected $autoRender = true;

	/**
	 * Linguagem utilizada na view e nas respostas devidas para a requisição.
	 *
	 * @var string
	 */
	protected $language;

	/**
	 * Módulo a qual o Controller pertence.
	 *
	 * @var string
	 */
	protected $module = null;


	/**
	 * View que deve ser renderizada e retornada ao final da requisição.
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Variáveis passadas na requisição HTTP. Neste array estão todas as variáveis
	 * das superglobais <code>$_POST</code>, <code>$_FILES</code>,
	 * <code>$_GET</code>. Para obter todos esses dados basta utilizar o método
	 * <code>getAllHttpData()</code> ou para uma variável específica utilize
	 * <code>getHttpData()</code>.
	 *
	 * @var array
	 */
	private $httpData;

	/**
	 * Inicia o Controller definindo os atributos necessários para executá-lo.
	 *
	 * No construtor do Controller é definido a linguagem que está sendo utilizada
	 * na requisição, disponibilizando para os mecanismos responsáveis pela tradução
	 * da aplicação qual pacote de linguagem deve ser carregada, os dados de
	 * requisição são capturados e tratados, e uma instancia da classe View é
	 * iniciada para possibilitar que as Actions possam fornecer as informações
	 * necessárias para a renderização.
	 *
	 * @param string $language Linguagem utilizada na requisição
	 * @return void
	 */
	public function __construct( $language = null ) {
		parent::__construct();
		$this->language		= $language;
		$this->httpData		= array_merge_recursive($_POST, $_FILES, $_GET);
		$this->view			= new View();
	}

	/**
	 * Executa uma Action de um controller.
	 *
	 * Carrega o arquivo do Controller da aplicação e executa a Action desejada
	 * passando os parâmetros informados.
	 *
	 * @param string $module Moduleo do controller
	 * @param string $controller Nome do controller
	 * @param string $action Nome da Action
	 * @param array $params Parâmetros para execução da Action
	 * @param string $language Linguagem para o retorno.
	 * @return Controller Retorna o Controller carregado.
	 */
	public static function loadController(
		$module,
		$controller,
		$action,
		$params = array(),
		$language = null
	) {
		// Monta o caminho do arquivo do controller
		$path = SYSROOT . 'modules' . DS;
		if( $module !== false ) {
			$path .= $module . DS . $controller . DS . $controller . '.ctrl.php';
		} else {
			$path .= $controller . DS . $controller . '.ctrl.php';
		}

		// Carrega o arquivo do controller
		if( file_exists( $path ) === false ) {
			throw new FwException(
				'Não foi possível encontrar o Controller "' . $controller . '".'
			);
		}
		require_once $path;

		// Intancia o controller
		$controllerClass = $controller . 'Controller';
		if( class_exists( $controllerClass ) === false ) {
			throw new FwException(
				'Arquivo de Controller exitente, mas a classe "' .
				$controllerClass . '" não foi encontrada.'
			);
		}
		$object = new $controllerClass( $language );
		$object->module = $module;

		// Verifica se é possível executar a Action
		if( method_exists( $object, $action ) === false ) {
			throw new FwException(
				'Método "' . $action . '" não existe no
				controller "' . $controller . '".'
			);
		}

		$method = new ReflectionMethod($object, $action);
		if( $method->isUserDefined() === false && $method->isPublic() === false ) {
			throw new FwException(
				'Método "' . $action . '" não existe
				no Controller "' . $controller . '".'
			);
		}

		// Executa a função de pré-execução do controller.
		$object->beforeFilter();

		// Chama o método desejado do controller.
		call_user_func_array( array( $object, $action ), $params );

		// Verifica se será automaticamente renderizado.
		if( $object->autoRender ) {
			// Executa a função de pré-renderização.
			$object->beforeRender();

			// Inicia a renderização.
			$object->view->module = $module;
			$object->view->controller = $controller;
			if( $object->view->name == null ) {
				$object->view->name = $action;
			}
			$object->view->render();

			// Executa a função de pós-renderização.
			$object->afterRender();
		}
		// Executa a função de pós-execução do controller.
		$object->afterFilter();
		return $object;
	}

	/**
	 * Retorna se a requisição foi originada de uma chamada Ajax.
	 *
	 * Retorna se a requisição foi originada de uma chamada Ajax baseado nos dados
	 * do cabeçalho da requisição.
	 *
	 * @return boolean Retorna TRUE caso seja uma requisição ajax e FALSE caso
	 * contrário.
	 */
	public function isAjaxRequest() {
		return isset( $_SERVER['HTTP_X_REQUESTED_WITH'] )
			&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	/**
	 * Obtem todas os dados passados na requisição.
	 *
	 * Os dados passados na requisição são tratados e armazenados durante o início
	 * da execução da chamada. Atráves deste método, é possível recuperar a lista
	 * completa das váriaveis passadas, seja por POST, GET, PUT, DELETE, ou FILE.
	 *
	 * @return mixed[] Array com as váriaveis superglobais de requisição mescladas.
	 */
	protected function getAllHttpData() {
		return $this->httpData;
	}

	/**
	 * Obtem uma variável passada por HTTP específica.
	 *
	 * Os dados passados na requisição são tratados e armazenados durante o início
	 * da execução da chamada. Atráves deste método, é possível recuperar uma
	 * váriavel específica passada, seja por POST, GET, PUT, DELETE, ou FILE.
	 *
	 * @param string $varName Nome da variável
	 * @return mixed Retorna o valor da variável específica ou FALSE caso não exista.
	 */
	protected function getHttpData( $varName ) {
		if( isset( $this->httpData[$varName] ) ) {
			return $this->httpData[$varName];
		}
		return false;
	}

	/**
	 * Verifica a existência de uma variável passada por HTTP.
	 *
	 * Verifica se uma variável passada por HTTP existe no conjunto de dados de
	 * requisição recuperados no início da execução da chamada.
	 *
	 * @param string $varName Nome da variável
	 * @return boolean Retorna TRUE caso exista ou FALSE caso contrário.
	 */
	protected function isSetHttpData( $varName ) {
		$httpData = $this->getAllHttpData();
		foreach ( $httpData as $httpVarName => $value ) {
			if( $httpVarName == $varName ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Altera valor para uma variável passada via HTTP.
	 *
	 * Irá alterar o valor de uma variavel passada via HTTP cujo foi recuperada no
	 * início da execução da chamada.
	 *
	 * @param string $varName Nome da variável
	 * @param mixed $value Novo valor para variável
	 * @return void
	 */
	protected function setHttpData( $varName, $value ) {
		$this->httpData[$varName] = $value;
	}

	/**
	 * Remove uma variável passada via HTTP.
	 *
	 * Remove uma variável do conjunto de dados de requisição que foi recuperado no
	 * início da execução da chamada.
	 *
	 * @param string $varName Nome da variável
	 * @return void
	 */
	protected function unsetHttpData( $varName ) {
		unset( $this->httpData[$varName] );
	}

	/**
	 * Método executado antes da execução de qualquer Action do controller.
	 *
	 * Este método é utilizado quando há a necessidade de executar uma lógica antes
	 * de qualquer Action de um Controller, como por exemplo verificação de
	 * permissões de usuários, iniciar variáveis com valores padrões, etc.
	 *
	 * Sobreescreva este método no Controller da aplicação para executar o que for
	 * necessário.
	 *
	 * @return void
	 */
	protected function beforeFilter() {
	}

	/**
	 * Método executado após a execução de qualquer Action do controller.
	 *
	 * Este método é utilizado quando há a necessidade de executar uma lógica após
	 * qualquer Action de um Controller, como por exemplo verificação de permissões
	 * de usuários, iniciar variáveis com valores padrões, etc.
	 *
	 * Sobreescreva este método no Controller da aplicação para executar o que for
	 * necessário.
	 *
	 * @return void
	 */
	protected function afterFilter() {
	}

	/**
	 * Método executado antes da renderização de uma View.
	 *
	 * Este método é utilizado quando há a necessidade de executar uma lógica antes
	 * da renderização da View de todas as Actions.
	 *
	 * Sobreescreva este método no Controller da aplicação para executar o que for
	 * necessário.
	 *
	 * @return void
	 */
	protected function beforeRender() {
	}

	/**
	 * Método executado após a renderização de uma View.
	 *
	 * Este método é utilizado quando há a necessidade de executar uma lógica após
	 * a renderização da View de todas as Actions.
	 *
	 * Sobreescreva este método no Controller da aplicação para executar o que for
	 * necessário.
	 *
	 * @return void
	 */
	protected function afterRender() {
	}

	/**
	 * Realiza o redirecionamento para outra Action de um Controller.
	 *
	 * Executa o rediredionamento para outra Action de um Controller passando os
	 * parâmetros necessários. Caso apenas o Controller seja indicado, a Action
	 * que será chamada é a "index".
	 *
	 * @param string $controller Nome do controller
	 * @param string $action Nome da Action
	 * @param (string|int)[] $params Parâmetros para execução da Action
	 * @return void
	 */
	protected function redirect(
		$controller,
		$action = 'index',
		$params = array()
	) {
		$url = UrlMaker::toAction( $controller, $action, $params );
		header( 'Location: ' . $url );
		exit();
	}

	/**
	 * Realiza o redirecionamento para outra Action de um Controller pertencente a
	 * um determinado módulo.
	 *
	 * Executa o rediredionamento para outra Action de um Controller pertencente
	 * a um determinado módulo, passando os parâmetros necessários. Caso apenas o
	 * Controller seja indicado, a Action
	 * que será chamada é a "index".
	 *
	 * @param string $module Nome do módulo
	 * @param string $controller Nome do controller
	 * @param string $action Nome da Action
	 * @param (string|int)[] $params Parâmetros para execução da Action
	 * @return void
	 */
	protected function redirectToModule(
		$module,
		$controller,
		$action = 'index',
		$params = array()
	) {
		$url = UrlMaker::toModuleAction( $module, $controller, $action, $params );
		header( 'Location: ' . $url );
		exit();
	}

	/**
	 * Obtem o nome do Controller da aplicação.
	 *
	 * Retorna o nome do Controller da aplicação, como por exemplo para um
	 * Controller de aplicação definido como "AppController" irá retornar "App".
	 *
	 * @return string Nome da controller
	 */
	protected function getControllerName() {
		$className = new ReflectionClass($this);
		$className = $className->getName();
		$className = strtolower( substr($className, 0, -10) );

		return $className;
	}
}