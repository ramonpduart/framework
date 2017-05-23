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
 * Classe base para Controllers que lidam com CRUD.
 *
 * Provê métodos preparados para a criação de CRUDs de uma determinada Model de forma
 * simples e rápida, com Actions comuns para listagem, cadastro, edição e exclusão.
 *
 * Tambem possui métodos intermediários que possibilitam a inclusão de códigos a
 * serem executados que complementam a lógica padrão e facilitam a adquação para as
 * necessidades da aplicação.
 *
 * Para criar um Controller de aplicação para CRUD, basta criar uma classe com o
 * sufixo "Controller" e extender para esta classe, não sendo necessário extender
 * para a classe Controller base, pois esta já extende. É necessário indicar alguns
 * parâmetros básicos, como o nome da model e campo de ordenação, mas após isto, caso
 * seja um CRUD simples, não será necessário criar nenhum método adicional, basta
 * criar a views "index.frm", "form.frm" e "view.frm" para ter um CRUD completo.
 *
 * <code>
 * class ExampleController extends CrudController {
 *     // Nome da model utilizada no CRUD
 *     protected $modelName = 'NomeDaModel';
 *
 *     // Título que será adicionado em todas as telas
 *     protected $viewTitle = 'Um título para as telas';
 *
 *     // Campo em que os registros serão ordenados na listagem
 *     protected $sortField = 'campo_para_ordenacao';
 *
 *     // Tipo de ordenação
 *     protected $sortOrder = 'ASC';
 *
 *     // Nome do layout que será utilizado
 *     protected $layoutName = 'default';
 *
 *     // Módulo ao qual o Controller pertence
 *     public $module = null;
 * }
 * </code>
 *
 * Para a criação das Views é fornecido algumas variáveis com dados importantes, como
 * na listagem que possui a lista de registros recuperados que podem ser exibidos,
 * váriaveis de paginação, filtros que foram aplicados e como os dados foram
 * ordenados. Para acessar as variáveis na view utilize dentro do arquivo .frm criado
 * o seguinte <code>$this->variavel</code>.
 *
 * Veja a documentação dos métodos "index", "edit", "add" e "view" para saber mais.
 *
 * @package MaiaFW\Controller
 * @category Core
 * @version 1.0
 */
abstract class CrudController extends Controller {
	/**
	 * Nome da model utilizada para o CRUD.
	 *
	 * Sobreescreva ao implementar para definí-la.
	 *
	 * @var string
	 */
	protected $modelName = null;

	/**
	 * Título padrão para as views.
	 *
	 * Sobreescreva ao implementar para definí-lo.
	 *
	 * @var string
	 */
	protected $viewTitle = null;

	/**
	 * Número máximo de registros para exibir na listagem.
	 *
	 * Caso este atributo não seja sobreescrito, irá assumir o limite de 15 registros
	 * por página na listagem.
	 *
	 * @var int
	 */
	protected $limit = 15;

	/**
	 * Campo padrão para ordenação.
	 *
	 * Caso este atributo não seja sobreescrito, irá assumir que o campo de ordenação
	 * é "id".
	 *
	 * @var string
	 */
	protected $sortField = 'id';

	/**
	 * Tipo de ordenação. Crescente ou decrescente.
	 *
	 * Os valores podem ser "ASC" para crescente ou "DESC" para decrescente.
	 *
	 * Caso este atributo não seja sobreescrito, irá assumir que o tipo de ordenação
	 * é crescente ("ASC").
	 *
	 * @var string
	 */
	protected $sortOrder = 'ASC';

	/**
	 * Campos inclusos na busca
	 *
	 * Define os campos que serão usados para realizar a busca de registros.
	 *
	 * @var array
	 */
	protected $searchFields = array();

	/**
	 * Layout padrão para o módulo.
	 *
	 * Caso este atributo não seja sobreescrito, irá assumir que o nome do layout é
	 * "default".
	 *
	 * @var string
	 */
	protected $layoutName = 'default';

