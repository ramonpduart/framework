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
 * Classe de abstração do banco de dados. Fornece métodos dinâmicos para manipulação
 * dos dados das tabelas. Toda tabela do banco de dados deve ter uma classe com seu
 * nome, cujo será extendida para esta classe.
 *
 * @package MaiaFW\Model
 * @category Core
 * @version 1.0
 */
abstract class Model implements ModelInterface {
	/**
	 * Guarda o nome da da tabela do banco de dados usada pelo modelo.
	 *
	 * @var string
	 */
	protected $_tableName = null;

	protected $_schema = null;

    protected $isView = false;

	/**
	 * Guarda a tabela do banco de dados usada pelo modelo.
	 *
	 * @var Table
	 */
	private $_table;

	/**
	 * Guarda o nome do campo primário da tabela.
	 *
	 * @var string
	 */
	protected $_primaryKey;

	/**
     * Dados do registro
	 *
     * @var array
     */
    protected $_data = array();

	/**
	* Atributo que informa se a instância atual da classe será inserida ou atualizada.
	*
	* Quando o objeto é criado vazio seu valor é true, indicando que os valores
	* informados serão inseridos. Quando o objeto é criado a partir de valores do
	* banco seu valor é false, indicando que os valores serão atualizados.
	*
	* @var bool
	*/
	private $_insert = false;

	/**
	 * Último resultado de uma consulta executada.
	 *
	 * @var PDOStatement
	 */
	private $_lastResult = false;

