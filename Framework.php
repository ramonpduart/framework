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
 * Classe responsável por inicar a execução de uma requisição.
 *
 * Esta Classe é responsável por iniciar as constantes de rotas da framework,
 * extrair informações passadas via URL, e garantir a segurança inicial.
 *
 * Aqui é identificado quais são os controllers que devem ser chamados, e quais
 * pacotes e idiomas será usado durante a exibição das views.
 *
 * Esta classe utiliza o padrão Singleton para garantir que não seja instanciada mais
 * de uma vez durante o projeto.
 *
 * @package MaiaFW
 * @category Core
 * @version 1.0
 */
final class Framework {
	/**
	 * Armazena a instância de um objeto único desta classe.
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Indica se a Framework já foi inicializada, impedindo a duplicação das
	 * configurações e ações iniciais.
	 *
	 * @var boolean
	 */
	private static $initialized = false;

	/**
	 * Caminhos para inclusão de arquivos de classes.
	 *
	 * @var array
	 */
	private static $fwPaths = array(
		'config',
		'exception',
		'view',
		'controller',
		'request',
		'environment',
		'model',
		'libs/helpers',
		'libs/reports',
		'libs/types'
	);

	/**
	 * Caminhos adicionais para inclusão de arquivos de classes.
	 *
	 * @var array
	 */
	private static $additionalPaths = array();