	/**
	 * Mensagens de retorno para o usuário.
	 *
	 * Esse atributo pode ser sobreescríto para a adição de novas mensagens ou
	 * alteração de uma mensagem pardrão, assim como recorrer ao método intermediário
	 * disponibilizado na classe base do Controller, beforeFilter().
	 *
	 * <code>
	 * protected function beforeFilter() {
	 *     $this->message['newMessage'] = "Texto da mensagem";
	 *     $this->message['error'] = "Substituindo o texto para mensagens de erro";
	 * }
	 * </code>
	 *
	 * As mensagens pré-definídas são:
	 *  - error: 'An error occurred while trying to add the record.'
	 *  - created: 'Record has been added.'
	 *  - updated: 'Record has been updated.'
	 *  - deleted: 'Record has been deleted.'
	 *  - allDeleted: 'All records has been deleted.'
	 *  - noRecordsSelected: 'No records selected'
	 *
	 * @var array
	 */
	protected $message = array (
		'error' => 'An error occurred while trying to add the record.',
		'created' => 'Record has been added.',
		'updated' => 'Record has been updated.',
		'deleted' => 'Record has been deleted.',
		'allDeleted' => 'All records has been deleted.',
		'noRecordsSelected' => 'No records selected'
	);

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
	 * No caso do construtor para controller de CRUDs, serão iniciados as seguintes
	 * variáveis para a View, que podem ser acessada usando
	 * <code>$this->variavel</code> dentro da View:
	 *  - record: Fornece o registro que está sendo trabalhado. Aqui o valor é
	 * definido como null.
	 *  - title: Título definido pelo atributo "viewTitle".
	 *  - isAjaxRequest: Indica se a requisição foi originada de uma chamada ajax ou
	 * não.
	 *  - message: Mensagem de retorno para o usuário.
	 *
	 * @param string $language Linguagem utilizada na requisição
	 * @return void
	 */
	public function __construct($language = null) {
		parent::__construct($language);
		$this->view->layout->layoutName = $this->layoutName;

		$this->view->record			= null;
		$this->view->title			= $this->viewTitle;
		$this->view->layout->title	= $this->viewTitle;
		$this->view->isAjaxRequest	= $this->isAjaxRequest();

		$this->view->message = Session::getVar('flash-message');
		$this->view->error = Session::getVar('flash-error');
		Session::deleteVar('flash-message');
		Session::deleteVar('flash-error');
	}

