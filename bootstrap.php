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
 * Este o arquivo onde tudo começa. É responsável por inicializar todas as ações
 * responsáveis pelo funcionamento do sistema baseado neste framework. As camadas
 * MVC são criadas e é feito uma leitura dos dados passados via URL, como idiomas,
 * ações, dados a serem exibidos, entre outros. Aqui tambem é iniciado as constantes
 * mais usuáis, como os endereços da raiz do projeto, caminhos para os recursos da
 * framework, etc.
 *
 * @author Marcus Alexandre Gomes Maia
 * @copyright Interativa Ficcção
 * @package Framework
 * @category Configurations
 * @version 1.0.0
 */

	/**
	 *  Esta framework suporta apenas a versão 5.3 do PHP.
	 *  Um erro é gerado caso a versão seja anterior a 5.3.
	 */
	if( version_compare( PHP_VERSION, '5.3' ) < 0 ) {
	    trigger_error( 'This framework only works with PHP 5.3 or newer', E_USER_ERROR );
	}

	/**
	 * Define qual é simbolo separador de caminhos do sistema, exemplo: '/'
	 */
	define( 'DS', DIRECTORY_SEPARATOR );

	/**
	 * Raiz da instalaçao.
	 */
	define( 'ROOT', dirname( __DIR__ ) );

	/**
	 * Constantes do caminho do sistema que utiliza a framework.
	 */
	if( !defined( 'SYSDIR' ) ) {
		define( 'SYSDIR', 'apps' );
	}

	if ( !defined( 'SYSROOT') )
		define( 'SYSROOT', ROOT . DS . SYSDIR . DS );

	/**
	 * Constantes de caminho da framework.
	 */
	define( 'FWDIR', 'framework' );
	define( 'FWROOT', ROOT . DS . FWDIR . DS );

	/**
	 * Define as configurações necessárias no servidor.
	 */
	//ini_set( 'include_path', ROOT );
	//if ( ini_get( 'display_erros') == 'off' )
		ini_set( 'display_errors', 'on' );

	//if ( ini_get( 'log_errors') == 'on' )
		ini_set( 'log_errors', 'on' );

	//if ( ini_get( 'log_errors_max_len') > 0 )
		ini_set( 'log_errors_max_len', 0 );

	set_include_path( ROOT );
	error_reporting(E_ALL & ~E_STRICT);
	session_start(0);
	session_write_close();

	/*
	 * Esta linha evita problemas com o favicon que deve estar na raiz do site.
	 */
	if( isset( $_GET['cod'] ) && $_GET['cod'] === 'favicon.ico' ) {
		return;
	}

	/**
	 * Importa as bibliotecas principais.
	 */
	require_once FWROOT . 'Framework.php';

	/**
	 * Registra as funções de autoload.
	 */
	Framework::registerAutoloads();

	/**
	 * Adiciona os arquivos de configurações.
	 */
	require_once SYSROOT . 'config' . DS . 'core.php';
	require_once SYSROOT . 'config' . DS . 'database.php';
	require_once SYSROOT . 'config' . DS . 'errors.php';
	require_once SYSROOT . 'config' . DS . 'routes.php';

	/**
	 * Inicializa a Framework.
	 */
	$Framework = Framework::getInstance();
	$Framework->initialize();