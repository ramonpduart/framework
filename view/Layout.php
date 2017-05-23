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
 * O layout é utilizado como apresentação a um usuário ou recurso. Através dele é
 * possível dar uma cara mais amigável para o utilizador da aplicação, ou mesmo
 * estabelecer um padrão inicial de resposta a uma requisição de um dispositivo.
 *
 * Esta classe possui recursos para montar toda a interface utilizando as melhores
 * práticas, deixando-a leve e consistente.
 *
 * @package MaiaFW\View
 * @category Core
 * @version 1.0
 */
class Layout extends Object {
	/**
	 * Layout a ser renderizado.
	 *
	 * @var string
	 */
	private $layoutName;

	/**
	 * Conteúdo da view que será exibida no layout.
	 *
	 * @var string
	 */
	private $content = null;

	/**
	 * Indica o titulo para o layout.
	 *
	 * @var string
	 */
	private $title = null;

	/**
	 * Lista de folhas de estilos que serão utilizados no layout.
	 *
	 * @var array
	 */
	private $css = array();

	/**
	 * Lista de javascripts que serão utilizados no layout.
	 *
	 * @var array
	 */
	private $js = array();

	/**
	 * Lista de javascripts que serão utilizados pela view
	 *
	 * @var array
	 */
	private $jsView = array();

	/**
	 * Guarda variáveis que serão passadas para o layout
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * Inicia um layout desejado. Caso nenhum parametro seja informado, o layout
	 * iniciado será o nomeado "default".
	 *
	 * @param string $layout Nome do layout.
	 * @return void
	 */
	public function __construct( $layout = 'default' ) {
		parent::__construct();
		$this->layoutName = $layout;
	}

	/**
	 * Método para obter as variáveis utilizadas no layout.
	 *
	 * @param string $attribute Nome do atributo.
	 * @return mixed
	 */
	public function __get( $attribute ) {
		switch ( $attribute ) {
			case 'layoutName':
				return $this->layoutName;
				break;

			case 'content':
				return $this->content;
				break;

			case 'title':
				return $this->title;
				break;

			case 'css':
				return $this->css;
				break;

			case 'js':
				return $this->js;
				break;

			default:
				return $this->_data[$attribute];
				break;
		}
	}

	/**
	 * Método para setar os valores das variáveis utilizadas no layout.
	 *
	 * @param string $attribute Nome do atributo.
	 * @param mixed $value Novo valor para o atributo.
	 * @return void
	 */
	public function __set( $attribute, $value ) {
		switch( $attribute ) {
			case 'layoutName':
				if( is_string( $value ) && preg_match( '/^[A-Za-z0-9_-]+$/', $value ) )
					$this->layoutName = $value;
				else
					throw new FwException( 'Nome de layout informado é inválido.' );
				break;

			case 'content':
				$this->content = $value;
				break;

			case 'title':
				if( is_string( $value ) )
					$this->title = $value;
				else
					throw new FwException( 'Valor para título do layout inválido.' );
				break;

			default:
				$this->_data[$attribute] = $value;
				break;
		}
	}

	public function __isset( $name ) {
		switch( $name ) {
			case 'layoutName':
			case 'content':
			case 'title':
				return true;

			default:
				return isset( $this->_data[ $name ] );
		}
	}

