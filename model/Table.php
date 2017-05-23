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
 * Esta classe abstrai a estrutura tabela do banco de dados, fornecendo alguns
 * métodos uteis para a utilização das informações extraidas do banco de dados.
 *
 * @package MaiaFW\Model
 * @category Database
 * @version 1.0
 */
class Table  {
	protected $_tableName;

	/**
	 * Guarda um array com a estrutura da tabela do banco de dados.
	 *
	 * @var array
	 */
	protected $_schema = array();

	/**
	 * Guarda o nome do campo primário da tabela.
	 *
	 * @var array
	 */
	protected $_primaryKeys = array();

	/**
	 * Guarda informações de chaves extrangeiras da tabela.
	 *
	 * @var array
	 */
	protected $_foreingKeys = array();

	/**
	 * Inicializa o objeto extraindo informações de uma tabela desejada do banco de
	 * dados.
	 *
	 * @param string $name Nome da tabela do banco de dados.
	 */
	public function __construct( $name, $schema = null ) {
		// Obtem a instancia do Banco de dados do ambiente atual.
		$Database			= Environment::getDatabase();
		$Connection			= $Database->getConnection();

		// Inicializa os atributos.
		$this->_tableName	= $Database->getPrefix() . $name;

		// Obtem as estrutura da tabela do banco de dados.
		if( $schema ) {
			$tableSchema = $schema;
		} else {
			$tableSchemaExec	= $Connection->query( 'DESCRIBE `' . $this->_tableName . '`' );

			$error = $Connection->errorInfo();
			if( $error[0] !== '00000' ) {
				throw new FwException('Ocorreu um erro ao executar a query: ' .
									   'DESCRIBE `' . $this->_tableName . '` - Erro: ' . $error[2] );
			}
			$tableSchema		= $tableSchemaExec->fetchAll();
			$type				= null;
		}

		// Armazena a estrutura da tabela.
		foreach( $tableSchema as $field ) {
			preg_match( '/([a-z]*)\(?([0-9]*)?\)?/', $field["Type"], $type);
			$this->_schema[ $field['Field'] ] = array(
											'type'		=> $type[1],
											'length'	=> $type[2],
											'null'		=> $field['Null'] == 'YES' ? true : false,
											'default'	=> $field['Default'],
											'key'		=> $field['Key'],
											'extra'		=> $field['Extra']
										);
			if($field['Key'] == 'PRI') {
				$this->_primaryKeys[] = $field['Field'];
			}
		}
	}

	/**
	 * Obtem o nome da tabela do banco de dados
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->_tableName;
	}

	/**
	 * Retorna um array com informações da estrutura da tabela do banco de dados
	 * - Field { type, length, null, default, key, extra }
	 *
	 * @return array
	 */
	public function getSchema() {
		return $this->_schema;
	}

	/**
	 * Retorna um array com informações da estrutura de um campo específico de uma
	 * tabela do banco de dados
	 * - Field { type, length, null, default, key, extra }
	 *
	 * @param string $columnName Nome da coluna
	 * @return array
	 */
	public function getColumn( $columnName ) {
		return $this->_schema[ $columnName ];
	}

	/**
	 * Obtem os nomes das chaves primárias da tabela do banco de dados.
	 *
	 * @return array
	 */
	public function getPrimaryKeysNames() {
		return $this->_primaryKeys;
	}

	/**
	 * Verifica a existência de um campo na tabela do banco de dados.
	 *
	 * @param string $columnName Nome da coluna
	 * @return boolean
	 */
	public function existsColumn( $columnName ) {
		return array_key_exists( $columnName, $this->_schema );
	}
}