	/**
	 * Action de listagem de registro.
	 *
	 * Nesta Action é recuperado os registros de acordo com os atributos definidos e
	 * parâmetros passados na requisição.
	 *
	 * Variáveis são criadas na View que podem ser acessadas usando
	 * <code>$this->variavel</code> dentro da view. Isso possibilita que seja feita
	 * a listagem dos registros, sendo elas:
	 *  - title: Título definido pelo atributo "viewTitle".
	 *  - isAjaxRequest: Indica se a requisição foi originada de uma chamada ajax ou
	 * não.
	 *  - message: Mensagem de retorno para o usuário.
	 *  - paginationPage: Página atual da páginação.
	 *  - paginationLimit: Número máximo de registros retornados.
	 *  - paginationSortField: Campo usado para a ordenação.
	 *  - paginationSortOrder: Tipo da ordenação usada, se é crescente ou decrescente.
	 *  - paginationList: Lista de registros recuperados.
	 *  - paginationTotal: Total de registros gravados no banco.
	 *
	 * Por padrão, a View utilizada será a "index.frm.php", mas este método pode ser
	 * sobreescrito para a definição de outra view ou mesmo a adição de lógicas
	 * complementáres.
	 *
	 * <code>
	 * public function index(
	 *     $page = 1,
	 *     $sortField = false,
	 *     $sortOrder = false,
	 *     $limit = null,
	 *     $filter = null
	 * ) {
	 *     parent::index( \$page, \$sortField, \$sortOrder, \$limit, \$filter );
	 *
	 *     // Evita que o layout seja renderizado junto com o código da view.
	 *     $this->view->autoLayout = false;
	 *
	 *     // Altera o nome da View que será utilizada.
	 *     $this->view->name = 'outraview';
	 * }
	 * </code>
	 *
	 * @param int $page Página atual da listagem
	 * @param string $sortField Campo para ordenação dos registros
	 * @param string $sortOrder Maneira de ordenação ASC ou DESC
	 * @param int $limit Número máximo de registros na listagem
	 * @param string|mixed[] $filter Valor para filtagem dos registros
	 * @return void
	 */
	public function index(
		$page = 1,
		$sortField = false,
		$sortOrder = false,
		$limit = null,
		$filter = null
	) {
		if( $limit === null ) {
			$limit = $this->limit;
		}

		if( $sortField === false ) {
			$sortField = $this->sortField;
		}

		if( $sortOrder === false ) {
			$sortOrder = $this->sortOrder;
		}

		$this->view->gridFilters = $filter;

		if ( is_array( $filter ) === false ) {
			if ( $filter == null ) {
				$filter = array();
			} else {
				$newFilters = array();
				$object = $this->getRequestObject();
				if( count( $this->searchFields) > 0 ) {
					$columns = $this->searchFields;
				} else {
					$columns = $object->getColumns();
				}
				foreach ( $columns as $column ) {
					if( $object->$column != 'id' && $object->isValid($column, $filter) ) {
						$newFilters[] = $column . ' LIKE "%' . $filter . '%"';
					}
				}
				$filter = array( implode(' OR ', $newFilters) );
			}
		}

		$startPagination = $page > 1 ? ( $page - 1 ) * $limit : '0';

		// Define as variáveis necessárias para a listagem
		$this->view->paginationPage 		= $page;
		$this->view->paginationLimit 		= $limit;
		$this->view->paginationSortField	= $sortField;
		$this->view->paginationSortOrder	= $sortOrder;
		$this->view->paginationList			= $this->getRecords(
												$filter,
												$sortField . ' ' . $sortOrder,
												$startPagination,
												$limit
											);
		$this->view->paginationTotal		= $this->getTotalRecords( $filter );
	}

	/**
	 * Realiza uma pesquisa na listagem de registros.
	 *
	 * Recebe os dados de pesquisa por parâmetro de requisição como POST, e realiza a
	 * pesquisa redirecionando para a View "index".
	 *
	 * Por padrão recebe a váriavel <code>$query</code> como string para filtragem.
	 *
	 * @param string $query String de pesquisa
	 * @return void
	 */
	public function search() {
		$params = array(
			'page'		=> 1,
			'sortField'	=> $this->sortField,
			'sortOrder'	=> $this->sortOrder,
			'limit'		=> $this->limit,
			'filter'	=> $this->getHttpData('query')
		);

		if( $this->module === false ) {
			$this->redirect( $this->getControllerName(), 'index', $params );
		} else {
			$this->redirectToModule(
				$this->module,
				$this->getControllerName(),
				'index',
				$params
			);
		}
	}

	/**
	 * Vizualização de registros
	 *
	 * Action para visualização de registros. Este método passa para a View a
	 * variável "record" que pode ser acessada usando <code>$this->record</code>
	 * em qualquer parte da View. A variável "record" é instanciada com base no id
	 * informado.
	 *
	 * @param int $id Id do registro
	 * @return void
	 */
	public function view( $id ) {
		if( is_numeric($id) === false ) {
			throw new FwException(
				'O valor "' . $id . '" informado não é válido para a Action "view"
				do controller "' . $this->getControllerName() . '" cujo utiliza a
				Model "' . $this->modelName . '".'
			);
		}

		if( $this->isAjaxRequest() ) {
			$this->view->autoLayout		= false;
		}
		$Model = new $this->modelName( (int) $id);

		$this->view->record			= $Model;
	}

