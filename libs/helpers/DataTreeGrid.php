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
 * Description of DataGrid
 *
 * @package MaiaFW\Lib\Helper
 * @category Helpers
 * @version 1.0
 */
class DataTreeGrid {
	private $_module;
	private $_controller;

	private $_urlToList = false;
	private $_urlToAdd = false;
	private $_urlToView = false;
	private $_urlToEdit = false;
	private $_urlToDelete = false;
	private $_urlToDeleteSelected = false;
	private $_urlToSearch = false;

    private $_actionList				= 'index';
    private $_actionView				= 'view';
	private $_actionAdd					= 'add';
	private $_actionEdit				= 'edit';
	private $_actionDelete				= 'delete';
	private $_actionDeleteSelected		= 'deleteSelected';
	private $_actionSearch				= 'search';

	private $_showActionView 			= true;
	private $_showActionAdd 			= true;
	private $_showActionEdit 			= true;
	private $_showActionDelete 			= true;
	private $_showActionDeleteSelected 	= true;
	private $_showSearch 				= true;

	private $_filters 					= '';
	private $_records 					= array();
	private $_columns 					= array();
	private $_methods					= array();
	private $_buttons					= array();
	private $_buttonsConditinals		= array();
	private $_total 					= 0;
	private $_actualPage 				= 1;
	private $_paginationLimit 			= 25;
	private $_maxLinkPagesNumbers		= 5;

	private $_sortField					= 'name';
	private $_sortOrder					= 'ASC';

	private $_templatePath;
	private $_templateRowPath;

	public function __construct( $controller, $module = null ) {
		$this->_controller		= $controller;
		$this->_module			= $module;
		$this->_templatePath	= FWROOT . 'libs/helpers/resource/datatreegrid/treegrid.tpl.php';
		$this->_templateRowPath	= FWROOT . 'libs/helpers/resource/datatreegrid/treegridrow.tpl.php';
		// Create a interface for helpers and class to extends with commons attributes
		// Add path to template in helpers resource
	}



	public function setFilters( $filters ) {
		/*if ( is_array($filters) ) {
			$this->_filters = $filters;
		} else {
			$this->_filters = array($filters);
		}*/
		$this->_filters = $filters;
	}

	public function getFilters() {
		return $this->_filters;
	}

	public function setLimit( $limit ) {
		return $this->_paginationLimit = (int) $limit;
	}

	public function getLimit() {
		return $this->_paginationLimit;
	}

	public function setRecords( $records ) {
		return $this->_records = $records;
	}

	public function getRecords() {
		return $this->_records;
	}

	public function setPage( $page ) {
		return $this->_actualPage = (int) $page;
	}

	public function getPage() {
		return $this->_actualPage;
	}

	public function setTotal( $total ) {
		$this->_total = (int) $total;
	}

	public function getTotal() {
		return $this->_total;
	}

	public function setSortField( $field ) {
		$this->_sortField = strtolower( $field );
	}

	public function getSortField() {
		return $this->_sortField;
	}

	public function setSortOrder( $order ) {
		$this->_sortOrder = strtolower( $order );
	}

	public function getSortOrder() {
		return $this->_sortOrder;
	}



	public function setShowActionAdd( $value ) {
		$this->_showActionAdd = (bool) $value;
	}

	public function getShowActionAdd() {
		return $this->_showActionAdd;
	}

	public function setShowActionDelete( $value ) {
		$this->_showActionDelete = (bool) $value;
	}

	public function getShowActionDelete() {
		return $this->_showActionDelete;
	}

	public function setShowActionDeleteSelected( $value ) {
		$this->_showActionDeleteSelected = (bool) $value;
	}

	public function getShowActionDeleteSelected() {
		return $this->_showActionDeleteSelected;
	}

	public function setShowActionEdit( $value ) {
		$this->_showActionEdit = (bool) $value;
	}

	public function getShowActionEdit() {
		return $this->_showActionEdit;
	}

	public function setShowActionView( $value ) {
		$this->_showActionView = (bool) $value;
	}

	public function getShowActionView() {
		return $this->_showActionView;
	}


	public function getFirstPage() {
		return 1;
	}

