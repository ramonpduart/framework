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
<?php
/*
<form action="<?php echo $this->getUrlToSearch() ?>" method="post" class="grid_search">
	<span>
		<label for="grid_search_query">Search:</label>
		<input type="text" name="query" id="grid_search_query" class="grid_search_input" value="<?php echo $filters ?>" />
	</span>
	<input type="submit" value="Ok" class="grid_search_button" />
</form>
 */
?>
<form action="<?php echo $this->getUrlToDeleteSelected() ?>" method="post" class="grid_form">

	<a href="<?php echo $this->getUrlToAdd() ?>" title="" class="grid_button_add button_add ajax">Add new</a>
	<span>
	</span>
	<input type="submit" value="Delete" disabled="true" id="grid_button_delete_all" class="grid_button_delete_all button_inactive" />

	<table class="grid_view tree">
		<thead>
			<tr>
				<th scope="col" class="grid_column_select">
					<input type="checkbox" name="grid_select_all" id="grid_select_all" title="Select all" />
				</th>
				<?php foreach ( $columns as $column ) { ?>
					<th scope="col">
						<?php
							//if( $column['sortable'] ) {
							//	echo '<a href="' . $this->getUrlToSortByField($column['field']) . '" title="" />';
							//}

							echo $column['title'];

							//if( $column['sortable'] ) {
							//	echo '</a>';
							//}
						?>
					</th>
				<?php } ?>
				<?php foreach ( $buttons as $button ) { ?>
					<th scope="col" class="action-<?php echo $button['field'] ?>"></th>
				<?php } ?>
				<?php foreach ( $buttonsConditionals as $button ) { ?>
					<th scope="col" class="action-<?php echo $button['field'] ?>"></th>
				<?php } ?>
				<th scope="col" class="grid_column_actions">Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php $this->makeTreeGrid( $records ); ?>
		</tbody>
	</table>
</form>
<?php
/*
<div class="grid_pagination_area">
	<ul class="grid_pagination_actions">
		<?php if ( $this->getPreviousPage() != false ) { ?>
			<li class="grid_pagination_actions_first"><a href="<?php echo $this->getUrlToList( array( 'page' => $this->getFirstPage(), 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'limit' => $this->getLimit(), 'filter' => $this->getFilters() ) ) ?>" title="" />First</a></li>
			<li class="grid_pagination_actions_previous"><a href="<?php echo $this->getUrlToList( array( 'page' => $this->getPreviousPage(), 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'limit' => $this->getLimit(), 'filter' => $this->getFilters() ) ) ?>" title="" />Previous</a></li>
		<?php } else { ?>
			<li class="grid_pagination_actions_first grid_pagination_actions_inactive">First</li>
			<li class="grid_pagination_actions_previous grid_pagination_actions_inactive">Previous</li>
		<?php } ?>

		<?php
			$totalPages = $this->getLastPage();
			for( $page = 1; $page <= $totalPages; $page++ ) {
				if( $this->getPage() != $page ) {
		?>
				<li class="grid_pagination_actions_numbers"><a href="<?php echo $this->getUrlToList( array( 'page' => $page, 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'limit' => $this->getLimit(), 'filter' => $this->getFilters() ) ) ?>" title="" /><?php echo $page ?></a></li>
			<?php } else { ?>
			<li class="grid_pagination_actions_numbers grid_pagination_actions_active"><?php echo $page ?></li>
		<?php } } ?>

		<?php if ( $this->getNextPage() != false ) { ?>
			<li class="grid_pagination_actions_next"><a href="<?php echo $this->getUrlToList( array( 'page' => $this->getNextPage(), 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'limit' => $this->getLimit(), 'filter' => $this->getFilters() ) ) ?>" title="" />Next</a></li>
			<li class="grid_pagination_actions_last"><a href="<?php echo $this->getUrlToList( array( 'page' => $this->getLastPage(), 'sortField' => $this->getSortField(), 'sortOrder' => $this->getSortOrder(), 'limit' => $this->getLimit(), 'filter' => $this->getFilters() ) ) ?>" title="" />Last</a></li>
		<?php } else { ?>
			<li class="grid_pagination_actions_next grid_pagination_actions_inactive">Next</li>
			<li class="grid_pagination_actions_last grid_pagination_actions_inactive">Last</li>
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
		<span class="grid_pagination_stats_text">Showing <?php echo $start . ' to ' . $end ?> of <?php echo $totalRecords ?></span>
	</div>
</div>
 */
?>

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
				if( $(this).attr('checked') === 'checked' ) {
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
		if( active === true ) {
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