	/**
	 * Action para adição de um registro.
	 *
	 * Este Action passa para a View a variável "record" que pode ser acessada usando
	 * <code>$this->record</code> em qualquer parte da View. A variável "record"
	 * possui um valor nulo, pois não tem a necessidade de instanciar nenhum objeto
	 * para a adição.
	 *
	 * @return void
	 */
	public function add() {
		if( $this->isAjaxRequest() ) {
			$this->view->autoLayout		= false;
		}
		$this->view->name			= 'form';
	}

	/**
	 * Insere um novo registro.
	 *
	 * Esta Action insere no banco de dados o novo registro originado de um
	 * formulário.
	 *
	 * Os métodos <code>beforeCreate()</code> e <code>afterCreate()</code>,
	 * <code>afterCreateSave()</code> e <code>afterSave()</code> podem ser
	 * utilizados para executar lógicas antes e depois da inserção do registro.
	 *
	 * O novo registro será inserido capturando os campos nomeados no padrão
	 * {tabela}_{campo}, como por exemplo user_id ou user_password, sendo "user" o
	 * nome da tabela. Após a captura irá popular os respectivos atirbutos da Model
	 * referentes aos campos identificados.
	 *
	 * Ao final da inserção, será redirecionado para a Action "index".
	 *
	 * @return void
	 */
	public function create() {
		try {
			$object = $this->getRequestObject();

			$this->beforeCreate( $object );
			$this->setPostValuesToModel( $object );
			$this->afterCreate( $object );
			$object->save();
			$this->afterCreateSave( $object );
			$this->afterSave( $object );

			$this->getAjaxReturn( true, 'created' );
			Session::setVar('flash-message', $this->message['created']);
		} catch ( FwException $e ) {
			$this->getAjaxReturn( false, 'error' );
			Session::setVar('flash-message', $this->message['error']);
		}
		if( $this->module === false ) {
			$this->redirect( $this->getControllerName(), 'index' );
		} else {
			$this->redirectToModule(
				$this->module,
				$this->getControllerName(),
				'index'
			);
		}
	}

	/**
	 * Action para edição de um registro
	 *
	 * Esta Action passa para a view a variável "record" que pode ser acessada usando
	 * <code>$this->record</code> em qualquer parte da View. A variável "record" é
	 * instanciada com base no id informado.
	 *
	 * @param int $id Id do registro
	 * @return void
	 */
	public function edit( $id ) {
		if( is_numeric($id) === false ) {
			throw new FwException(
				'O valor "' . $id . '" informado não é válido para a Action "edit"
				do controller "' . $this->getControllerName() . '" cujo utiliza a
				Model "' . $this->modelName . '".'
			);
		}

		if( $this->isAjaxRequest() ) {
			$this->view->autoLayout		= false;
		}
		$Model = new $this->modelName($id);
		$this->view->record			= $Model;

		$this->view->name			= 'form';
	}

	/**
	 * Atualiza um registro.
	 *
	 * Esta Action atualiza no banco de dados o registro originado de um
	 * formulário.
	 *
	 * Os métodos <code>beforeUpdate()</code> e <code>afterUpdate()</code>,
	 * <code>afterUpdateSave()</code> e <code>afterSave()</code> podem ser
	 * utilizados para executar lógicas antes e depois da atualização do registro.
	 *
	 * O registro será atualizado capturando os campos no padrão
	 * {tabela}_{campo}, como por exemplo user_id ou user_password, sendo "user" o
	 * nome da tabela. Após a captura irá popular os respectivos atirbutos da Model
	 * referentes aos campos identificados.
	 *
	 * Ao final da inserção, será redirecionado para a Action "index".
	 *
	 * @return void
	 */
	public function update() {
		try {
			$object = $this->getRequestObject();
			$this->beforeUpdate( $object );
			$this->setPostValuesToModel( $object );
			$this->afterUpdate( $object );
			$object->save();
			$this->afterUpdateSave( $object );
			$this->afterSave( $object );

			$this->getAjaxReturn( true, 'updated' );
			Session::setVar('flash-message', $this->message['updated']);
		} catch ( FwException $e ) {
			$this->getAjaxReturn( false, 'error' );
			Session::setVar('flash-message', $this->message['error']);
		}
		if( $this->module === false ) {
			$this->redirect( $this->getControllerName(), 'index' );
		} else {
			$this->redirectToModule(
				$this->module,
				$this->getControllerName(),
				'index'
			);
		}
	}