	/**
	 * Este objeto não pode ser instânciado, por fazer uso do padrão Singleton.
	 * Sendo assim o construtor está setado como privado, para evitar que seja
	 * instânciado fora desta classe.
	 *
	 * @return void
	 */
	private function __construct() {
		$Benchmark = Benchmark::getInstance();
		$Benchmark->start( 'MaiaFW.load' );
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
	 * Inicializa a estrutura do framework, instanciando o controle desejado e
	 * chamando o método correto. Todas as variáveis da aplicação são atribuidas e
	 * as conferências são realizadas através deste método.
	 *
	 * @return boolean Retorna TRUE em caso de sucesso.
	 */
	public function initialize() {
		// Verifica se já foi iniciado o processo, retornando true caso tenha.
		if( self::$initialized === true ) {
			return true;
		}

		// Obtem as configurações do aplicativo
		$ConfigCore	= ConfigCore::getInstance();

		// Configurações de disparo de exceções.
		FwException::setDebug( $ConfigCore->getDebug() );
		FwException::setReport( $ConfigCore->getReport() );
		FwException::setPathLogs( $ConfigCore->getPathLogs() );
		FwException::setEmailSubject( $ConfigCore->getReportEmailSubject() );

		// Configura o handler de captura de erros.
		set_error_handler( array( 'FwException', 'throwError' ) );
		ini_set( 'display_errors', 'on' );

		// Inicializa o ambiente de trabalho
		Environment::initialize( $ConfigCore->getEnvironment() );

		// Obtem as informações passadas por URL
		$Dispatcher	= Dispatcher::getInstance();
		$Dispatcher->dispatch();

		// Altera a flag da Framework, indicando que já foi inicializada.
		self::$initialized = true;
		return true;
	}

	/**
	 * Importa uma classe da biblioteca para ser usada.
	 *
	 * Esse método é resposável pela execução do carregamento das classes utilizadas
	 * pela framework e aplicação.
	 *
	 * @param string $class Nome da classe a ser importada.
	 * @return boolean Retorna TRUE em caso de sucesso.
	 */
	public static function import( $class ) {
		// Verifica se o nome da classe informada é válida.
		if( is_string( $class ) === false && preg_match( '/^[A-Za-z0-9_-]+$/', $class ) === false ) {
			throw new FwException(
				'O nome da classe contém caracteres inválidos ou o
				parâmetro passado não é do tipo string.'
			);
		}

		// Verifica se a classe ou interface já está carregada.
		if( class_exists( $class ) || interface_exists( $class ) ) {
			return true;
		}

		if( self::loadCoreClass( $class ) ) {
			return true;
		}

		if( self::loadLibraryClass( $class ) ) {
			return true;
		}

		if( self::loadModelClass( $class ) ) {
			return true;
		}

		if( self::loadRepositoryClass( $class ) ) {
			return true;
		}

		if( self::loadServiceClass( $class ) ) {
			return true;
		}

		if( self::loadClassInAdditionalPaths( $class ) ) {
			return true;
		}

		throw new FwException(
			'Não foi possível encontrar a classe "'. $class . '".'
		);
	}

	/**
	 * Adiciona pastas alternativas para carregar classes.
	 *
	 * @param string $path Caminho completo da pasta.
	 * @return boolean Retorna TRUE em caso de sucesso.
	 */
	public static function addLoadPatches( $path ) {
		if (is_dir( realpath( SYSROOT . $path ) ) ) {
			self::$additionalPaths[] = realpath( SYSROOT . $path );
			return true;
		}
		throw new FwException( 'O caminho "'. $path . '" não é válido.' );
	}

	/**
	 * Habilita todos os autoloads necessários para o funcionamento da framework.
	 *
	 * Realiza o registro do autoload da framework, indicando o método
	 * <code>import()</code>.
	 *
	 * @return void
	 */
	public static function registerAutoloads() {
		spl_autoload_register( array('Framework', 'import') );
	}

	/**
	 * Executa os procedimentos necessários para a finalização dos recursos
	 * utilizados pelo framework, como banco de dados, sessões, leitura de arquivos,
	 * entre outros.
	 *
	 * @return void
	 */
	public function __destruct() {
		if( !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest' ) {
			$sqls				= FwException::getSqlLog();
			$totalSQLs			= count( $sqls );

			$errors				= FwException::getErrors();
			$totalErrors		= count( $errors );

			$ConfigCore			= ConfigCore::getInstance();

			if( $ConfigCore->getEnvironment() == 'development' && $ConfigCore->getDebug() == '1' ) {
				$Benchmark			= Benchmark::getInstance();
				$Benchmark->stop( 'MaiaFW.load' );
				$BenchmarkValues	= $Benchmark->getAllMarks();

				echo '<div id="fw_painel">';
				echo '<nav id="fw_tabs"><ul>';
					echo '<li><a href="#fw_general">General</a></li>';
					echo '<li><a href="#fw_sqls">SQLs<span class="count';
					if ( $totalSQLs < 15 ) {
						echo ' success';
					} elseif( $totalSQLs >= 15 && $totalSQLs <= 30 ) {
						echo ' warning';
					}
					echo '"><em>Total:</em>' . $totalSQLs . '</span></a></li>';
					echo '<li><a href="#fw_errors">Errors<span class="count';
					if ( $totalErrors == 0 ) {
						echo ' success';
					}
					echo '"><em>Total:</em>' . $totalErrors . '</span></a></li>';
				echo '</ul></nav>';
				echo '<section id="fw_general">';
				echo '<table summary="">';
				echo '<caption>General</caption>';
				echo '<tr><th scope="row">Debug:</th><td>'				. $ConfigCore->getDebug()						. '</td>';
				echo '<tr><th scope="row">Report:</th><td>'				. $ConfigCore->getReport()						. '</td>';
				echo '<tr><th scope="row">Environment:</th><td>'		. $ConfigCore->getEnvironment()					. '</td>';
				echo '<tr><th scope="row">AppName:</th><td>'			. $ConfigCore->getAppName()						. '</td>';
				echo '<tr><th scope="row">AppVersion:</th><td>'			. $ConfigCore->getAppVersion()					. '</td>';
				echo '<tr><th scope="row">AppLanguage:</th><td>'		. $ConfigCore->getAppLanguage()					. '</td>';
				echo '<tr><th scope="row">AppCharset:</th><td>'			. $ConfigCore->getAppCharset()					. '</td>';
				echo '<tr><th scope="row">AppBaseUrl:</th><td>'			. $ConfigCore->getAppBaseUrl()					. '</td>';
				echo '<tr><th scope="row">Appindex:</th><td>'			. $ConfigCore->getAppIndex()					. '</td>';
				echo '<tr><th scope="row">AppAdmin:</th><td>'			. $ConfigCore->getAppAdmin()					. '</td>';
				echo '<tr><th scope="row">Salt:</th><td>'				. $ConfigCore->getSecuritySalt()				. '</td>';
				echo '<tr><th scope="row">Execution time:</th><td>'		. ( round( $BenchmarkValues['MaiaFW.load']['total'], 6) * 1000 )	. ' ms</td>';
				echo '<tr><th scope="row">Memory allocated:</th><td>'		. self::formatBytes( memory_get_usage(false) )		. '</td>';
				echo '<tr><th scope="row">Peak of memory allocated:</th><td>'	. self::formatBytes( memory_get_peak_usage(false) ) . '</td>';
				echo '</table>';
				echo '</section>';

				// Monta o cabeçalho do log de sqls
				echo '<section id="fw_sqls">';
				$log  = '<table summary="">';
				$log .= '<caption>Errors <span class="count"><em>Total:</em> ';
				$log .= $totalSQLs . '</span></caption>';
				$log .= '<thead><tr>';
				$log .= '	<th scope="col">#ID</th>';
				$log .= '	<th scope="col">Time</th>';
				$log .= '	<th scope="col">SQL</th>';
				$log .= '</tr></thead>';

				// Inicia o preenchimento do log de erros adicionais
				$log .= '<tbody>';
				if( $totalSQLs == 0 ) {
					$log .= '<tr><td colspan="3">No SQL executed</td></tr>';
				} else {
					foreach( $sqls as $sql => $values ) {
						$log .= '<tr>';
						$log .= '<td>' . $sql . '</td>';

						if( isset( $values['time'] ) ) {
							$log .= '<td>' . ( round($values['time'], 6) * 1000 ) . ' ms</td>';
						} else {
							$log .= '<td></td>';
						}

						if( isset( $values['sql'] ) ) {
							$log .= '<td>' . $values['sql'] . '</td>';
						} else {
							$log .= '<td></td>';
						}

						$log .= '</tr>';
					}
				}
				$log .= '</tbody></table></section>';
				echo $log;

				// Monta o cabeçalho do log de erros adicionais
				echo '<section id="fw_errors">';
				$log  = '<table summary="">';
				$log .= '<caption>Errors <span class="count"><em>Total: </em>';
				$log .= $totalErrors . '</span></caption>';
				$log .= '<thead><tr>';
				$log .= '	<th scope="col">#ID</th>';
				$log .= '	<th scope="col">Code</th>';
				$log .= '	<th scope="col">Message</th>';
				$log .= '	<th scope="col">Line</th>';
				$log .= '	<th scope="col">File</th>';
				$log .= '</tr></thead>';

				// Inicia o preenchimento do log de erros adicionais
				$log .= '<tbody>';
				if( $totalErrors == 0 ) {
					$log .= '<tr><td colspan="5">No errors</td></tr>';
				} else {
					foreach( $errors as $error => $values ) {
						$log .= '<tr>';
						$log .= '<td>' . $error . '</td>';

						if( isset( $values['code'] ) ) {
							$log .= '<td>' . $values['code'] . '</td>';
						} else {
							$log .= '<td></td>';
						}

						if( isset( $values['message'] ) ) {
							$log .= '<td>' . $values['message'] . '</td>';
						} else {
							$log .= '<td></td>';
						}

						if( isset( $values['line'] ) ) {
							$log .= '<td>' . $values['line'] . '</td>';
						} else {
							$log .= '<td></td>';
						}

						if( isset( $values['file'] ) ) {
							$log .= '<td>' . $values['file'] . '</td>';
						} else {
							$log .= '<td></td>';
						}

						$log .= '</tr>';
					}
				}
				$log .= '</tbody></table></section>';
				echo $log;
				echo '</section>';
				echo '</div>';
			}
		}
	}

	/**
	 * Converte o tamanho em bytes para unidades maiores.
	 *
	 * @param float $size Tamanho em bytes
	 * @return string Tamanho convertido
	 */
	private static function formatBytes($size) {
		$filesizename = array(
			' Bytes',
			' KB',
			' MB',
			' GB',
			' TB',
			' PB',
			' EB',
			' ZB',
			' YB'
		);
	    return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
	}

	/**
	 * Verifica a existência e carrega o arquivo de uma determinada classe básica da
	 * framework disponibilizando-a para uso.
	 *
	 * @param string $class Classe a ser carregada
	 * @return boolean Retorna TRUE em caso de sucesso, caso contrário retorna FALSE.
	 */
	private static function loadCoreClass( $class ) {
		if( file_exists( FWROOT . $class . '.php' ) ) {
			require_once FWROOT . $class . '.php';
			if( class_exists( $class ) || interface_exists( $class ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Verifica a existência e carrega o arquivo de uma determinada classe básica da
	 * biblioteca da framework disponibilizando-a para uso.
	 *
	 * @param string $class Classe a ser carregada
	 * @return boolean Retorna TRUE em caso de sucesso, caso contrário retorna FALSE.
	 */
	private static function loadLibraryClass( $class ) {
		foreach ( self::$fwPaths as $path ) {
			if( file_exists( FWROOT . $path . DS . $class . '.php' ) ) {
				require_once FWROOT . $path . DS . $class . '.php';

				if( class_exists( $class ) || interface_exists( $class ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Verifica a existência e carrega o arquivo de uma determinada Model,
	 * disponibilizando-a para uso.
	 *
	 * @param string $class Classe a ser carregada
	 * @return boolean Retorna TRUE em caso de sucesso, caso contrário retorna FALSE.
	 */
	private static function loadModelClass( $class ) {
		// Verifica se a classe é uma classe modelo das aplicações
		if( file_exists( SYSROOT . 'models' . DS . $class . '.mdl.php' ) ) {
			require_once SYSROOT . 'models' . DS . $class . '.mdl.php';

			if( class_exists( $class ) || interface_exists( $class ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Verifica a existência e carrega o arquivo de um determinado Repository,
	 * disponibilizando-o para uso.
	 *
	 * @param string $class Classe a ser carregada
	 * @return boolean Retorna TRUE em caso de sucesso, caso contrário retorna FALSE.
	 */
	private static function loadRepositoryClass( $class ) {
		// Verifica se a classe é uma classe repositório das aplicações
		if( substr( $class, -10, 10 ) == 'Repository' ) {
			$repositoryName = substr( $class, 0, -10 );
			$path = SYSROOT .
					'models' . DS .
					'repositories' . DS .
					$repositoryName . '.rep.php';
			if( file_exists( $path ) ) {
				require_once $path;

				if( class_exists( $class ) || interface_exists( $class ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Verifica a existência e carrega o arquivo de um determinado Service,
	 * disponibilizando-o para uso.
	 *
	 * @param string $class Classe a ser carregada
	 * @return boolean Retorna TRUE em caso de sucesso, caso contrário retorna FALSE.
	 */
	private static function loadServiceClass( $class ) {
		// Verifica se a classe é uma classe serviço das aplicações
		if( file_exists( SYSROOT . 'services' . DS . $class . '.svc.php' ) ) {
			require_once SYSROOT . 'services' . DS . $class . '.svc.php';

			if( class_exists( $class ) || interface_exists( $class ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Verifica a existência e carrega o arquivo de uma determinada classe nos
	 * caminhos adicionais pré-configurados, disponibilizando-a para uso.
	 *
	 * @param string $class Classe a ser carregada
	 * @return boolean Retorna TRUE em caso de sucesso, caso contrário retorna FALSE.
	 */
	private static function loadClassInAdditionalPaths( $class ) {
		foreach ( self::$additionalPaths as $path ) {
			if( file_exists( $path . DS . $class . '.php' ) ) {
				require_once $path . DS . $class . '.php';

				if( class_exists( $class ) || interface_exists( $class ) ) {
					return true;
				}
			} elseif( file_exists( $path . DS . $class . '.mdl.php' ) ) {
				require_once $path . DS . $class . '.mdl.php';

				if( class_exists( $class ) || interface_exists( $class ) ) {
					return true;
				}
			} elseif( file_exists( $path . DS . $class . '.svc.php' ) ) {
				require_once $path . DS . $class . '.svc.php';

				if( class_exists( $class ) || interface_exists( $class ) ) {
					return true;
				}
			}
		}
		return false;
	}
}