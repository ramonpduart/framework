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

//$records = $this->getRecords();
$columns = $this->getColumns();
$buttons = $this->getButtons();
$buttonsConditionals = $this->getButtonsConditionals();
$filters = $this->getFilters();
foreach ( $records as $record ) {
	$value = $record['record'];
	?>
	<tr class="treegrid-<?php echo $value['id'] ?><?php echo $value['parent_id'] ? ' treegrid-parent-' . $value['parent_id'] : '' ?>">
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
				echo '<a href="' . UrlMaker::toAction( $buttonValues['controller'], $buttonValues['action'], array( $buttonValues['field'] => $value[$buttonValues['field'] ? $buttonValues['field'] : $button['field'] ] ) ) . '" title="" class="button_common' . ( $buttonValues['ajaxAction'] ? ' ' . $button['field'] .'-ajax-action' : '' ) . '">' . $buttonValues['text'] . '</a>';
				echo '</td>';
			}
		?>
		<td>
			<a href="<?php echo $this->getUrlToView( array( 'id' => $value['id']) ) ?>" title="" class="grid_button_view ajax"><span>&nbsp;</span>View</a>
			<a href="<?php echo $this->getUrlToEdit( array( 'id' => $value['id']) ) ?>" title="" class="grid_button_edit ajax"><span>&nbsp;</span>Edit</a>
			<a href="<?php echo $this->getUrlToDelete( array( 'id' => $value['id']) ) ?>" title="" class="grid_button_delete"><span>&nbsp;</span>Delete</a>
		</td>
	</tr>
	<?php
	$this->makeTreeGrid( $record['children'] );
}
?>