	/**
	 * Remove um registro.
	 *
	 * Esta Action remove do banco de dados o registro indicado pelo $id informado.
	 *
	 * Ao final da exclusão, será redirecionado para a Action "index".
	 *
	 * Os métodos <code>beforeDelete()</code> e <code>afterDelete()</code> podem ser
	 * utilizados para executar lógicas antes e depois da remocão do registro.
	 *
	 * @param int $id Id do regsitro
	 * @return void
	 */
	public function delete( $id ) {
		try {
			if( is_numeric($id) === false ) {
				throw new FwException(
					'O valor "' . $id . '" informado não é válido para a Action
					"delete" do controller "' . $this->getControllerName() . '" cujo
					utiliza a Model "' . $this->modelName . '".'
				);
			}

			$Model = new $this->modelName($id);
			$this->beforeDelete( $Model );
			$Model->delete();
			$this->afterDelete( $Model );

			$this->getAjaxReturn( true, 'deleted' );
			Session::setVar('flash-message', $this->message['deleted']);
		} catch ( FwException $e ) {
			$this->getAjaxReturn( false, 'error' );
			Session::setVar('flash-message', $this->message['error']);
		}
		if( $this->module === false ) {
			$this->redirect( $this->getControllerName(), 'index' );
		} else {
			$this->redirectToModule(
				$this->module,
				$this->getControllerName(),
				'index'
			);
		}
	}

	/**
	 * Remove vários registros.
	 *
	 * Esta Action remove do banco de dados os registros indicados por um array de
	 * $id.
	 *
	 * Ao final da exclusão, será redirecionado para a Action "index".
	 *
	 * Os métodos <code>beforeDeleteSelected()</code> e
	 * <code>afterDeleteSelected()</code> podem ser utilizados para executar
	 * lógicas antes e depois da remocão de cada registro.
	 *
	 * @return void
	 */
	public function deleteSelected() {
		$list = $this->getHttpData('grid_selected_records');
		if( is_array($list) && count($list) > 0 ) {
			foreach( $list as $item ) {
				$Model = new $this->modelName($item);
				$this->beforeDeleteSelected( $Model );
				$Model->delete();
				$this->afterDeleteSelected( $Model );
			}

			$this->getAjaxReturn( true, 'allDeleted' );
			Session::setVar('flash-message', $this->message['allDeleted']);
		} else {
			$this->getAjaxReturn( false, 'noRecordsSelected' );
			Session::setVar('flash-message', $this->message['noRecordsSelected']);
		}
		if( $this->module === false ) {
			$this->redirect( $this->getControllerName(), 'index' );
		} else {
			$this->redirectToModule(
				$this->module,
				$this->getControllerName(),
				'index'
			);
		}
	}

