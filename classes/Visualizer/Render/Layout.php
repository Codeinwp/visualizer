<?php

// +----------------------------------------------------------------------+
// | Copyright 2013  Madpixels  (email : visualizer@madpixels.net)        |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+
// | Author: Eugene Manuilov <eugene@manuilov.org>                        |
// +----------------------------------------------------------------------+
/**
 * Layout rendering class.
 *
 * @category Visualizer
 * @package Render
 */
class Visualizer_Render_Layout extends Visualizer_Render {

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @abstract
	 * @access protected
	 */
	protected function _toHTML() {
		// empty.
	}

	/**
	 * Show the layout by delegating the call to the layout-specific method with the params.
	 *
	 * @access public
	 */
	public static function show( $layout ) {
		return call_user_func( array( __CLASS__, '_render' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $layout ) ) ) ), func_get_args() );
	}

	/**
	 * Show the fake editor (just an empty div).
	 *
	 * @access public
	 */
	public static function _renderFauxEditor( $args ) {
		echo '<div id="chart-lhs" class="visualizer-editor-lhs" style="display: none"></div>';
	}

	/**
	 * Show the DB wizard.
	 *
	 * @access public
	 */
	public static function _renderDbWizard( $args ) {
		$tables     = $args[1];
		$params     = $args[2];
		$tbl        = null;
		$columns    = null;
		$group      = array();
		$order      = array();
		$limit      = '';
		$where      = array();
		if ( ! empty( $params['from'] ) ) {
			$tbl        = $params['from'];
		}
		if ( ! empty( $params['select'] ) ) {
			$select     = $params['select'];
		}
		if ( ! empty( $params['group'] ) ) {
			$group      = $params['group'];
		}
		if ( ! empty( $params['order'] ) ) {
			$order      = $params['order'];
		}
		if ( ! empty( $params['limit'] ) ) {
			$limit      = $params['limit'];
		}
		if ( ! empty( $params['where'] ) ) {
			$where      = $params['where'];
		}

		if ( ! empty( $params ) ) {
			$columns    = Visualizer_Source_Query_Params::get_table_columns( $tbl );
		}
	?>
		<div id='visualizer-db-wizard' style="display: none">
			<form id='db-wizard-form'>
				<div class='db-wizard-query'>
					<div class='db-wizard-from'>
						<label for="from"><?php _e( 'Select Table', 'visualizer' ); ?></label>
						<select name='from' class='visualizer-select-from' data-placeholder='<?php _e( 'Select Table', 'visualizer' ); ?>'>
							<option></option>
						<?php
						foreach ( $tables as $table ) {
							$selected   = $tbl === $table ? 'selected' : '';
							echo '<option value="' . esc_attr( $table ) . '" ' . $selected . '>' . esc_html( $table ) . '</option>';
						}
						?>
						</select>
					</div>
					<div class='db-wizard-select'>
						<label for="select[]"><?php _e( 'Select Columns', 'visualizer' ); ?></label>
						<select name='select[]' class='visualizer-select-select' data-placeholder='<?php _e( 'Select Columns', 'visualizer' ); ?>' multiple>
							<option></option>
						<?php
						if ( $columns ) {
							foreach ( $columns as $col ) {
								$selected   = in_array( $col['name'], $select ) ? 'selected' : '';
								echo '<option value="' . $col['name'] . '" ' . $selected . '>' . $col['name'] . '</option>';
							}

							// each column will also have a count(*), count(distinct #).
							$extra = array( 'count(*)', 'count(distinct #)' );
							foreach ( $extra as $x ) {
								if ( strpos( $x, '#' ) !== false ) {
									foreach ( $columns as $col ) {
										$name   = str_replace( '#', $col['name'], $x );
										$selected   = in_array( $name, $select ) ? 'selected' : '';
										echo '<option value="' . $name . '" ' . $selected . '>' . $name . '</option>';
									}
								} else {
									$selected   = in_array( $x, $select ) ? 'selected' : '';
									echo '<option value="' . $x . '" ' . $selected . '>' . $x . '</option>';
								}
							}
						}
						?>
						</select>
					</div>

					<div class='db-wizard-templates'>
						<?php
						if ( $where ) {
							$index = 0;
							foreach ( $where as $col ) {
								self::show( 'db-wizard-where', $columns, $col, $index++, $params );
							}
						}
						?>
					</div>
					
					<button class='db-wizard-where-template-add' title='<?php _e( 'Add', 'visualizer' ); ?>'>+</button>
					
					<div class='db-wizard-group'>
						<label for="group[]"><?php _e( 'Group By', 'visualizer' ); ?></label>
						<select name='group[]' class='visualizer-select-group' data-placeholder='<?php _e( 'Group By', 'visualizer' ); ?>' multiple>
							<option></option>
						<?php
						if ( $columns ) {
							foreach ( $columns as $col ) {
								$selected   = in_array( $col['name'], $group ) ? 'selected' : '';
								echo '<option value="' . $col['name'] . '" ' . $selected . '>' . $col['name'] . '</option>';
							}
						}
						?>
						</select>
					</div>
					<div class='db-wizard-order'>
						<label for="order[]"><?php _e( 'Order By', 'visualizer' ); ?></label>
						<select name='order[]' class='visualizer-select-order' data-placeholder='<?php _e( 'Order By', 'visualizer' ); ?>' multiple>
							<option></option>
						<?php
						if ( $columns ) {
							foreach ( $columns as $col ) {
								$selected   = in_array( $col['name'], $order ) ? 'selected' : '';
								echo '<option value="' . $col['name'] . '" ' . $selected . '>' . $col['name'] . '</option>';
							}
						}
						?>
						</select>
					</div>
					<div class='db-wizard-limit'>
						<label for="limit"><?php _e( 'Limit results to', 'visualizer' ); ?></label>
						<input type="number" min="0" name='limit' class='visualizer-select-limit' placeholder='<?php _e( 'Limit results to', 'visualizer' ); ?>' value='<?php echo $limit; ?>'>
					</div>
				</div>

				<input type="button" class="button button-primary" id='visualizer-query-fetch' value='<?php _e( 'Show Results', 'visualizer' ); ?>'>
			</form>

			<div class='db-wizard-results'></div>
			<div class='db-wizard-error'>
				<div class='query'></div>
				<div class='msg'></div>
			</div>

			<!-- templates -->
			<div style="display: none">
				<div class='db-wizard-where-template'>
					<?php self::show( 'db-wizard-where', $columns, null, null, null ); ?>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Show the DB wizard's where block.
	 *
	 * @access public
	 */
	public static function _renderDbWizardWhere( $args ) {
		$columns    = $args[1];
		$where      = $args[2];
		$index      = $args[3];
		$params     = $args[4];
		$type       = null;
		$operator   = null;
		$operand    = '';
	?>
	<div class='db-wizard-where-clause'>
		<div class='db-wizard-where'>
			<label for="where[]"><?php _e( 'Where', 'visualizer' ); ?></label>
			<select name='where[]' class='visualizer-select-where' data-placeholder='<?php _e( 'Where', 'visualizer' ); ?>'>
				<option></option>
				<?php
				if ( $columns ) {
					foreach ( $columns as $col ) {
						$selected   = $col['name'] === $where ? 'selected' : '';
						if ( ! empty( $selected ) ) {
							$type   = $col['type'];
						}
						echo '<option value="' . $col['name'] . '" ' . $selected . ' data-type="' . $col['type'] . '">' . $col['name'] . '</option>';
					}

					if ( ! empty( $where ) && ! empty( $type ) ) {
						$operator   = $params[ $type . '-operator' ][ $index ];
						$operand    = $params[ $type ][ $index ];
					} elseif ( empty( $where ) ) {
						// we will show one empty where condition by default.
						$type       = 's';
					}
				}
				?>
			</select>
		</div>
		<div class='db-wizard-condition'>
			<div class='select-condition select-condition-n <?php echo 'n' === $type ? 'active' : ''; ?>'>
				<div class='select-condition-operator'>
					<?php
						$operators  = array(
							'>'     => __( 'Greater than', 'visualizer' ),
							'>='    => __( 'Greater than or equals', 'visualizer' ),
							'<'     => __( 'Less than', 'visualizer' ),
							'<='    => __( 'Less than or equals', 'visualizer' ),
							'='     => __( 'Equals', 'visualizer' ),
							'!='    => __( 'Not Equals', 'visualizer' ),
						);
					?>
					<select name="n-operator[]" data-placeholder='<?php _e( 'Operator', 'visualizer' ); ?>'>
					<?php
					foreach ( $operators as $k => $v ) {
						$selected   = $k === $operator ? 'selected' : '';
						echo '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
					}
					?>
					</select>
				</div>
				<div class='select-condition-operand'>
					<input type="number" name="n[]" placeholder="<?php _e( 'Value', 'visualizer' ); ?>" value="<?php echo $operand; ?>">
				</div>
			</div>
			<div class='select-condition select-condition-s <?php echo 's' === $type ? 'active' : ''; ?>'>
				<div class='select-condition-operator'>
					<?php
						$operators  = array(
							'='     => __( 'Equals', 'visualizer' ),
							'!='    => __( 'Not Equals', 'visualizer' ),
							'like'  => __( 'Like', 'visualizer' ),
						);
					?>
					<select name="s-operator[]" data-placeholder='<?php _e( 'Operator', 'visualizer' ); ?>'>
					<?php
					foreach ( $operators as $k => $v ) {
						$selected   = $k === $operator ? 'selected' : '';
						echo '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
					}
					?>
					</select>
				</div>
				<div class='select-condition-operand'>
					<input type="text" name="s[]" placeholder="<?php _e( 'Value', 'visualizer' ); ?>" value="<?php echo $operand; ?>">
				</div>
			</div>
			<div class='select-condition select-condition-d <?php echo 'd' === $type ? 'active' : ''; ?>'>
				<div class='select-condition-operator'>
					<?php
						$operators  = array(
							'='     => __( 'Equals', 'visualizer' ),
							'!='    => __( 'Not Equals', 'visualizer' ),
							'<='    => __( 'Before', 'visualizer' ),
							'>='    => __( 'After', 'visualizer' ),
						);
					?>
					<select name="d-operator[]" data-placeholder='<?php _e( 'Operator', 'visualizer' ); ?>'>
					<?php
					foreach ( $operators as $k => $v ) {
						$selected   = $k === $operator ? 'selected' : '';
						echo '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
					}
					?>
					</select>
				</div>
				<div class='select-condition-operand'>
					<input type="date" name="d[]" placeholder="<?php _e( 'Value', 'visualizer' ); ?>" value="<?php echo $operand; ?>">
				</div>
			</div>
		</div>
		<button class='db-wizard-where-template-remove' title='<?php _e( 'Remove', 'visualizer' ); ?>'>-</button>
	</div>
	<?php
	}

	/**
	 * Show the DB wizard's results table.
	 *
	 * @access public
	 */
	public static function _renderDbWizardResults( $args ) {
		$headers    = $args[1];
		$results    = $args[2];
		ob_start();
	?>
		<table cellspacing="0" width="100%" id="results">
			<thead>
				<tr>
	<?php
	foreach ( $headers as $header ) {
		echo '<th>' . $header['label'] . '</th>';
	}
	?>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>					
	<?php
	foreach ( $results as $result ) {
		echo '<tr>';
		foreach ( $result as $r ) {
			echo '<td>' . $r . '</td>';
		}
		echo '</tr>';
	}
	?>
			</tbody>
		</table>
	<?php
		return ob_get_clean();
	}
}
