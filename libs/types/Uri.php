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
 * Utilize para validar, extrair informações, adicionar ou montar URLs.
 *
 * @package MaiaFW\Lib\Type
 * @category Types
 * @version 1.0
 */
class Uri  {
	/**
	 * URI scheme da URL.
	 * @var string
	 */
	private $_scheme;

	/**
	 * Host da URL.
	 * @todo Validar usando a listagem de domínios encontrados em
	 * ftp://data.iana.org/TLD/tlds-alpha-by-domain.txt ou em
	 * http://www.ixus.net/net/tldext.php
	 * @var string
	 */
	private $_host;

	/**
	 * Porta de acesso da URL.
	 * @var integer
	 */
	private $_port;

	/**
	 * Usuário para acesso.
	 * @var string
	 */
	private $_user;

	/**
	 * Senha para acesso.
	 * @var string
	 */
	private $_password;

	/**
	 * Caminho de acesso de um arquivo.
	 * @var string
	 */
	private $_path;

	/**
	 * Parâmetros passados pela URL.
	 * @var array
	 */
	private $_query = array();

	/**
	 * Obtem as referências da URL.
	 * @var string
	 */
    private $_fragment;

    /**
     * Inicializa o objeto URI. Se for informado uma URL, realiza o parser extraindo
     * as informações e preenchendo os atributos.
     *
     * @param $url[optional]
     * @return void
     */
	public function __construct( $url = false ) {
		if( $url ) {
			/**
			 * Dispara uma exceção caso a URL seja inválida, ou extrai as informações
			 * armazenando as em no array $parser.
			 */
			$parser = parse_url( $url );
			if ( $parser === false )
				throw new FwException( 'A URL informada é inválida.' );

			/**
			 * Preenche os atributos com as informações extraídas.
			 */
			$this->_scheme		= isset( $parser['scheme'] )	? $parser['scheme'] 	: null;
			$this->_host		= isset( $parser['host'] )		? $parser['host'] 		: null;
			$this->_port		= isset( $parser['port'] )		? $parser['port'] 		: null;
			$this->_user		= isset( $parser['user'] )		? $parser['user'] 		: null;
			$this->_password	= isset( $parser['pass'] )		? $parser['pass'] 		: null;
			$this->_path		= isset( $parser['path'] )		? $parser['path'] 		: null;
			$this->_fragment	= isset( $parser['fragment'] )	? $parser['fragment']	: null;

			if( isset( $parser['query'] ) )
				$this->_parseQuery( $parser['query'] );
		}
	}

	/**
	 * Altera um valor em um atributo.
	 *
	 * @param string $attribute Nome do atributo.
	 * @param mixed $value Novo valor para o atributo.
	 * @return void
	 */
	public function __set( $attribute, $value ) {
		switch ($attribute) {
			case 'scheme':
				if ( $value === false || preg_match('/^([a-zA-Z0-9])+$/', $value ) )
					$this->_scheme = $value;
				else
					throw new FwException( 'A Uri scheme informada é inválida.' );
			break;

			case 'host':
				if ( $value === false || preg_match('/^([a-z0-9-.]*)(\.([a-z]{2,3}))?$/', $value ) )
					$this->_host = $value;
				else
					throw new FwException( 'O host informado é inválido.' );
			break;

			case 'port':
				if( $value === false || preg_match('/^[0-9]+$/', $value )  )
					$this->_port = (int) $value;
				else
					throw new FwException( 'A porta informada é inválida.' );
			break;

			case 'user':
				if ( $value === false || preg_match('/^[a-z0-9_.-]+$/', $value ) )
					$this->_user = $value;
				else
					throw new FwException( 'O usuário informado é inválido.' );
			break;

			case 'password':
				if ( $value === false || preg_match('/^[a-z0-9+!*(),;?&=\$_.-]+$/', $value ) )
					$this->_password = $value;
				else
					throw new FwException( 'A senha informada é inválida.' );
			break;

			case 'path':
				if ( $value === false || preg_match('/^\/([a-zA-Z0-9_-]+\/?)+(\.[a-zA-Z0-9]+)?$/', $value ) )
					$this->_path = $value;
				else
					throw new FwException( 'O caminho informado é inválido.' );
			break;

			case 'query':
				throw new FwException( 'Utilize os métodos corretos para alterar a Uri Query.' );
			break;

			case 'fragment':
				if ( $value === false || preg_match('/^[a-z_.-][a-z0-9+\$_.-]+$/', $value ) )
					$this->_fragment = $value;
				else
					throw new FwException( 'O "fragment" informado é inválido.' );
			break;

			default:
				;
			break;
		}
	}