	public function getPreviousPage() {
		if ( $this->getPage() > 1 ) {
			return $this->getPage() - 1;
		}
		return false;
	}

	public function getNextPage() {
		$total = $this->getLastPage();
		if ( $total > $this->getPage() ) {
			return $this->getPage() + 1;
		}
		return false;
	}

	public function getLastPage() {
		return ceil( $this->getTotal() / $this->getLimit() );
	}



	public function setTemplate( $path ) {
		$this->_templatePath = $path;
	}

	public function setTemplateRow( $path ) {
		$this->_templateRowPath = $path;
	}

	public function setUrlToList( $value ) {
		$this->_urlToList = $value;
	}

	public function getUrlToList( $params = array() ) {
		if( $this->_urlToList ) {
			return $this->_urlToList . '/' . join( '/', $params );
		} elseif ( $this->_module ) {
			return UrlMaker::toModuleAction( $this->_module, $this->_controller, $this->_actionList, $params );
		}
		return UrlMaker::toAction( $this->_controller, $this->_actionList, $params );
	}

	public function setUrlToView( $value ) {
		$this->_urlToView = $value;
	}

	public function getUrlToView( $params = array() ) {
		if( $this->_urlToView ) {
			return $this->_urlToView . '/' . join( '/', $params );
		} elseif ( $this->_module ) {
			return UrlMaker::toModuleAction( $this->_module, $this->_controller, $this->_actionView, $params );
		}
		return UrlMaker::toAction( $this->_controller, $this->_actionView, $params );
	}

	public function setUrlToAdd( $value ) {
		$this->_urlToAdd = $value;
	}

	public function getUrlToAdd( $params = array() ) {
		if( $this->_urlToAdd ) {
			return $this->_urlToAdd . '/' . join( '/', $params );
		} elseif ( $this->_module ) {
			return UrlMaker::toModuleAction( $this->_module, $this->_controller, $this->_actionAdd, $params );
		}
		return UrlMaker::toAction( $this->_controller, $this->_actionAdd, $params );
	}

	public function setUrlToEdit( $value ) {
		$this->_urlToEdit = $value;
	}

	public function getUrlToEdit( $params = array() ) {
		if( $this->_urlToEdit ) {
			return $this->_urlToEdit . '/' . join( '/', $params );
		} elseif ( $this->_module ) {
			return UrlMaker::toModuleAction( $this->_module, $this->_controller, $this->_actionEdit, $params );
		}
		return UrlMaker::toAction( $this->_controller, $this->_actionEdit, $params );
	}

	public function setUrlToDelete( $value ) {
		$this->_urlToDelete = $value;
	}

	public function getUrlToDelete( $params = array() ) {
		if( $this->_urlToDelete ) {
			return $this->_urlToDelete . '/' . join( '/', $params );
		} elseif ( $this->_module ) {
			return UrlMaker::toModuleAction( $this->_module, $this->_controller, $this->_actionDelete, $params );
		}
		return UrlMaker::toAction( $this->_controller, $this->_actionDelete, $params );
	}

	public function setUrlToDeleteSelected( $value ) {
		$this->_urlToDeleteSelected = $value;
	}

	public function getUrlToDeleteSelected( $params = array() ) {
		if( $this->_urlToDeleteSelected ) {
			return $this->_urlToDeleteSelected . '/' . join( '/', $params );
		} elseif ( $this->_module ) {
			return UrlMaker::toModuleAction( $this->_module, $this->_controller, $this->_actionDeleteSelected, $params );
		}
		return UrlMaker::toAction( $this->_controller, $this->_actionDeleteSelected, $params );
	}

	public function getUrlToSortByField( $field ) {
		if ( $field == $this->getSortField() ) {
			$order = $this->getSortOrder() == 'asc' ? 'desc' : 'asc';
		} else {
			$order = 'asc';
		}
		$params = array(
			'page'			=> $this->getFirstPage(),
			'sortField'		=> $field,
			'sortOrder'		=> $order,
			'limit'			=> $this->getLimit(),
			'filters'		=> $this->getFilters()
		);
		if( $this->_module !== null ) {
			return UrlMaker::toModuleAction( $this->_module , $this->_controller, $this->_actionList, $params );
		}
		return UrlMaker::toAction( $this->_controller, $this->_actionList, $params );
	}

