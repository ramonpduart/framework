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
 * As views são os retornos do sistema a uma requisição. Este retorno pode ser
 * projetado para a interação com o usuário, como tambem fornecer uma resposta a
 * requisições realizadas via código, como por exemplo uma requisição ajax.
 *
 * Através desta classe as views recebem os dados do controle e podem ser
 * renderizadas com as informações necessárias.
 *
 * @package MaiaFW\View
 * @category Core
 * @version 1.0
 */
class View extends Object {
	/**
	 * Layout a ser renderizado.
	 *
	 * @var string
	 */
	private $_layout;

	private $_module = null;

	/**
	 * Indica a qual controller a view pertence.
	 *
	 * Este atributo é utilizado pelo framework para identificar onde encontrar o
	 * arquivo da view.
	 *
	 * @var string
	 */
	private $_controller = null;

	/**
	 * Identifica a view desejada para exibição. Permite que uma ação possa
	 * reutilizar uma view de outra ação.
	 *
	 * @var string
	 */
	private $_name = null;

	/**
	 * Define se ao renderizar a view, o layout deve ser renderizado junto.
	 *
	 * @var boolean
	 */
	private $_autoLayout = true;

	/**
	 * Váriaveis utilizadas pela view.
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * Inicia a view definindo os atributos necessários como o layout utilizado.
	 */
	public function __construct() {
		parent::__construct();
		$this->_layout = new Layout();
	}

	/**
	 * Método para obter as variáveis utilizadas na view.
	 *
	 * @param string $attribute Atributo que se deseja obter o valor.
	 */
	public function __get( $attribute ) {
		switch ( $attribute ) {
			case 'layout':
				return $this->_layout;
				break;

			case 'module':
				return $this->_module;
				break;

			case 'controller':
				return $this->_controller;
				break;


			case 'name':
				return $this->_name;
				break;

			default:
				return $this->_data[$attribute];
				break;
		}
	}

	/**
	 * Método para setar os valores das variáveis utilizadas na view.
	 *
	 * @param string $attribute Nome do atributo que se deseja atribuir um valor.
	 * @param mixed $value Valor a ser setado ao atributo.
	 */
	public function __set( $attribute, $value ) {
		switch ( $attribute ) {
			case 'layout':
				$this->_layout = new Layout( $value );
				break;

			case 'module':
				$this->_module = $value;
				break;

			case 'controller':
				$this->_controller = $value;
				break;

			case 'name':
				$this->_name = $value;
				break;

			case 'autoLayout':
				$this->_autoLayout = (bool) $value;
				break;

			default:
				$this->_data[$attribute] = $value;
				break;
		}
	}

	/**
	 * Método responsável pela renderização das views.
	 *
	 * @param string $action Nome da action que a view deve ser executada.
	 */
	public function render() {
		// Armazena a montagem da view em buffer
		ob_start();

		$path = null;
		if( $this->_module !== null ) {
			$path = SYSROOT . 'modules' . DS . $this->_module . DS . $this->_controller . DS . 'views' . DS . strtolower( $this->_name ) . '.frm.php';
		} else {
			$path = SYSROOT . 'modules' . DS . $this->_controller . DS . 'views' . DS . strtolower( $this->_name ) . '.frm.php';
		}

		if( file_exists( $path ) )
			require_once $path;
		else
			throw new FwException( 'Não foi possível encontrar a view "' . $this->_name .
									'" do módulo "' . $this->_controller . '".' );

		// Libera o buffer armazenando o resultado do retorno da view no conteúdo
		// do Layout que será usado.
		$this->_layout->content = ob_get_clean();

		if( ini_get('zlib.output_compression' ) ) {
            ob_start();
		} else {
			ob_start('ob_gzhandler');
		}

		if( $this->_autoLayout ) {
			$this->_layout->render();
		} else {
			echo $this->_layout->content;
			echo $this->_layout->getViewJs();
		}
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $this->compressHtml( $buffer );
	}

	public function addPartial( $name, $controller = null, $params = array() ) {
		extract($params);
		$name = trim($name);
		$controller = $controller != null ? trim($controller) : $this->_controller;
		require SYSROOT . 'modules' . DS . $controller . DS . 'views' . DS . $name . '.prt.php';
	}

	public function addModulePartial( $name, $module = null, $controller = null, $params = array() ) {
		extract($params);
		$name = trim($name);
		$module = $module != null ? trim($module) : $this->_module;
		$controller = $controller != null ? trim($controller) : $this->_controller;
		require SYSROOT . 'modules' . DS . $module . DS . $controller . DS . 'views' . DS . $name . '.prt.php';
	}

	private function compressHtml( $buffer ) {
		if( $this->_app->getEnvironment() == 'development' ) {
			return $buffer;
		}
        $foundTxt = null;
        $foundPre = null;
        $foundCode = null;
        $foundScript = null;

		// Searching textarea and pre
        preg_match_all('#\<textarea.*\>.*\<\/textarea\>#Uis', $buffer, $foundTxt);
        preg_match_all('#\<pre.*\>.*\<\/pre\>#Uis', $buffer, $foundPre);
        preg_match_all('#\<code.*\>.*\<\/code\>#Uis', $buffer, $foundCode);
        preg_match_all('#\<script.*\>.*\<\/script\>#Uis', $buffer, $foundScript);

        // replacing both with <textarea>$index</textarea> / <pre>$index</pre>
        $buffer = str_replace($foundTxt[0], array_map(function($el){ return '<textarea>'.$el.'</textarea>'; }, array_keys($foundTxt[0])), $buffer);
        $buffer = str_replace($foundPre[0], array_map(function($el){ return '<pre>'.$el.'</pre>'; }, array_keys($foundPre[0])), $buffer);
        $buffer = str_replace($foundCode[0], array_map(function($el){ return '<code>'.$el.'</code>'; }, array_keys($foundCode[0])), $buffer);
        $buffer = str_replace($foundScript[0], array_map(function($el){ return '<script>'.$el.'</script>'; }, array_keys($foundScript[0])), $buffer);

        // your stuff
        $search = array(
            '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
            '/[^\S ]+\</s',  // strip whitespaces before tags, except space
            '/(\s)+/s',       // shorten multiple whitespace sequences
			'/<!--(.|\s)*?-->/',    //strip HTML comments
			'#(?://)?<!\[CDATA\[(.*?)(?://)?\]\]>#s' //leave CDATA alone
        );

        $replace = array(
            '>',
            '<',
            '\\1',
			'',
			"//<![CDATA[\n".'\1'."\n//]]>"
        );

        $buffer = preg_replace($search, $replace, $buffer);

        // Replacing back with content
        $buffer = str_replace(array_map(function($el){ return '<textarea>'.$el.'</textarea>'; }, array_keys($foundTxt[0])), $foundTxt[0], $buffer);
        $buffer = str_replace(array_map(function($el){ return '<pre>'.$el.'</pre>'; }, array_keys($foundPre[0])), $foundPre[0], $buffer);
        $buffer = str_replace(array_map(function($el){ return '<code>'.$el.'</code>'; }, array_keys($foundCode[0])), $foundCode[0], $buffer);
        $buffer = str_replace(array_map(function($el){ return '<script>'.$el.'</script>'; }, array_keys($foundScript[0])), $foundScript[0], $buffer);

        return $buffer;
	}
}