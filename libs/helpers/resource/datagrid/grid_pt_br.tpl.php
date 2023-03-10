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

$records = $this->getRecords();
$columns = $this->getColumns();
$buttons = $this->getButtons();
$buttonsConditionals = $this->getButtonsConditionals();
$filters = $this->getFilters();
?>
<form action="<?php echo $this->getUrlToSearch() ?>" method="post" class="grid_search">
	<span>
		<label for="grid_search_query">Busca:</label>
		<input type="text" name="query" id="grid_search_query" class="grid_search_input" value="<?php echo $filters ?>" />
	</span>
	<input type="submit" value="Ok" class="grid_search_button" />
</form>
<form action="<?php echo $this->getUrlToDeleteSelected() ?>" method="post" class="grid_form">

	<a href="<?php echo $this->getUrlToAdd() ?>" title="" class="grid_button_add button_add ajax">Adicionar</a>
	<span>
	</span>
	<input type="submit" value="Excluir" disabled="true" id="grid_button_delete_all" class="grid_button_delete_all button_inactive" />

	<table class="grid_view">
		<thead>
			<tr>
				<th scope="col" class="grid_column_select">
					<input type="checkbox" name="grid_select_all" id="grid_select_all" title="Selecionar todos" />
				</th>
				<?php foreach ( $columns as $column ) { ?>
					<th scope="col">
						<?php
							if( $column['sortable'] ) {
								echo '<a href="' . $this->getUrlToSortByField($column['field']) . '" title="" />';
							}

							echo $column['title'];

							if( $column['sortable'] ) {
								echo '</a>';
							}
						?>
					</th>
				<?php } ?>
				<?php foreach ( $buttons as $button ) { ?>
					<th scope="col"></th>
				<?php } ?>
				<?php foreach ( $buttonsConditionals as $button ) { ?>
					<th scope="col"></th>
				<?php } ?>
				<th scope="col" class="grid_column_actions">A????es</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $records as $value ) { ?>
			<tr>
				<td><input type="checkbox" name="grid_selected_records[]" value="<?php echo $value['id'] ?>" class="grid_checkbox_delete" /></td>
				<?php
					foreach ( $columns as $column ) {
						if( $column['model'] !== null ) {
							if( $value[ $column['field'] ] != null ) {
								$modelName = $column['model'];
								$columnModel = new $modelName( $value[ $column['field'] ] );
								echo '<td>' . $columnModel . '</td>';
							} else {
								echo '<td>-</td>';
							}
						} elseif( count( $column['values'] ) > 0 ) {
							echo '<td>' . $column['values'][ $value[ $column['field'] ] ] . '</td>';
						} else {
							echo '<td>' . $value[ $column['field'] ] . '</td>';
						}
					}
				?>
				<?php
					foreach ( $buttons as $button ) {
						echo '<td>';
						echo '<a href="' . UrlMaker::toAction( $button['controller'], $button['action'], array( $button['field'] => $value[$button['field']] ) ) . '" title="" class="button_common">' . $button['text'] . '</a>';
						echo '</td>';
					}
				?>
				<?php
					foreach ( $buttonsConditionals as $button ) {
						$buttonValues = $button['conditionalsValues'][$value[$button['field']]];
						echo '<td>';
						echo '<a href="' . UrlMaker::toAction( $buttonValues['controller'], $buttonValues['action'], array( $buttonValues['field'] => $value[$buttonValues['field'] ? $buttonValues['field'] : $button['field'] ] ) ) . '" title="" class="button_common' . ( $buttonValues['ajaxAction'] ? 'ajax-action' : '' ) . '">' . $buttonValues['text'] . '</a>';
						echo '</td>';
					}
				?>
				<td>
					<a href="<?php echo $this->getUrlToView( array( 'id' => $value['id']) ) ?>" title="" class="grid_button_view ajax"><span>&nbsp;</span>Ver</a>
					<a href="<?php echo $this->getUrlToEdit( array( 'id' => $value['id']) ) ?>" title="" class="grid_button_edit ajax"><span>&nbsp;</span>Editar</a>
					<a href="<?php echo $this->getUrlToDelete( array( 'id' => $value['id']) ) ?>" title="" class="grid_button_delete"><span>&nbsp;</span>Excluir</a>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</form>
<div class="grid_pagination_area">
	<ul class="grid_pagination_actions">
		<?php if ( $this->getPreviousPage() != false ) { ?>
			<li class="grid_pagination_actions_first"><a href="<?php echo $this->getUrlToList( array( 'page' => $this->getFirstPage(), 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'filter' => $this->getFilters(), 'limit' => $this->getLimit() ) ) ?>" title="" />Primeiro</a></li>
			<li class="grid_pagination_actions_previous"><a href="<?php echo $this->getUrlToList( array( 'page' => $this->getPreviousPage(), 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'filter' => $this->getFilters(), 'limit' => $this->getLimit() ) ) ?>" title="" />Anterior</a></li>
		<?php } else { ?>
			<li class="grid_pagination_actions_first grid_pagination_actions_inactive">Primeiro</li>
			<li class="grid_pagination_actions_previous grid_pagination_actions_inactive">Anterior</li>
		<?php } ?>

		<?php
			$totalPages = $this->getLastPage();
			for( $page = 1; $page <= $totalPages; $page++ ) {
				if( $this->getPage() != $page ) {
		?>
				<li class="grid_pagination_actions_numbers"><a href="<?php echo $this->getUrlToList( array( 'page' => $page, 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'filter' => $this->getFilters(), 'limit' => $this->getLimit() ) ) ?>" title="" /><?php echo $page ?></a></li>
			<?php } else { ?>
			<li class="grid_pagination_actions_numbers grid_pagination_actions_active"><?php echo $page ?></li>
		<?php } } ?>

		<?php if ( $this->getNextPage() != false ) { ?>
			<li class="grid_pagination_actions_next"><a href="<?php echo $this->getUrlToList( array( 'page' => $this->getNextPage(), 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'filter' => $this->getFilters(), 'limit' => $this->getLimit() ) ) ?>" title="" />Pr??ximo</a></li>
			<li class="grid_pagination_actions_last"><a href="<?php echo $this->getUrlToList( array( 'page' => $this->getLastPage(), 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'filter' => $this->getFilters(), 'limit' => $this->getLimit() ) ) ?>" title="" />??ltimo</a></li>
		<?php } else { ?>
			<li class="grid_pagination_actions_next grid_pagination_actions_inactive">Pr??ximo</li>
			<li class="grid_pagination_actions_last grid_pagination_actions_inactive">??ltimo</li>
		<?php } ?>

	</ul>
	<div class="grid_pagination_stats">
		<?php
			$page = $this->getPage();
			$limit = $this->getLimit();
			$totalRecords = $this->getTotal();

			$start = $page > 1 ? ( $page - 1 ) * $limit + 1 : 1;
			$start = $start > $totalRecords ? $totalRecords : $start;
			$end =  $page * $limit < $totalRecords ? $page * $limit : $totalRecords;
		?>
		<span class="grid_pagination_stats_text">Mostrando de <?php echo $start . ' a ' . $end ?> de <?php echo $totalRecords ?></span>
	</div>
</div>

<script>
	$(document).ready(
		function() {
			$('.grid_view tbody tr').click(function (event) {
				if (event.target.type !== 'checkbox') {
					checkbox = $(this).children('td').children('.grid_checkbox_delete');
      				$(':checkbox', this).trigger('click');
			    }
			});
			$('#grid_select_all').change(function (event) {
				if( $(this).attr('checked') == 'checked' ) {
					$('.grid_checkbox_delete').attr('checked', 'checked');
					toggleDeleteAllButton( true );
				} else {
					$('.grid_checkbox_delete').removeAttr('checked');
					toggleDeleteAllButton( false );
				}
			});
			$('.grid_checkbox_delete').change(function (event) {
				if( $('.grid_checkbox_delete:checked').length > 0) {
					toggleDeleteAllButton( true );
				} else {
					toggleDeleteAllButton( false );
				}
			});
			$('#grid_button_delete_all').click(function (event) {
				if( $('.grid_checkbox_delete:checked').length > 0) {
					return confirm('Are you sure to delete selected items?');
				} else {
					alert('Please, select some record.');
					return false;
				}
			});
			$('.grid_button_delete').click(function (event) {
				return confirm('Are you sure to delete selected items?');
			});
	});
	function toggleDeleteAllButton( active ) {
		if( active == true ) {
			$('#grid_button_delete_all').removeAttr('disabled')
										.removeClass('button_inactive')
										.addClass('button_delete');
		} else {
			$('#grid_button_delete_all').attr('disabled', true)
										.removeClass('button_delete')
										.addClass('button_inactive');
		}
	}
</script>