	/**
	 * Obtem todos os registros da model.
	 *
	 * Retorna para a listagem todos os registros que devem aparecer.
	 *
	 * Sobrescreva este método, caso necessite de alguma forma específica de
	 * recuperar os registros.
	 *
	 * @param string|mixed[] $filter Valor para filtagem dos registros
	 * @param string $order Campo e maneira de ordenação dos registros
	 * @param int $start Indica de qual resgistro começar a listar
	 * @param int $limit Número máximo de registros na listagem
	 * @return array Lista de registros
	 */
	protected function getRecords (
		$filter = array(),
		$order = null,
		$start = null,
		$limit = null
	) {
		$Model = new $this->modelName();
		if( method_exists( $Model, 'getAllRecords' ) ) {
			return $Model::getAllRecords($filter, $order, $start, $limit);
		}
		return $Model->find($filter, $order, $start, $limit);
	}

	/**
	 * Retorna para a listagem o total de registros.
	 *
	 * Sobrescreva este método, caso necessite recuperar os registros de forma
	 * específica.
	 *
	 * @param string|mixed[] $filter Valor para filtagem dos registros
	 * @return int Total de registros
	 */
	protected function getTotalRecords( $filter = array() ) {
		$Model = new $this->modelName();
		if( method_exists( $Model, 'getTotal' ) ) {
			return $Model->getTotal($filter);
		}
		return count( $Model->find(  $filter ) );
	}

	/**
	 * Obtem a instância de uma Model do Registro solicitado pela requisição.
	 *
	 * Obtem a instância de do registro passado pelos formulários de adição e edição,
	 * obtendo o id do registro usando o padrão para nome de campo {tabela}_id. Após
	 * capturado o valor de id, uma instância da model utilizada no CRUD é criada
	 * utilizando o valor capturado.
	 *
	 * @return Model Instância de uma Model do registro.
	 * @todo Adicionar a possibilidade de utilizar a chave primária diferente de "id".
	 */
	protected function getRequestObject() {
		$httpVarNamePrefix = new String( $this->modelName );
		$httpVarNamePrefix->underscore('_');
		$primaryKeyField = $httpVarNamePrefix . '_id';

		$id = null;
		if( $this->isSetHttpData($primaryKeyField) ) {
			$id = $this->getHttpData($primaryKeyField);
		}

		if( is_numeric( $id ) === false && ( empty( $id ) === false ) ) {
			throw new FwException(
				'O valor "' . $id . '" informado não é válido para a Action "edit"
				do controller "' . $this->getControllerName() . '" cujo utiliza a
				Model "' . $this->modelName . '".'
			);
		}

		return new $this->modelName( $id > 0 ? $id : null );
	}

	/**
	 * Preenche um objeto com os valores recebidos por um formulário.
	 *
	 * Preenche uma instância de uma Model com os valores recebidos de uma requisição
	 * usando o padrão {tabela}_{campo}. Caso nenhum objeto ou nome de Model for
	 * informado, as informações pré-definidas nos atributos do Controller serão
	 * utilizados para instanciar um objeto e preencher os valores.
	 *
	 * É preciso ter cuidado com campos de data por causa do formato utilizado,
	 * ou que são apresentados no formulário como checkbox, pois o valor do checkbox
	 * só é passado na requisição se estiver marcado.
	 *
	 * Este método altera apenas os campos recebidos na requisição para evitar
	 * manipulação errada de dados.
	 *
	 * @param Model|boolean $object Instância de uma Model que irá receber os valores.
	 * @param string|boolean $modelName Nome da Model.
	 * @return Model Instância de uma Model com os valores preenchidos.
	 * @todo Adicionar a possibilidade de utilizar a chave primária diferente de "id".
	 */
	protected function setPostValuesToModel( $object = false, $modelName = false ) {
		$httpData = $this->getAllHttpData();

		$object = $object ? $object : $this->getRequestObject();
		$columns = $object->getColumns();

		if( $modelName === false ) {
			$modelName = $this->modelName;
		}
		$httpVarNamePrefix = new String( $modelName );
		$httpVarNamePrefix->underscore();

		foreach ( $httpData as $httpVar => $value ) {
			foreach ( $columns as $column ) {
				$httpVarName = new String( $httpVarNamePrefix . '_' . $column );
				$httpVarName->normalize('_');
				if( $column != 'id' && $httpVarName == $httpVar ) {
					if( $object->isValid($column, $value) === false ) {
						throw new FwException(
							'O valor "' . $value . '" para o campo "' . $column .
							'" na tabela "' . $this->modelName . '" é inválido.'
						);
					}

					if( $value == '' ) {
						$object->$column = null;
					} else {
						$object->$column = $value;
					}
				}
				unset($httpVarName);
			}
		}

		return $object;
	}