	/**
	 * Inicia os atributos lendo a estrutura da tabela e gerando um array que é
	 * guardada no atributo $schema.
	 *
	 * @param mixed $id Valor da chave primária
	 * @return void
	 */
	public function __construct( $id = null, $data = array() ) {
		// Caso ainda não esteja setada uma tabela, dispara um erro.
		if ( $this->_tableName === null ) {
			throw new FwException( 'Foi solicitado a criação de um modelo cujo a
									tabela não foi indicada.' );
		}

		// Inicia uma instancia da tabela utilizada.
		$this->_table		= new Table( $this->_tableName, $this->_schema );

		// Obtem uma chave primária, futuramente isto deverá ser melhorado para ser
		// aceito chaves primárias compostas.
        if ($this->isView === false) {
            $this->_primaryKey	= $this->_table->getPrimaryKeysNames();
            $this->_primaryKey	= $this->_primaryKey[0];
        }

		$this->_data = array();
		$schema = array_keys( $this->_table->getSchema() );
		foreach ( $schema as $field ) {
			$this->_data[$field] = null;
		}

		// Verifica se a instancia criada desta classe deve ser preparada para
		// inserção.
		// Aqui deve ser adicionado futuramente o tratamento pela classe Row
        if ($this->isView === false) {
            if ( $id !== null ) {
                if( count( $data ) == 0 ) {
                    $this->_data = $this->findBy( $this->_primaryKey, $id, array(), 0, 1 );
                    $totalData = count( $this->_data );

                    if( $totalData > 0 ) {
                        $this->_data = $this->_data[0];
                    } else {
                        throw new FwException( 'O registro com o ID informado não existe.' );
                    }
                } else {
                    $this->_data = $data;
                }
            } else {
                $this->_insert = true;
            }
        }
	}

	/**
	 * Trata chamadas de métodos desta classe, possibilitando adicionar alguns
	 * recursos adicionais ou possibilitar uma maior versatilidade ao se trabalhar
	 * com os métodos.
	 *
	 * @param varchar $method Nome do método
	 * @param varchar $params Parâmetros para execução do método
	 * @return mixed
	 */
	public function __call( $method, $params ) {
		// Seta possíveis parametros como nulos para evitar que chamadas de métodos
		// acabem em erros por falta de parametros.
		$params = array_merge( $params, array( null, null, null, null ) );

		// Inicia um objeto String para facilitar no tratamento necessário.
		$method	= new String( $method );
		$fields	= array();

		// Filtra a chamada de método, iniciando pelo mais comum, o findByCampo
		if( $method->match( '/^findBy(.*)/', $fields ) ) {
			// Trata o nome do metodo para igualar ao campo no banco de dados
			$field	= new String( $fields[1] );
			$field->normalize( '_' );

			// Garante que seja passado um array no segundo parametro do método
			// findBy.
			if($params[1] == null) {
				$params[1] = array();
			}

			// Chama o método findBy informando o campo e passando os parametros
			// desejados.
			return $this->findBy( $field, $params[0], $params[1], $params[2]);

		// Verifica se o método desejado é o existsThisCampo
		} elseif ( $method->match( '/existsThis(.*)/' ) ) {
			// Trata o nome do metodo para igualar ao campo no banco de dados
			$field	= new String( $fields[1] );
			$field->normalize( '_' );

			// Chama o método existsThis informando o campo e passando os parametros
			// desejados.
			return $this->existsThis( $field, $params[0] );

		// Caso o método não exista dispara uma exceção.
		} else {
			throw new FwException( 'Método "' . $method . '" inesistente para classes
									de abstração do banco' );
		}
	}

	/**
	 * Trata a atribuição de valores aos campos da tabela, sem a necessidade dos
	 * campos serem um atributo da classe.
	 *
	 * @param varchar $field Nome do campo
	 * @param varchar $value Novo valor para o campo
	 * @return void
	 */
	public function __set( $field, $value ) {
		if ( $this->_table->existsColumn( $field ) ) {
			$this->_data[$field] = $value;
		} elseif ( method_exists( $this, $field ) ) {
			$this->$field = $value;
		} else {
			throw new FwException( 'O campo "' . $field . '" não existe na ' .
									'tabela "' . $this->_tableName . '".' );
		}
	}

	/**
	 * Obtem os valores dos campos da tabela, sem a necessidade de serem atributos de
	 * uma classe.
	 *
	 * @param string $field Nome do campo
	 * @return mixed
	 */
	public function __get( $field ) {
		if( $this->_table->existsColumn( $field ) ) {
			return $this->_data[$field];
		}

		//Caso não retorne algum valor, dispara uma exceção.
		throw new FwException( 'O campo "' . $field . '" não existe na ' .
									'tabela "' . $this->_tableName . '".' );
	}

	public function __sleep() {
		return array( '_tableName', '_schema', '_data', '_primaryKey', '_insert' );
	}

	public function __wakeup() {
		$this->_table = new Table( $this->_tableName, $this->_schema );
	}

	public function isValid( $field, $value ) {
		$columnSchema = $this->_table->getColumn($field);
		$valid = true;

		if( $columnSchema['null'] == false && ( $value == '' || $value == null ) ) {
			$valid = false;
		}

		if( $columnSchema['type'] == 'varchar' && mb_strlen( $value ) > $columnSchema['length'] ) {
			$valid = false;
		}

		return $valid;
	}

	public function getColumns() {
		return array_keys( $this->_table->getSchema() );
	}

	public function getSchema() {
		return $this->_schema;
	}

	/**
	 * Método que insere/atualiza o registro. Caso a instância esteja com valores do
	 * banco de dados será chamado o método update(), que atualiza o resgistro. Caso
	 * contrário será chamado o método insert() que insere um novo registro com os
	 * valores dos atributos (campos).
	 *
	 * @return boolean
	 */
	public function save() {
        if ($this->isView === true) {
            throw new FwException('Tentativa de salvar dados em uma view.');
        }
		if( $this->_insert === true ) {
			return $this->insert();
		} elseif( $this->_insert === false ) {
			return $this->update();
		}
		return false;
	}

	/**
	 * Método que realiza a inserção dos atributos (campos) na tabela no banco de
	 * dados caso o objeto NÃO tenha sido instanciado com uma primary key válida.
	 *
	 * @return integer
	 */
	private function insert() {
		if( !$this->_insert ) {
			throw new FwException( 'Método insert() utilizado com instância de registro existente.' );
		}

		$this->beforeInsert();

		//Insere os campos verificando se s√£o identicos a null, pois caso sejam √© colocado valor nulo
		$sql_insere_campos = 'INSERT INTO ' . $this->_table->getTableName() . ' (`';

		$ncampo	= count( $this->_table->getSchema() );
		$columns	= $this->getColumns();
		$sql_insere_campos .= join('`, `', $columns);

		$sql_insere_campos .= '`) VALUES (';

		$count = 0;
		reset( $columns );
		foreach( $columns as $column ) {
			$sql_insere_campos .= ($this->_data[$column] === null || $this->_data[$column] == '') ? 'NULL' : '\'' . addslashes($this->$column) . '\'';
			$count++;
			if($count < $ncampo) {
				$sql_insere_campos .= ', ';
			}
		}
		$sql_insere_campos .= ')';
		$this->execute( $sql_insere_campos );
		$this->_insert = false;
		$id = $this->_primaryKey;
		$this->$id = $this->getInsertId();

		$retorno = $this->getInsertId();

		$this->afterInsert();

		if( !is_int($retorno) ) {
			return true;
		}
		return $retorno;
	}

	/**
	 * Método que realiza a atualização dos atributos (campos) na tabela
	 * plugacao_contatos no banco de dados caso o objeto tenha sido
	 * instanciado com uma primary key válida.
	 *
	 * True caso consiga realizar a operação com sucesso, mesmo que nenhuma linha no
	 * banco seja afetada: se os valores não foram alterados.
	 *
	 * @return boolean
	 */
	private function update() {
		if( $this->_insert ) {
			throw new FwException( 'Método update() utilizado com instância vazia.' );
		}

		$this->beforeUpdate();

		// Atualiza os campos verificando se são identicos a null, pois caso sejam
		// é colocado valor nulo
		$sql_insere_campos = 'UPDATE ' . $this->_table->getTableName() . ' SET ';

		$ncampo	= count( $this->_table->getSchema() );
		$campos	= $this->getColumns();
		$count	= 0;
		foreach( $campos as $campo ) {
			$sql_insere_campos .= '`' . $campo . '` = ';
			$sql_insere_campos .= ($this->$campo === null || $this->$campo == '') ? "NULL" : "'".addslashes($this->$campo)."'";
			$count++;
			if( $count < $ncampo ) {
				$sql_insere_campos .= ", ";
			}
		}

		$pk = $this->_primaryKey;
		$sql_insere_campos .= ' WHERE `' . $pk . '` = "' . $this->$pk . '"';

		if( $this->execute( $sql_insere_campos ) ) {
			$this->afterUpdate();
			return true;
		}
	}

	/**
	 * Exclui o o dado do objeto.
	 *
	 * @return void
	 */
	public function delete() {
        if ($this->isView === true) {
            throw new FwException('Tentativa de excluir dados de uma view.');
        }
		if( $this->_insert === true ) {
			throw new FwException( 'Tentativa de excluir uma instância ainda não salva.' );
		}

		$this->beforeDelete();

		$sql_delete  = 'DELETE FROM ' . $this->_table->getTableName();
		$sql_delete .= ' WHERE ' . $this->_primaryKey;
		$sql_delete .= ' = \'' . $this->_data[$this->_primaryKey] . '\'';


		if( $this->execute( $sql_delete ) ) {
			$this->afterDelete();
			return true;
		}
	}

	protected function beforeInsert() {
		return true;
	}

	protected function beforeUpdate() {
		return true;
	}

	protected function beforeDelete() {
		return true;
	}

	protected function afterInsert() {
		return true;
	}

	protected function afterUpdate() {
		return true;
	}

	protected function afterDelete() {
		return true;
	}

	/**
	 * Realiza buscas na tabela.
	 *
	 * @param array $conditions Condições para executar a busca.
	 * @param string $order Forma de ordenação desejada
	 * @param integer $start Número do registro inicial para retorno
	 * @param integer $limit Limite de registros retornados
	 * @return array
	 */
	public function find( $conditions = array(), $order = null, $start = null, $limit = null ) {
		$where 		= $conditions	? ' WHERE ' . implode( ' AND ', $conditions): '';
		$orderby 	= $order 		? ' ORDER BY ' . $order						: '';
		if( $start !== null ) {
			$start 	= ' LIMIT ' . $start;
			$limit 		= $limit ? ', ' . $limit : '';
		}
		$sql 		= "SELECT * FROM {$this->_table->getTableName()} {$where} {$orderby} {$start} {$limit}";
		$find		= $this->fetchResult($sql);
		return $find;
	}

	/**
	 * Realiza uma busca na tabela através de um atributo específico.
	 * Pode ser chamado por 'findByCampo(valor)'.
	 *
	 * @param string $field Nome do campo
	 * @param string $value[optional] Valor desejado
	 * @param array $conditions Condições adicionais
	 * @param <type> $order Forma de ordenação desejada
	 * @return mixed
	 */
	public function findBy( $field, $value, array $conditions = array(), $order = null, $start = null, $limit = null ) {
		$conditions = array_merge( array( "{$field} = '{$value}'" ), $conditions );
		return $this->find( $conditions, $order, $start, $limit );
	}

	/**
	 * Realiza buscas na tabela e retona o total de registos.
	 *
	 * @param array $conditions Condições para executar a busca.
	 * @return array
	 */
	public function getTotal( $conditions = array() ) {
        if ($this->isView === true) {
            $field = "*";
        } else {
            $field = "`" . $this->_primaryKey . "`";
        }
		$where 		= $conditions	? ' WHERE ' . implode( ' AND ', $conditions): '';
		$sql 		= "SELECT COUNT(" . $field . ") as 'total'"
            . " FROM {$this->_table->getTableName()} {$where} LIMIT 0, 1";
		$find		= $this->fetchResult($sql);
		return $find[0]['total'];
	}

	/**
	 * Verifica se um registro existe a partir de um determinado campo e valor.
	 * Pode ser chamado por 'existsThisCampo(valor)'.
	 *
	 * @param varchar $field Nome do campo
	 * @param varchar $value Valor de comparação
	 * @return boolean
	 */
	public function existsThis( $field, $value ) {
		$row	= $this->findBy($field, $value);
		if( !empty( $row ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Executa um SQL
	 *
	 * @param string $query Código SQL a ser executado.
	 * @return object
	 */
	public function execute( $query, $data = array() ) {
		// Obtem a instancia do Banco de dados do ambiente atual.
		$Database			= Environment::getDatabase();
		$Connection			= $Database->getConnection();

		// Inicia a captura de possíveis exceções.
		try {
			// Obtem e armazena o resultado obtido.
            if (count($data) > 0) {
                $statement = $Connection->prepare($query);
                $start = round( microtime(true), 4 );
                $statement->execute($data);
                $end = round( microtime(true), 4 );
                $this->_lastResult = $statement;
                FwException::setSqlLog( $statement->queryString, $end - $start );
            } else {
                $start = round( microtime(true), 4 );
                $result = $Connection->query( $query );
                $end = round( microtime(true), 4 );
                $this->_lastResult = $result;
                FwException::setSqlLog( $result->queryString, $end - $start );
            }

			$error = $Connection->errorInfo();
			if( $error[0] !== '00000' ) {
				throw new FwException('Ocorreu um erro ao executar a query: ' .
									   $query . ' - Erro: ' . $error[2] );
			}

		// Caso algum erro tenha ocorrido, dispara uma exceção.
		} catch ( PDOException $e ) {
			throw new FwException('Ocorreu um erro ao executar a query: ' .
								   $query . ' - Erro: ' . $e->getMessage() );
		}
		return $this->_lastResult;
	}

	/**
	 * Retorna um array com todos os resultados de uma busca.
	 *
	 * @param string $query Código SQL a ser executado.
	 * @return array
	 */
	public function fetchResult( $query, $data = array() ) {
		// Executa a query e verifica o retorno
		$query = $this->execute( $query, $data );

		// Retorna um array com o resultado.
		return $query->fetchAll();
	}

	/**
	 * Retorna o número de registros afetados pela última ação no banco de dados.
	 *
	 * @return integer
	 */
	public function getAffectedRows() {
		if ( $this->_lastResult === false ) {
			return false;
		}
		return $this->_lastResult->rowCount();
	}

	/**
	 * Retorna o valor da primary key do último registro inserido na tabela do
	 * modelo.
	 *
	 * @return integer
	 */
	public function getInsertId() {
		// Obtem a instancia do Banco de dados do ambiente atual.
		$Database			= Environment::getDatabase();
		$Connection			= $Database->getConnection();

		return $Connection->lastInsertId();
	}
}