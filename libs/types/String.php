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
 * Esta classe contem várias funções para tratamento de variáveis do tipo string.
 * Toda variável que seja deste tipo, é recomendado que utilize esta classe para
 * suprir a necessidade de tipagem forte no PHP em aplicações orientadas a objetos.
 *
 * @package MaiaFW\Lib\Type
 * @category Types
 * @version 1.0
 */
class String  {
	/**
	 * String da instância.
	 * @var string
	 */
	private $_string = '';

	/**
	 * Codificação utilizada pela string.
	 * @var string
	 */
	private $_encode;

	/**
	 * Inicia uma string convertendo-a para a codificação desejada.
	 *
	 * @param string $string Valor da string
	 * @param string $encode[optional] Codificação desejada. O padrão é UTF-8.
	 * @return void
	 */
	public function __construct( $string, $encode = 'UTF-8' ) {
		$this->_encode = $encode;

		if ( !mb_check_encoding( $string, $this->_encode ) )
			$string = mb_convert_encoding( $this->_string, $this->_encode );

		$this->_string = $string;
	}

	/**
	 * Método mágico para retornar e utilizar a string quando não é necessário um
	 * método relativo ao tipo String.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->_string;
	}

	/**
	 * Troca o valor da String atual.
	 *
	 * @param string $string Novo valor.
	 * @return void
	 */
	public function set( $string ) {
		$this->_string = $string;
	}

	/**
	 * Altera a codificação e realiza a conversão da string atual.
	 *
	 * @param string $encode Nova codificação.
	 * @return void
	 */
	public function setEncode( $encode ) {
		$this->_string = mb_convert_encoding( $this->_string, $encode, $this->_encode );
		$this->_encode = $encode;
	}

	/**
	 * Imprime uma string já traduzida de acordo com o idioma ativo ou desejado.
	 *
	 * @param string $string String a ser traduzida.
	 * @param string $fileTranslation Arquivo de tradução onde se encontra a string
	 * desejada.
	 * @param string $language Codigo da linguagem desejada.
	 */
	public static function write( $string , $fileTranslation, $language = null ) {
		$fileTranslation;
		$language;
		echo $string;
	}

	/**
	 * Adiciona uma string ao final da string atual.
	 *
	 * @param string $string String a ser adicionada.
	 * @return void
	 */
	public function append( $string ) {
		$this->_string .= $string;
	}

	/**
	 * Adiciona uma string no início da string atual.
	 *
	 * @param string $string String a ser adicionada.
	 * @return void
	 */
	public function prepend( $string ) {
		 $this->_string = $string . $this->_string;
	}

	/**
	 * Obtem o tamanho da string.
	 *
	 * @return integer
	 */
	public function length() {
		return mb_strlen( $this->_string, $this->_encode );
	}

	/**
	 * Retorna a string escrita ao contrário, exemplo: "Exemplo" retorna "olpmexE"
	 *
	 * @return void
	 */
	public function reverse() {
		$this->_string = strrev( $this->_string );
		/*if ( $this->length() > 1 )
		{
			$reversed		= '';
			$lengthString	= $this->length();
			$positionString	= $this->length() - 1;

			for( $positionCopy	= 0; $positionCopy < $lengthString; $positionCopy++, $positionString-- )
			{
				$reversed[$positionCopy]	= $this->_string[$positionString];
				$reversed[$positionString]	= $this->_string[$positionCopy];
			}

			$this->_string = $reversed;
		}*/
	}

	/**
	 * Retorna o caracter em uma determinada posição na string.
	 *
	 * @return void
	 */
	public function charAt( $position ) {
		$this->_string[$position];
	}

	/**
	 * Verifica se existe uma um padrão na string atual. Este método aceita
	 * expressões regulares.
	 *
	 * @param string $pattern Texto ou expressão procurada.
	 * @return boolean
	 */
	public function match( $pattern, &$returnMatches = false ) {
		if ( $returnMatches !== false )
			return preg_match( $pattern, $this->_string, $returnMatches );
		return preg_match( $pattern, $this->_string );
		//return mb_ereg_match( $pattern, $this->_string );
	}

	/**
	 * Corta a string em um número determinado de caracteres, podendo iniciar de uma
	 * determinada posição.
	 *
	 * @param integer $limit Número máximo de caracteres.
	 * @param integer $start[optional] Posição inicial de contagem.
	 * @return void
	 */
	public function crop( $limit, $start = 0 ) {
		$this->_string = mb_strcut( $this->_string, $start, $limit, $this->_encode );
	}