	/**
	 * Retorna um objeto JSON simples contendo informações sobre a requisição.
	 *
	 * Retorna um objeto JSON simples com informações sobre o sucesso da execução e
	 * a mensagem que deve ser retornada para o usuário. O objeto possui a seguinte
	 * estrutura:
	 *
	 * <pre>
	 * {
	 *     'success' : true,
	 *     'message' : 'Texto da mensagem'
	 * }
	 * </pre>
	 *
	 * @param boolean $success Sucesso ou não da execução.
	 * @param string $messageKey Chave no array de mensagens do controller.
	 * @param string $message Mensagem personalizada.
	 * @return void
	 */
	protected function getAjaxReturn ( $success, $messageKey = null, $message = null ) {
		if( $this->isAjaxRequest() ) {
			$return = array(
				'success' => $success,
				'message' => $message == null ? $this->message[$messageKey] : $message
			);
			echo json_encode($return);
			exit();
		}
	}

	/**
	 * Executa ações antes da criação de um registro.
	 *
	 * Executa ações antes de preencher o novo registro com valores capturados do
	 * formulário.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function beforeCreate( $object ) {
	}

	/**
	 * Executa ações após a criação de um registro.
	 *
	 * Executa ações após o novo registro ter sido preenchido com valores capturados
	 * do formulário.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function afterCreate( $object ) {
	}

	/**
	 * Executa ações após efetivar a criação do registro.
	 *
	 * Executa ações após o novo registro ter sido salvo no banco de dados.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function afterCreateSave( $object ) {
	}

	/**
	 * Executa ações antes da atualização do registo.
	 *
	 * Executa ações antes de preencher o registro com valores capturados do
	 * formulário.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function beforeUpdate( $object ) {
	}

	/**
	 * Executa ações após a atualização do registo.
	 *
	 * Executa ações após o registro ter sido preenchido com valores capturados
	 * do formulário.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function afterUpdate( $object ) {
	}

	/**
	 * Executa ações após efetivar a atualização do registro.
	 *
	 * Executa ações após o registro ter sido salvo no banco de dados.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function afterUpdateSave( $object ) {
	}

	/**
	 * Executa ações após efetivar a criação ou atualização do registro.
	 *
	 * Executa ações após o registro ter sido salvo no banco de dados, seja durante
	 * a criação ou atualização.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function afterSave( $object ) {
	}

	/**
	 * Executa ações antes da exclusão do registo.
	 *
	 * Executa ações antes de excluir o registro do banco de dados.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function beforeDelete( $object ) {
	}

	/**
	 * Executa ações após efetivar a exclusão do registro.
	 *
	 * Executa ações após o registro ter sido excluído no banco de dados.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function afterDelete( $object ) {
	}

	/**
	 * Executa ações antes da exclusão de um registro.
	 *
	 * Executa ações antes de excluir um registro do banco de dados.
	 *
	 * Esta ação é executada para cada item a ser excluído.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function beforeDeleteSelected( $object ) {
	}

	/**
	 * Executa ações após efetivar a exclusão de um registro.
	 *
	 * Executa ações após um registro ter sido excluído no banco de dados.
	 *
	 * Esta ação é executada para cada item a ser excluído.
	 *
	 * @param object $object Objeto do processo
	 * @return void
	 */
	protected function afterDeleteSelected( $object ) {
	}
}