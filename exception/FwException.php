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
 * @todo Aumentar a possibilidade de reutilização desta classe, possibilitando a
 * utilização de seus métodos de forma mais flexivel, tanto por classes filhas como
 * por outros sistemas. A idéia inclui a adição de variáveis estáticas para
 * armazenar informações de log, emails para envio etc. A possibilidade de tornar
 * esta classe abstrata é relevante.
 *
 * @todo Permitir criar uma lista de destinatários para cada tipo de erro.
 *
 * @package MaiaFW\Exceptions
 * @category Core
 * @version 1.0
 */
class FwException extends Exception {
	private $_logFileName;
	private $_logFileExtension;
	private static $_errors = array();
	private static $_additionalLogs = array();
	private static $_sqlLog = array();

	private static $_pathLogs = null;
	private static $_report = 0;
	private static $_debug = 0;

	private static $_emailSubject = 'Exception';
	private static $_emailsReport = array();

	/**
	 * Inicia a instancia da exceção cuidando de como os logs devem ser gerados de
	 * acordo com as configurações feitas para o aplicativo.
	 *
	 * @param string $message
	 * @param integer $code
	 * @param Exception $previous
	 */
	public function __construct( $message = null, $code = 0, Exception $previous = null ) {
		parent::__construct( trim( $message ), $code, $previous);

		// Define o nome dos arquivos de logs que serão gerados.
		$this->_logFileName	= date('Ymd_His') . '_' . str_pad( rand(0, 99999), 5, '0' );

		// Verifica se foi setado o caminho que os logs serão salvos.
		if( self::$_pathLogs === null) {
			self::$_pathLogs = dirname( __FILE__ );
		}

		// Inicia as chamadas dos tratamentos de erros desejados.
		switch ( self::$_report ) {
			case 1:
				$this->sendMail();
				break;

			case 2:
				$this->saveLog();
				$this->sendMail();
				break;

			default:
				$this->saveLog();
				break;
		}

		switch ( self::$_debug ) {
			case 1:
				echo $this->makeHtmlLog();
				break;

			default:
				header('HTTP/1.0 404 Not Found');

				$log  = '<!DOCTYPE HTML>
						<html lang="pt-br">
						<head>
						<meta charset=utf-8>
						<title>Log de erro [ ' . date('Y-m-d H:i:s') . ' ]</title>
						<style type="text/css">
							* { margin: 0; padding: 0 }
							body { background: #eee; color: #333; font-family: Arial, Helvetica, sans-serif; font-size: 12px; text-align: center }
							#fwErrors { width: 750px; margin: 0 auto; text-align: center }
							#fwErrors header { display: block; margin: 20px 0; padding: 20px; /*background: #fff;*/ border-radius: 15px; -moz-border-radius: 15px; -webkit-border-radius: 15px; -op-border-radius: 15px }
							#fwErrors header h1 { display: block; color: #555; font-weight: normal }
							#fwErrors header h1 strong { font-size: 3em; display: block; font-weight: normal }
							#fwErrors header hr { margin: 30px; color: #eee }
							#fwErrors header div { text-align: left }
							#fwErrors header div p { margin: 2px 0; padding: 5px; background: #f2f2f2 }
							#fwErrors table { width: 100%; margin: 15px 0; padding: 15px; background: #fff; border-radius: 15px; -moz-border-radius: 15px; -webkit-border-radius: 15px; -op-border-radius: 15px }
							#fwErrors table caption { width: 80%; margin: 10px auto; padding: 5px; border-bottom: 1px dashed #ccc; font-size: 1.3em }
							#fwErrors table thead tr th { background: #ccc; padding: 5px; text-align: left }
							#fwErrors table tbody tr td,
							#fwErrors table tbody tr th { padding: 5px; text-align: left }
							#fwErrors table tbody tr:nth-child(odd) { background: #f2f2f2 }
							#fwErrors table tbody tr:hover { outline: 1px dashed #900; background: #fcc; border-collapse: collapse }
						</style>
						</head>
						<body><div id="fwErrors">';

				// Monta o cabeçalho do log
				$log .= '<header>';
				$log .= '<h1><br><br><strong>404</strong><hr>Página não encontrada.<br><br><br><br></h1>';
				$log .= '</header>';

				$log .= '</div></body></html>';

				echo $log;

				break;
		}

	}

	public static function setReport( $type ) {
		self::$_report = (int) $type;
	}

	public static function setDebug( $type ) {
		self::$_debug = (int) $type;
	}

	public static function setEmailSubject( $subject ) {
		self::$_emailSubject = $subject;
	}

	public static function addEmail( $email ) {
		if( preg_match( '/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $email ) ) {
			if( !in_array( $email, self::$_emailsReport ) ) {
				self::$_emailsReport[] = $email;
			} else {
				file_put_contents( self::$_pathLogs . 'ExceptionError_' . $this->_logFileName . '.txt', 'Tentativa de adicionar email para envio de logs duplicado.' );
			}
		} else {
			file_put_contents( self::$_pathLogs . 'ExceptionError_' . $this->_logFileName . '.txt', 'Email para envio de logs inválido.' );
		}
	}

	public static function getEmails() {
		return $this->reportEmails;
	}

	public static function setPathLogs( $path ) {
		if( file_exists( $path ) ) {
			if( substr( $path, -1 ) == DIRECTORY_SEPARATOR ) {
				self::$_pathLogs = $path;
			} else {
				self::$_pathLogs = $path . DIRECTORY_SEPARATOR;
			}
		}

	}

	public static function setSqlLog( $sql, $time ) {
		self::$_sqlLog[] = array(
			'sql'	=> $sql,
			'time'	=> $time
		);
	}

	public static function getSqlLog() {
		return self::$_sqlLog;
	}

	private static function setError( $message, $code, $file, $line ) {
		self::$_errors[] = array(
			'message'	=> $message,
			'code'		=> $code,
			'file'		=> $file,
			'line'		=> $line
		);
	}

	public static function getErrors() {
		return self::$_errors;
	}

	/**
	 * Captura os erros lançados pelo PHP e trata-os como uma exceção.
	 *
	 * @param int $code
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 *
	 * @throws FwException
	 * @static
	 */
	public static function throwError( $code, $message, $file, $line ) {
		self::setError( $message, $code, $file, $line );
		//throw new FwException( $message, $code );
	}

	public function saveLog() {
		if( empty( self::$_pathLogs ) ) {
			self::$_pathLogs = dirname( __FILE__ ) . DS;
		}

		$log		= $this->makeLog();
		$logname 	= $this->_logFileName . '.' . $this->_logFileExtension;
		file_put_contents( self::$_pathLogs . $logname, $log );
	}

	/**
	 * Envia o relatório da exceção disparada por email.
	 */
	public function sendMail() {
		// Monta o relatório
		$boundary = 'XYZ-' . date("dmYis") . '-ZYX';

		$header	 = 'MIME-Version: 1.0' . "\n";
		$header	.= 'X-Mailer: PHP/' . phpversion() . "\n";
		$header	.= "Content-type: multipart/mixed; ";
		$header	.= 'charset=utf-8; ';
		$header	.= 'boundary="' . $boundary . "\"\n";
		$header	.= 'Date: ' . date("D, d M Y H:i:s O", time() - 3600) . "\n";
		$header	.= 'From: ' . $_SERVER['SERVER_ADMIN'] . '<' . $_SERVER['SERVER_ADMIN'] . ">\n";

		$body  = '--' . $boundary . "\n";
		$body .= "Content-Transfer-Encoding: 7bit;\n";
		$body .= 'Content-type: text/html' . "; ";
		$body .= 'charset=utf-8'. ";\n\n";
		$body .= $this->makeHtmlLog();
		$body .= "\n\n--" . $boundary . "\n";

		// Percorre a lista de destinatários enviando o log.
		foreach( self::$_emailsReport as $email ) {
			if ( !mail( $email, self::$_emailSubject . date(' H:i:s d/m/Y'), $body, $header ) ) {
				file_put_contents( self::$_pathLogs . 'ExceptionError.txt', 'Ocorreu um erro ao enviar e-mail de disparo de Exceção.');
			}
		}
	}

	/**
	 * Gera arquivo de log da exceção. Os logs podem ser gerados nos formatos de XML,
	 * HTML ou TXT.
	 *
	 * @param string $format
	 */
	private function makeLog( $format = null ) {
		switch ( $format ) {
			case 'xml':
				$this->_logFileExtension = 'xml';
				return $this->makeXmlLog();

			case 'html':
				$this->_logFileExtension = 'htm';
				return $this->makeHtmlLog();

			/**
			 * Gera um log textual formatado
			 */
			default:
				$this->_logFileExtension = 'txt';
				return $this->makeTextLog();
		}
	}

	private function makeXmlLog() {

	}

	private function makeHtmlLog() {
		$Benchmark			= Benchmark::getInstance();
		$Benchmark->stop( 'MaiaFW.load' );
		$BenchmarkValues	= $Benchmark->getAllMarks();
		$ConfigCore			= ConfigCore::getInstance();

		// Obtem o array com o caminho percorrido até ocorrer o erro.
		$trace	= $this->getTrace();

		// Monta o cabeçalho do HTML
		$log  = '<!DOCTYPE HTML>
				<html lang="pt-br">
				<head>
				<meta charset=utf-8>
				<title>Log de erro [ ' . date('Y-m-d H:i:s') . ' ]</title>
				<style type="text/css">
					* { margin: 0; padding: 0 }
					body { background: #eee; color: #333; font-family: Arial, Helvetica, sans-serif; font-size: 12px; text-align: center }
					#fwErrors { width: 750px; margin: 0 auto; text-align: center }
					#fwErrors header { display: block; margin: 20px 0; padding: 20px; background: #fff; border-radius: 15px; -moz-border-radius: 15px; -webkit-border-radius: 15px; -op-border-radius: 15px }
					#fwErrors header h1 { display: block; color: #900 }
					#fwErrors header div { text-align: left }
					#fwErrors header div p { margin: 2px 0; padding: 5px; background: #f2f2f2 }
					#fwErrors table { width: 100%; margin: 15px 0; padding: 15px; background: #fff; border-radius: 15px; -moz-border-radius: 15px; -webkit-border-radius: 15px; -op-border-radius: 15px }
					#fwErrors table caption { width: 80%; margin: 10px auto; padding: 5px; border-bottom: 1px dashed #ccc; font-size: 1.3em }
					#fwErrors table thead tr th { background: #ccc; padding: 5px; text-align: left }
					#fwErrors table tbody tr td,
					#fwErrors table tbody tr th { padding: 5px; text-align: left }
					#fwErrors table tbody tr:nth-child(odd) { background: #f2f2f2 }
					#fwErrors table tbody tr:hover { outline: 1px dashed #900; background: #fcc; border-collapse: collapse }
				</style>
				</head>
				<body><div id="fwErrors">';

		// Monta o cabeçalho do log
		$log .= '<header>';
		$log .= '<h1>' . $this->getMessage() . '</h1>';
		$log .= '<div><p class="date"><strong>Date:</strong> ';
		$log .= '<time datetime="' . date('Y-m-d\TH:i:s') . '">' . date('Y-m-d H:i:s') . '</time></p>';
		$log .= '<p class="line"><strong>Line:</strong> ' . $this->getLine() . '</p>';
		$log .= '<p class="file"><strong>File:</strong> ' . $this->getFile() . '</p></div>';
		$log .= '</header>';

		// Monta o cabeçalho do backtrace
		$log .= '<table summary="">';
		$log .= '<caption>Backtrace</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">#ID</th>';
		$log .= '	<th scope="col">Class</th>';
		$log .= '	<th scope="col">Function</th>';
		$log .= '	<th scope="col">Params</th>';
		$log .= '	<th scope="col">Line</th>';
		$log .= '	<th scope="col">File</th>';
		$log .= '</tr></thead>';

		// Inicia o preenchimento do log de backtrace
		$log .= '<tbody>';
		foreach( $trace as $line => $values ) {
			$log .= '<tr>';
			$log .= '<td>' . $line . '</td>';

			if( isset( $values['class'] ) ) {
				$log .= '<td>' . $values['class'] . '</td>';
			} else {
				$log .= '<td></td>';
			}

			if( isset( $values['function'] ) ) {
				$log .= '<td>' . $values['function'] . '</td>';
			} else {
				$log .= '<td></td>';
			}

			// Faz o tratamento dos argumentos passados a um método para
			// evitar erros.
			$params = array();
			if ( isset( $values['args'] ) && count( $values['args'] ) ) {
				foreach ( $values['args'] as $param ) {
					$params[] = is_array( $param ) ? 'array' : $param;
				}
			}
			$log .= '<td>' . implode(', ', $params ) . '</td>';

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
		$log .= '</tbody></table>';

		// Monta o cabeçalho do log de erros adicionais
		$errors			= $this->getErrors();
		$totalErrors	= count( $errors );
		$log .= '<table summary="">';
		$log .= '<caption>Errors <span class="count"><em>Total:</em> ';
		$log .= $totalErrors . '</span></caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">#ID</th>';
		$log .= '	<th scope="col">Code</th>';
		$log .= '	<th scope="col">Message</th>';
		$log .= '	<th scope="col">File</th>';
		$log .= '	<th scope="col">Line</th>';
		$log .= '</tr></thead>';

		// Inicia o preenchimento do log de erros adicionais
		$log .= '<tbody>';
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
		$log .= '</tbody></table>';

		// Monta o cabeçalho do log das configurações da aplicação.
		$log .= '<table summary="">';
		$log .= '<caption>Application variables</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">Variable</th>';
		$log .= '	<th scope="col">Value</th>';
		$log .= '</tr></thead>';

		// Exibe as configurações da aplicação
		$log .= '<tbody>';
		$log .= '<tr><th scope="row">Debug</th><td>' 				. $ConfigCore->getDebug()						. '</td></tr>';
		$log .= '<tr><th scope="row">Report</th><td>' 				. $ConfigCore->getReport()						. '</td></tr>';
		$log .= '<tr><th scope="row">Environment</th><td>' 			. $ConfigCore->getEnvironment()					. '</td></tr>';
		$log .= '<tr><th scope="row">AppName</th><td>' 				. $ConfigCore->getAppName()						. '</td></tr>';
		$log .= '<tr><th scope="row">AppVersion</th><td>'			. $ConfigCore->getAppVersion()					. '</td></tr>';
		$log .= '<tr><th scope="row">AppLanguage</th><td>'			. $ConfigCore->getAppLanguage()					. '</td></tr>';
		$log .= '<tr><th scope="row">AppCharset</th><td>'			. $ConfigCore->getAppCharset()					. '</td></tr>';
		$log .= '<tr><th scope="row">AppBaseUrl</th><td>'			. $ConfigCore->getAppBaseUrl()					. '</td></tr>';
		$log .= '<tr><th scope="row">Appindex</th><td>'				. $ConfigCore->getAppIndex()					. '</td></tr>';
		$log .= '<tr><th scope="row">AppAdmin</th><td>'				. $ConfigCore->getAppAdmin()					. '</td></tr>';
		$log .= '<tr><th scope="row">Salt</th><td>'					. $ConfigCore->getSecuritySalt()				. '</td></tr>';
		$log .= '<tr><th scope="row">Tempo total gasto</th><td>'	. $BenchmarkValues['MaiaFW.load']['total']	. '</td></tr>';
		$log .= '</tbody></table>';

		// Exibe as variáveis de servidor
		$log .= '<h2>HTTP variables</h2>';


		// $_POST
		$log .= '<table summary="">';
		$log .= '<caption>POST</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">Variable</th>';
		$log .= '	<th scope="col">Value</th>';
		$log .= '</tr></thead>';
		$log .= '<tbody>';
		foreach( $_POST as $key => $value ) {
			$log .= '<tr><th scope="row">' . $key . '</th><td>' . $value . '</td></tr>';
		}
		$log .= '</tbody></table>';


		// $_GET
		$log .= '<table summary="">';
		$log .= '<caption>GET</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">Variable</th>';
		$log .= '	<th scope="col">Value</th>';
		$log .= '</tr></thead>';
		$log .= '<tbody>';
		foreach( $_GET as $key => $value ) {
			$log .= '<tr><th scope="row">' . $key . '</th><td>' . $value . '</td></tr>';
		}
		$log .= '</tbody></table>';


		// $_FILES
		$log .= '<table summary="">';
		$log .= '<caption>FILES</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">Variable</th>';
		$log .= '	<th scope="col">Value</th>';
		$log .= '</tr></thead>';
		$log .= '<tbody>';
		foreach( $_FILES as $key => $value ) {
			$log .= '<tr><th scope="row">' . $key . '</th><td>' . $value . '</td></tr>';
		}
		$log .= '</tbody></table>';


		// $_ENV
		$log .= '<table summary="">';
		$log .= '<caption>ENV</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">Variable</th>';
		$log .= '	<th scope="col">Value</th>';
		$log .= '</tr></thead>';
		$log .= '<tbody>';
		foreach( $_ENV as $key => $value ) {
			$log .= '<tr><th scope="row">' . $key . '</th><td>' . $value . '</td></tr>';
		}
		$log .= '</tbody></table>';


		// $_SESSION
		$log .= '<table summary="">';
		$log .= '<caption>SESSION</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">Variable</th>';
		$log .= '	<th scope="col">Value</th>';
		$log .= '</tr></thead>';
		$log .= '<tbody>';
		if( isset( $_SESSION ) ) {
			foreach( $_SESSION as $key => $value ) {
				$log .= '<tr><th scope="row">' . $key . '</th><td>' . $value . '</td></tr>';
			}
		}
		$log .= '</tbody></table>';

		// $_COOKIE
		$log .= '<table summary="">';
		$log .= '<caption>COOKIE</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">Variable</th>';
		$log .= '	<th scope="col">Value</th>';
		$log .= '</tr></thead>';
		$log .= '<tbody>';
		foreach( $_COOKIE as $key => $value ) {
			$log .= '<tr><th scope="row">' . $key . '</th><td>' . $value . '</td></tr>';
		}
		$log .= '</tbody></table>';

		// $_REQUEST
		$log .= '<table summary="">';
		$log .= '<caption>REQUEST</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">Variable</th>';
		$log .= '	<th scope="col">Value</th>';
		$log .= '</tr></thead>';
		$log .= '<tbody>';
		foreach( $_REQUEST as $key => $value ) {
			$log .= '<tr><th scope="row">' . $key . '</th><td>' . $value . '</td></tr>';
		}
		$log .= '</tbody></table>';

		// $_SERVER
		$log .= '<table summary="">';
		$log .= '<caption>SERVER</caption>';
		$log .= '<thead><tr>';
		$log .= '	<th scope="col">Variable</th>';
		$log .= '	<th scope="col">Value</th>';
		$log .= '</tr></thead>';
		$log .= '<tbody>';
		foreach( $_SERVER as $key => $value ) {
			$log .= '<tr><th scope="row">' . $key . '</th><td>' . $value . '</td></tr>';
		}
		$log .= '</tbody></table>';

		$log .= '</div></body></html>';

		return $log;
	}

	private function makeTextLog() {
		$Benchmark			= Benchmark::getInstance();
		$Benchmark->stop( 'MaiaFW.load' );
		$BenchmarkValues	= $Benchmark->getAllMarks();
		$ConfigCore			= ConfigCore::getInstance();

		// Obtem o array com o caminho percorrido até ocorrer o erro.
		$trace	= $this->getTrace();

		// Monta o cabeçalho do log
		$log  = '[ ' . date('Y-m-d H:i:s') . " ]\n\n";

		$log .= $this->getMessage() . "\n";

		// Monta o cabeçalho do backtrace
		$log .= "\n[ Backtrace ]\n";
		$log .= str_pad( '#ID', 3, ' ', STR_PAD_LEFT )			. ' | ';
		$log .= str_pad( 'Class', 30, ' ', STR_PAD_RIGHT )		. ' | ';
		$log .= str_pad( 'Function', 30, ' ', STR_PAD_RIGHT )	. ' | ';
		$log .= str_pad( 'Params', 100, ' ', STR_PAD_RIGHT )	. ' | ';
		$log .= str_pad( 'Line', 10, ' ', STR_PAD_RIGHT )		. ' | ';
		$log .= 'File';
		$log .= "\n";

		// Inicia o preenchimento do log
		foreach( $trace as $line => $values ) {
			$log .= str_pad( $line, 3, ' ', STR_PAD_LEFT ) . ' | ';

			if( isset( $values['class'] ) ) {
				$log .= str_pad( $values['class'], 30, ' ', STR_PAD_RIGHT ) . ' | ';
			} else {
				$log .= str_pad( '', 30, ' ', STR_PAD_RIGHT ) . ' | ';
			}

			if( isset( $values['function'] ) ) {
				$log .= str_pad( $values['function'], 30, ' ', STR_PAD_RIGHT ) . ' | ';
			} else {
				$log .= str_pad( '', 30, ' ', STR_PAD_RIGHT ) . ' | ';
			}

			// Faz o tratamento dos argumentos passados a um método para
			// evitar erros.
			$params = array();
			if ( isset( $values['args'] ) && count( $values['args'] ) ) {
				foreach ( $values['args'] as $param ) {
					$params[] = is_array( $param ) ? 'array' : $param;
				}
			}
			$log .= str_pad( implode(', ', $params ), 100, ' ', STR_PAD_RIGHT ) . ' | ';

			if( isset( $values['line'] ) ) {
				$log .= str_pad( $values['line'], 10, ' ', STR_PAD_RIGHT ) . ' | ';
			}

			if( isset( $values['file'] ) ) {
				$log .= $values['file'];
			}

			$log .= "\n";
		}


		// Monta o cabeçalho de logs de erros adicionais
		$errors			= $this->getErrors();
		$totalErrors	= count( $errors );
		$log .= "\n[ Errors (" . $totalErrors . ") ]\n";
		$log .= str_pad( '#ID', 3, ' ', STR_PAD_LEFT )			. ' | ';
		$log .= str_pad( 'Code', 30, ' ', STR_PAD_RIGHT )		. ' | ';
		$log .= str_pad( 'Message', 30, ' ', STR_PAD_RIGHT )	. ' | ';
		$log .= str_pad( 'Line', 10, ' ', STR_PAD_RIGHT )		. ' | ';
		$log .= 'File';
		$log .= "\n";

		// Inicia o preenchimento do log
		foreach( $errors as $error => $values ) {
			$log .= str_pad( $error, 3, ' ', STR_PAD_LEFT ) . ' | ';

			if( isset( $values['code'] ) ) {
				$log .= str_pad( $values['code'], 30, ' ', STR_PAD_RIGHT ) . ' | ';
			} else {
				$log .= str_pad( '', 30, ' ', STR_PAD_RIGHT ) . ' | ';
			}

			if( isset( $values['message'] ) ) {
				$log .= str_pad( $values['message'], 30, ' ', STR_PAD_RIGHT ) . ' | ';
			} else {
				$log .= str_pad( '', 30, ' ', STR_PAD_RIGHT ) . ' | ';
			}

			if( isset( $values['line'] ) ) {
				$log .= str_pad( $values['line'], 10, ' ', STR_PAD_RIGHT ) . ' | ';
			}

			if( isset( $values['file'] ) ) {
				$log .= $values['file'];
			}

			$log .= "\n";
		}


		// Exibe as configurações de aplicação.
		$log .= "\n[ Application variables ]\n";
		$log .= str_pad( '  Debug: ', 35, ' ' )			. $ConfigCore->getDebug()							. "\n";
		$log .= str_pad( '  Report: ', 35, ' ' )		. $ConfigCore->getReport()							. "\n";
		$log .= str_pad( '  Environment: ', 35, ' ' )	. $ConfigCore->getEnvironment()						. "\n";
		$log .= str_pad( '  AppName: ', 35, ' ' )		. $ConfigCore->getAppName()							. "\n";
		$log .= str_pad( '  AppVersion: ', 35, ' ' )	. $ConfigCore->getAppVersion()						. "\n";
		$log .= str_pad( '  AppLanguage: ', 35, ' ' )	. $ConfigCore->getAppLanguage()						. "\n";
		$log .= str_pad( '  AppCharset: ', 35, ' ' )	. $ConfigCore->getAppCharset()						. "\n";
		$log .= str_pad( '  AppBaseUrl: ', 35, ' ' )	. $ConfigCore->getAppBaseUrl()						. "\n";
		$log .= str_pad( '  Appindex: ', 35, ' ' )		. $ConfigCore->getAppIndex()						. "\n";
		$log .= str_pad( '  AppAdmin: ', 35, ' ' )		. $ConfigCore->getAppAdmin()						. "\n";
		$log .= str_pad( '  Salt: ', 35, ' ' )			. $ConfigCore->getSecuritySalt()					. "\n";
		$log .= str_pad( '  Tempo total gasto: ', 35, ' ' ) . $BenchmarkValues['MaiaFW.load']['total']	. "\n";

		// Exibe as variáveis de servidor
		$log .= "\n[ HTTP variables ]\n";

		$log .= "\nPOST: \n";
		foreach( $_POST as $key => $value ) {
			$log .= '  ' . str_pad( $key, 35, ' ' ) . ' : ' . $value . "\n";
		}

		$log .= "\nGET: \n";
		foreach( $_GET as $key => $value ) {
			$log .= '  ' . str_pad( $key, 35, ' ' ) . ' : ' . $value . "\n";
		}

		$log .= "\nFILES: \n";
		foreach( $_FILES as $key => $value ) {
			$log .= '  ' . str_pad( $key, 35, ' ' ) . ' : ' . $value . "\n";
		}

		$log .= "\nENV: \n";
		foreach( $_ENV as $key => $value ) {
			$log .= '  ' . str_pad( $key, 35, ' ' ) . ' : ' . $value . "\n";
		}

		$log .= "\nSESSION: \n";
		if( isset( $_SESSION ) ) {
			foreach( $_SESSION as $key => $value ) {
				$log .= '  ' . str_pad( $key, 35, ' ' ) . ' : ' . $value . "\n";
			}
		}

		$log .= "\nCOOKIE: \n";
		foreach( $_COOKIE as $key => $value ) {
			$log .= '  ' . str_pad( $key, 35, ' ' ) . ' : ' . $value . "\n";
		}

		$log .= "\nREQUEST: \n";
		foreach( $_REQUEST as $key => $value ) {
			$log .= '  ' . str_pad( $key, 35, ' ' ) . ' : ' . $value . "\n";
		}

		$log .= "SERVER: \n";
		foreach( $_SERVER as $key => $value ) {
			$log .= '  ' . str_pad( $key, 35, ' ' ) . ' : ' . $value . "\n";
		}

		return $log;
	}
}