	/**
	 * Retorna a primeira posição de uma string dentro da atual.
	 *
	 * @param string $string String desejada.
	 * @param $offset[optional] Posição inicial para busca na string.
	 */
	public function indexOf( $string, $offset = 0 ) {
		return mb_strpos( $this->_string, $string, $offset, $this->_encode );
	}

	/**
	 * Divide a string em um array usando um delimitador como referencia, podendo
	 * limitar o número máximo de caracteres dentro das partes criadas.
	 *
	 * @param string $pattern Delimitador para separação.
	 * @param integer $limit[optional] Limite máximo de caracteres para as partes
	 * separadas.
	 * @return array
	 */
	public function split( $pattern, $limit = -1 ) {
		return mb_split( $pattern, $this->_string, $limit );
	}

	/**
	 * Localiza e substitui todos os trechos do padrão informado por um texto na
	 * string atual.
	 *
	 * @param string $pattern Expressão que se deseja substituir.
	 * @param string $replacement Texto que substituirá a expressão.
	 * @return void
	 */
	public function replace( $pattern, $replacement ) {
		$this->_string = mb_ereg_replace( $pattern, $replacement, $this->_string );
	}

	/**
	 * Retira os espaços em branco do início e do fim da string atual.
	 *
	 * @return void
	 */
	public function trim() {
		$this->_string = trim( $this->_string );
	}

	/**
	 * Esta função retorna a string input preenchida na esquerda, direita ou ambos
	 * os lados até o tamanho especificado. Se o parâmetro opcional 'padString' não
	 * for indicado, input  é preenchido com espaços, se não é preenchido com os
	 * caracteres de 'padString' até o limite.
	 *
	 * @param integer $length Tamanho fixo desejado para a string.
	 * @param string $padString[optional] String que preencherá os espaços que faltam
	 * para atigir o limite, caso não seja informado, a string é completada com
	 * espaço.
	 * @param string $type[optional] Como será preenchido o espaço. Use 'left' para
	 * adicionar os caracteres à esquerda da string, 'right' para adicionar na
	 * direita, e 'both' para que os caracteres complementares sejam postos em torno
	 * da string, deixando-a no centralizada.
	 * @return void
	 */
	public function pad( $length, $padString = false, $type = false ) {
		switch ( $type ) {
			case 'left':
				$type = 'STR_PAD_LEFT';
			break;

			case 'both':
				$type = 'STR_PAD_BOTH';
			break;

			case 'right':
				$type = 'STR_PAD_RIGHT';
			break;
		}
		$this->_string = str_pad ( $this->_string, $length, $padString, $type );
	}

	/**
	 * Remove todas as tags PHP e HTML da string atual.
	 *
	 * @return void
	 */
	public function stripTags( $allowable_tags = false ) {
		$this->_string = strip_tags( $this->_string, $allowable_tags );
	}

	/**
	 * Remove todas as barras de escape da string.
	 *
	 * @return void
	 */
	public function stripSlashes() {
		$this->_string = stripslashes( $this->_string );
	}

	/**
	 * Adiciona barras de escape ao lado dos caracteres especiais como aspas.
	 *
	 * @return void.
	 */
	public function addSlashes() {
		$this->_string = addslashes( $this->_string );
	}

	/**
	 * Altera a string atual, deixando-a mais amigável a humanos, alterando strings
	 * como 'Exemplo-De-Texto' ou 'Exemplo_De_Texto' para 'Exemplo de texto'.
	 *
	 * @return void
	 */
	public function humanize() {
		$this->_string = ucfirst( str_replace( array( '_', '-'), ' ', $this->_string ) );
	}

	/**
	 * Elimina os caracteres especiais da string atual e utiliza um caracter (por
	 * padrão '-') para separar as palavras, e deixa os caracteres todos minúsculos.
	 *
	 * @return void
	 */
	public function normalize( $replace = "-" ) {
        $map = array(
            '/À|à|Á|á|å|Ã|â|Ã|ã/'	=> 'a',
            '/È|è|É|é|ê|ê|ẽ|Ë|ë/'	=> 'e',
            '/Ì|ì|Í|í|Î|î/'			=> 'i',
            '/Ò|ò|Ó|ó|Ô|ô|ø|Õ|õ/'	=> 'o',
            '/Ù|ù|Ú|ú|ů|Û|û|Ü|ü/'	=> 'u',
            '/ç|Ç/'					=> 'c',
            '/ñ|Ñ/'					=> 'n',
            '/ä|æ/'					=> 'ae',
            '/Ö|ö/'					=> 'oe',
            '/Ä|ä/'					=> 'Ae',
            '/Ö/'					=> 'Oe',
            '/ß/'					=> 'ss',
            '/[^\\w\\s]/'			=> ' ',
            '/\\s+/'				=> $replace,
            "/^{$replace}+|{$replace}+$/" => ''
        );
        $this->_string = strtolower( preg_replace( array_keys( $map ), array_values( $map ), $this->_string ) );
	}