	/**
	 * Obtem o valor de um atributo.
	 *
	 * @param string $attribute Nome do atributo.
	 * @return mixed Valor do atributo
	 */
	public function __get( $attribute ) {
		switch ($attribute) {
			case 'scheme':
				return $this->_scheme;
			break;
			case 'host':
				return $this->_host;
			break;
			case 'port':
				return $this->_port;
			break;
			case 'user':
				return $this->_user;
			break;
			case 'password':
				return $this->_password;
			break;
			case 'path':
				return $this->_path;
			break;
			case 'query':
				$this->_query = $this->_getStringQuery();
			break;
			case 'fragment':
				return $this->_fragment;
			break;

			default:
				throw new FwException( 'O atributo "' . $attribute . '" informado é inválido ou privado em um objeto da classe Uri.' );
			break;
		}
		return false;
	}

	/**
	 * Retorna a URL montada caso haja uma tentativa te utilização de um objeto URI
	 * como string.
	 *
	 * @return string URL formada sem formatação HTML.
	 */
	public function __toString() {
		return $this->getUrl();
	}

	/**
	 * Obtem a URL formada, podendo estar codificada para inclusão em um HTML ou não.
	 *
	 * @param boolean $html Codificar para uso em HTML.
	 * @return string URL formada.
	 */
	public function getUrl( $html = false ) {
		$uri	 = '';
		$uri	.= $this->_scheme	? $this->_scheme . '://'				: 'http://';

		// Verifica se usuario e senha de acesso foram passados para serem
		// adicionados na URL.
		if( $this->_user && $this->_password )
			$uri.= $this->_user . ':' . $this->_password . '@';

		// Adiciona o Host
		$uri	.= $this->_host;

		// Verifica se a porta está setada ou se não é a porta padrão (80)
		if( $this->_port && $this->_port != '80' )
			$uri	.= 	':' . $this->_port;

		// Completa a URL com as outras informações.
		$uri	.= $this->_path		? $this->_path							: '/';
		$uri	.= $this->_query	? '?' . $this->_getStringQuery( $html )	: '';
		$uri	.= $this->_fragment	? '#' . $this->_fragment				: '';

		return $uri;
	}

	/**
	 * Realiza a extração de parâmetros de uma string e armazena no atributo _query.
	 *
	 * @param string $query String com parâmetros da URL para extração.
	 * @return void
	 */
	private function _parseQuery( $query ) {
		parse_str( $query, $this->_query );
	}

	/**
	 * Obtem a string de parâmetros da URL, podendo estar codificada para inclusão
	 * em um HTML ou não.
	 *
	 * @param boolean $html Codificar para uso em HTML.
	 * @return string URL formada.
	 */
	private function _getStringQuery( $html = false ) {
		if ( $html != false )
			return  urldecode( http_build_query( $this->_query, '', '&amp;' ) );

		return  urldecode( http_build_query( $this->_query ) );
	}

	/**
	 * Adiciona um novo parâmetro e seu respectivo valor à URL. Cuidado, pois se a
	 * o parâmetro já existir, seu valor será substituido.
	 *
	 * @param string $variable Nome do parâmetro.
	 * @param string $value Valor para o parâmetro.
	 * @return void
	 */
	public function addQueryVar( $variable, $value) {
		$this->_query[$variable]	= $value;
	}

	/**
	 * Adiciona um array com parâmetros e seu respectivo valor à URL. Cuidado, pois
	 * se algum dos parâmetros já existir, seu valor será substituido.
	 *
	 * @param array $array Nome do parâmetro.
	 * @return void
	 */
	public function addQueryArray( array $array ) {
		foreach( $array as $variable => $value )
			$this->_query[$variable]	= $value;
	}

	/**
	 * Remove um novo parâmetro dà URL.
	 *
	 * @param string $variable Nome do parâmetro.
	 * @return void
	 */
	public function removeQueryVar( $variable ) {
		unset( $this->_query[$variable] );
	}

	/**
	 * Obtem o valor de um parâmetro e seu respectivo valor à URL. Retorna false caso
	 * o parâmetro não exista ou não esteja setado.
	 *
	 * @param string $variable Nome do parâmetro.
	 * @param string $value Valor para o parâmetro.
	 * @return mixed
	 */
	public function getQueryVar( $variable ) {
		if( isset( $this->_query[$variable] ) )
			return $this->_query[$variable];
		return false;
	}

}