	public function setUrlToSearch( $value ) {
		$this->_urlToSearch = $value;
	}

	public function getUrlToSearch( $params = array() ) {
		if( $this->_urlToSearch ) {
			return $this->_urlToSearch . '/' . join( '/', $params );
		} elseif ( $this->_module ) {
			return UrlMaker::toModuleAction( $this->_module, $this->_controller, $this->_actionSearch, $params );
		}
		return UrlMaker::toAction( $this->_controller, $this->_actionSearch, $params );
	}

	public function getColumn( $field ) {
		return $this->_columns[$field];
	}

	public function getColumns() {
		return $this->_columns;
	}

	public function addColumn( $field, $title, $sortable = true, $model = null, $values = array() ) {
		$this->_columns[$field] = array (
			'field'		=> $field,
			'title'		=> $title,
			'sortable'	=> (bool) $sortable,
			'model'		=> $model,
			'values'	=> $values
		);
	}

	public function getButtons() {
		return $this->_buttons;
	}

	/**
	 * Adiciona uma coluna para exibir o retorno de um método da Model
	 *
	 * Adiciona uma coluna que irá chamar um método específico de uma Model e exibir
	 * seu retorno.
	 *
	 * O retorno do método deverá se uma string ou um valor que possa ser convertido
	 * para string.
	 *
	 * Este método só deve ser chamado caso a lista de registros for uma coleção de
	 * objetos.
	 *
	 * @param type $modelMethodName Nome do método da model.
	 * @param type $title Cabeçalho da coluna.
	 */
	public function addColumnForModelMethod( $modelMethodName, $title ) {
		$this->_methods[] = array (
			'method'	=> $modelMethodName,
			'title'		=> $title
		);
	}

	public function getColumnForModelMethod() {
		return $this->_methods;
	}

	/**
	 * Adiciona uma coluna com um botão de acão na grid
	 *
	 * @param string $buttonText Texto que aparecerá no botão
	 * @param string $controller Controller da ação do botão
	 * @param string $action Action da ação do botão
	 * @param string $field Campo que serão passado como parametro no link
	 */
	public function addColumnButton ( $buttonText, $controller, $action, $field = 'id' ) {
		$this->_buttons[] = array (
			'field'		=> $field,
			'module'	=> false,
			'controller'=> $controller,
			'action'	=> $action,
			'text'		=> $buttonText,
			'conditionalsValues' => false
		);
	}

	public function addColumnButtonByModule ( $buttonText, $module, $controller, $action, $field = 'id' ) {
		$this->_buttons[] = array (
			'field'		=> $field,
			'module'	=> $module,
			'controller'=> $controller,
			'action'	=> $action,
			'text'		=> $buttonText,
			'conditionalsValues' => false
		);
	}

	public function getButtonsConditionals() {
		return $this->_buttonsConditinals;
	}

	/**
	 * Adiciona uma coluna com um botão de acão na grid que varia de acordo com o
	 * valor de um determinado campo.
	 *
	 * @param string $buttonText Texto que aparecerá no botão
	 * @param string $controller Controller da ação do botão
	 * @param string $action Action da ação do botão
	 * @param string $field Campo que serão passado como parametro no link
	 * @param array $conditionalsValues Valores condicionais. Usado para quando a URL
	 * de ação do link irá mudar de acordo com o valor do campo do registro.
	 * Ex: array(
	 *		'true' => array( $controller, $actionTrue, $text, $field ),
	 *		'false' =>  array( $controller, $actionTrue, $text, $field )
	 * );
	 */
	public function addColumnButtonConditional ( $conditionalsValues, $field = 'id', $ajaxAction = false ) {
		$this->_buttonsConditinals[] = array (
			'field'		=> $field,
			'conditionalsValues' => $conditionalsValues,
			'ajaxAction'=> $ajaxAction
		);
	}

	public function makeTreeGrid( $records ) {
		require $this->_templateRowPath;
	}

	public function generate() {
		require $this->_templatePath;
	}
}