	/**
	 * Altera a string atual para o formato CamelCase. Por exemplo, o texto
	 * 'Texto exemplo' ficará desta forma: 'TextoExemplo'.
	 *
	 * @return void
	 */
	public function camelize() {
		$this->_string = str_replace( ' ', '', ucwords( str_replace( array( '_', '-' ), ' ', $this->_string ) ) );
	}

	/**
	 * Altera a string no formato CamelCase para o separado por um determinado caractere.
	 * Por exemplo, o texto 'TextoExemplo' ficará desta forma: 'texto_exemplo'.
	 *
	 * @return void
	 */
	function underscore() {
		$this->_string = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $this->_string));
	}

	/**
	 * Codifica a string atual para HTML, alterando todos os caracteres especiais
	 * para a tabela de códigos dos caracteres.
	 *
	 * @return void
	 */
	public function htmlEncode() {
		$this->_string = htmlentities( $this->_string, ENT_QUOTES, $this->_encode );
	}

	/**
	 * Decodifica a string, removendo os códigos de caracteres especiais para
	 * caracteres comuns.
	 *
	 * @return void
	 */
	public function htmlDecode() {
		$this->_string = html_entity_decode( $this->_string, ENT_QUOTES, $this->_encode );
	}

	/**
	 * Torna todos os caracteres minúsculos.
	 *
	 * @return void
	 */
	public function toLower() {
		$this->_string = mb_strtolower( $this->_string, $this->_encode );
	}

	/**
	 * Torna todos os caracteres maiúsculos.
	 *
	 * @return void
	 */
	public function toUpper() {
		$this->_string = mb_strtoupper( $this->_string, $this->_encode );
	}

	/**
	 * Torna o primeiro caracter da string atual, maiúsculo.
	 *
	 * @return void
	 */
	public function upperFirst() {
		$this->_string = ucfirst( $this->_string );
	}

	/**
	 * Torna o primeiro caracter da string atual, minúsculo.
	 *
	 * @return void
	 */
	public function lowerFisrt() {
		$this->_string = lcfirst( $this->_string );
	}

	/**
	 * Torna o primeiro caracter de cada palavra maiúsculo.
	 *
	 * @return void
	 */
	public function upperWords() {
		$this->_string = ucwords( $this->_string );
	}

	/**
	 * Contabiliza quantas palavras existem na string atual.
	 *
	 * @return integer
	 */
	public function countWords() {
		$aditionalCaracters  = 'ÀàÁáåÃâÃã';
		$aditionalCaracters .= 'ÈèÉéêêẽËë';
		$aditionalCaracters .= 'ÌìÍíÎî';
		$aditionalCaracters .= 'ÒòÓóÔôøÕõ';
		$aditionalCaracters .= 'ÙùÚúůÛûÜü';
		$aditionalCaracters .= 'çÇ';
		$aditionalCaracters .= 'ñÑ';
		return str_word_count( $this->_string, 0, $aditionalCaracters );
	}

	/**
	 * Embaralha aleatóriamente os caracteres da string atual.
	 *
	 * @return void
	 */
	public function shuffle() {
		$this->_string = str_shuffle( $this->_string );
	}

	/**
	 * Criptografa a string atual usando o hash md5.
	 *
	 * @return void
	 */
	public function md5() {
		$this->_string = md5( $this->_string );
	}

	/**
	 * Criptografa a string atual usando uma chave como referência.
	 *
	 * @return void
	 */
	public function crypt( $salt ) {
		$this->_string = crypt( $this->_string, $salt );
	}

	/**
	 * Troca as quebras de linha por '<br />'.
	 *
	 * @return void
	 */
	public function newLineToBreak ( $is_xhtml = true) {
		$this->_string = nl2b( $this->_string, $is_xhtml );
	}
}