	/**
	 * Renderiza o layout.
	 *
	 * @return void
	 */
	public function render() {
		if( file_exists( SYSROOT . 'layouts' . DS . $this->layoutName . '.lay.php' ) ) {
			require_once SYSROOT . 'layouts' . DS . $this->layoutName . '.lay.php';
		} else
			throw new FwException( 'O Layout requisitado não foi encontrado ou
									não existe' );
	}

	/**
	 * Adiciona um CSS ao layout.
	 *
	 * @param string $css Nome do arquivo CSS.
	 * @param string $media Tipo de media que o CSS informado será aplicado.
	 * @return void
	 */
	public function addCss( $css, $media = 'all' ) {
		$this->css[] = array( 'src' => $css, 'media' => $media );
	}

	/**
	 * Remove um CSS do layout.
	 *
	 * @param string $css Nome do arquivo CSS.
	 * @param string $media Tipo de media que o CSS informado será aplicado.
	 * @return void
	 */
	public function removeCss( $css, $media = 'all'  ) {
		$position	= array_search( array(  'src' => $css,
											'media' => $media ) , $this->css );
		if( $position !== false ) {
			unset( $this->css[$position] );
		}
	}

	/**
	 * Obtem a lista de CSS formatada em HTML, pronta para a inclusão no layout.
	 *
	 * @return string
	 */
	public function getCss() {
		if( $this->_app->getEnvironment() == 'development' ) {
			$return = '';
			if( count( $this->css ) > 0 ) {
				foreach( $this->css as $css ) {
					$return .= '<link type="text/css" rel="stylesheet" href="';
					$return .= $this->_app->getAppBaseUrl() . 'css/' . $css['src'];
					$return .= '" media="' . $css['media'] . '" />';
				}
			}
			return $return;
		} else {
			$ds = DIRECTORY_SEPARATOR;
			$cachePath = SYSROOT . 'public' . $ds . 'cache' . $ds;
			$cacheFile = $cachePath . 'layout_css_' . $this->layoutName . '.php';

			if( !file_exists( $cacheFile ) ) {
				$cssContents = '';
				if( count( $this->css ) > 0 ) {
					$buffer = '';
					foreach( $this->css as $css ) {
						$buffer .= file_get_contents(
							SYSROOT . 'public' .
							$ds . 'css' .
							$ds . $css['src']
						);
					}

					// shorthand hex color codes
					$buffer = preg_replace('/(?<![\'"])#([0-9a-z])\\1([0-9a-z])\\2([0-9a-z])\\3(?![\'"])/i', '#$1$2$3', $buffer);

					// Remove comments
					$buffer = preg_replace( '/\/\*(.*?)\*\//is', '', $buffer);

					// semicolon/space before closing bracket > replace by bracket
					$buffer = preg_replace( '/;?\s*}/', '}', $buffer);

					// bracket, colon, semicolon or comma preceeded or followed by whitespace > remove space
					$buffer = preg_replace( '/^\s*|\s*$/m', '', $buffer);

					// Remove whitespace
					$cssContents = str_replace(array("\r\n", "\r", "\n", "\t"), '', $buffer);
				}
				ob_start();
				include( FWROOT .
						$ds . 'libs' .
						$ds . 'helpers' .
						$ds . 'resource' .
						$ds . 'css' .
						$ds . 'css.res.php'
				);
				$css = ob_get_contents();
				ob_end_clean();

				if( !file_exists( $cachePath ) ) {
					if( !mkdir( $cachePath, 0775, true ) ) {
						throw new FwException( 'The cache folder does not exist and cannot be created.' );
					}
				}

				file_put_contents( $cacheFile  , $css );
				chmod( $cacheFile , 0644 );
			}

			$return = '';
			$return .= '<link type="text/css" rel="stylesheet" href="';
			$return .= $this->_app->getAppBaseUrl() . 'cache/layout_css_' . $this->layoutName . '.php';
			$return .= '" media="all" />';
			return $return;
		}
	}

	/**
	 * Adiciona um script ao layout.
	 *
	 * @param string $script Nome do arquivo do script.
	 * @return void
	 */
	public function addJs( $script ) {
		$this->js[] = $script;
	}

	/**
	 * Remove um script do layout.
	 *
	 * @param string $script Nome do arquivo do script.
	 * @return void
	 */
	public function removeJs( $script ) {
		unset( $this->js[$script] );
	}

	/**
	 * Adiciona um script ao layout.
	 *
	 * @param string $script Nome do arquivo do script.
	 * @return void
	 */
	public function addViewJs( $script ) {
		$this->jsView[] = $script;
	}

	/**
	 * Remove um script do layout.
	 *
	 * @param string $script Nome do arquivo do script.
	 * @return void
	 */
	public function removeViewJs( $script ) {
		unset( $this->jsView[$script] );
	}

	/**
	 * Obtem a lista de CSS formatada em HTML, pronta para a inclusão no layout.
	 *
	 * @return string
	 */
	public function getJs() {
		$return = '';
		$return.= $this->getLayoutJsLinks();
		$return.= $this->getViewJsLinks();
		return $return;
	}

	private function getLayoutJsLinks() {
		if( $this->_app->getEnvironment() == 'development' ) {
			$return = '';
			if( count( $this->js ) > 0 ) {
				foreach( $this->js as $script ) {
					$return .= '<script src="';
					$return .= $this->_app->getAppBaseUrl() . 'scripts/' . $script;
					$return .= '"></script>';
				}
			}
		} else {
			$ds = DIRECTORY_SEPARATOR;
			$cachePath = SYSROOT . 'public' . $ds . 'cache' . $ds;
			$cacheFile = $cachePath . 'layout_script_' . $this->layoutName . '.php';

			if( !file_exists( $cacheFile ) ) {
				$jsContents = '';
				if( count( $this->js ) > 0 ) {
					$buffer = '';
					foreach( $this->js as $script ) {
						$buffer .= "\n" . file_get_contents(
							SYSROOT . 'public' .
							$ds . 'scripts' .
							$ds . $script
						);
					}
				}

				$buffer = str_replace( "\t", ' ', $buffer);
                $buffer = str_replace(array("\r\n", "\r"), "\n", $buffer);

				// $buffer = preg_replace( '/^([\'"]).*?(?<!\\\\)\\1/s', '\\0', $buffer );
				// $buffer = preg_replace( '/^\\\\\//', '\\0', $buffer );
				// $buffer = preg_replace( '/^\s*\/\/.*$[\r\n]/m', '', $buffer );
				// $buffer = preg_replace( '/^\/\*.*?\*\//s', '', $buffer );
				// $buffer = preg_replace( '/\/\*(.*?)\*\//is', '', $buffer);

    //             // Tenta remover o comentários de final de linha
				// $buffer = preg_replace( '/\/\/\s?[\s\d\w,\'\-\+\[\]\(\)\|\*"\.@#$%&:]+$/m', '', $buffer);

				// $operators = array(
				// 	// arithmetic
				// 	'+', '-', '*', '/', '%', '++', '--', // @todo: slash can be
				// 	// assignment
				// 	'=', '+=', '-=', '*=', '/=', '%=',
				// 	'<<=', '>>=', '>>>=', '&=', '^=', '|=',
				// 	// bitwise
				// 	'&', '|', '^', '~', '<<', '>>', '>>>',
				// 	// comparison
				// 	'==', '===', '!=', '!==', '>', '<', '>=', '<=',
				// 	// logical
				// 	'&&', '||', '!',
				// 	// string
				// 	// + and += already added
				// 	// member
				// 	'.', '[', ']',
				// 	// conditional
				// 	'?', ':',
				// 	// comma
				// 	',',

				// 	// function call
				// 	'(', ')',
				// 	// object literal ({ & } are also used as block delimiter, but
				// 	// we can strip whitespace around that too)
				// 	'{', '}', ':',
				// 	// statement terminator
				// 	';',
				// );
				// $delimiter = array_fill( 0, count($operators), '/' );
				// $operators = array_map( 'preg_quote', $operators, $delimiter );
				// $buffer = preg_replace( '/^\s*('. implode('|', $operators) .')\s/s', '\\1', $buffer );

				// $operators = array_merge( $operators, array('else', 'while', 'catch', 'finally', '$' ) );
				// $buffer = preg_replace( '/^([\)\}])(?!('. implode('|', $operators) .'))/s', '\\1;', $buffer);

				// $buffer = preg_replace( '/^\s*\n\s/s', ';', $buffer);
				// $buffer = preg_replace( '/^\s+/ms', '', $buffer);
				// $buffer = preg_replace( '/^;\}/', '}', $buffer);

    //             $buffer = str_replace(array(" \n", "\n "), "\n", $buffer);
    //             $buffer = preg_replace('/\n+/', "\n", $buffer);

				$jsContents = trim($buffer);

				ob_start();
				include( FWROOT .
						$ds . 'libs' .
						$ds . 'helpers' .
						$ds . 'resource' .
						$ds . 'js' .
						$ds . 'js.res.php'
				);
				$script = ob_get_contents();
				ob_end_clean();

				if( !file_exists( $cachePath ) ) {
					if( !mkdir( $cachePath, 0775, true ) ) {
						throw new FwException( 'The cache folder does not exist and cannot be created.' );
					}
				}

				file_put_contents( $cacheFile  , $script );
				chmod( $cacheFile , 0644 );
			}

			$return = '';
			$return .= '<script src="';
			$return .= $this->_app->getAppBaseUrl() . 'cache/layout_script_' . $this->layoutName . '.php';
			$return .= '"></script>';
		}
		return $return;
	}

	private function getViewJsLinks() {
		$return = '';
		if( count( $this->jsView ) > 0 ) {
			foreach( $this->jsView as $script ) {
				$return .= '<script src="';
				$return .= $this->_app->getAppBaseUrl() . 'scripts/modules/' . $script;
				$return .= '"></script>';
			}
		}
		return $return;
	}

	public function getViewJs() {
		$return = '';
		if( count( $this->jsView ) ) {
			foreach( $this->jsView as $script ) {
				$return .= '<script src="';
				$return .= $this->_app->getAppBaseUrl() . 'scripts/modules/' . $script;
				$return .= '"></script>';
			}
		}
		return $return;
	}

	public function addPartial( $name, $params = array() ) {
		extract($params);
		$name = trim($name);
		require SYSROOT . 'layouts' . DS . $name . '.prt.php';
	}
}