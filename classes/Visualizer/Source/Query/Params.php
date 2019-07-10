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
 * Source manager for query builder.
 *
 * @category Visualizer
 * @package Source
 */
class Visualizer_Source_Query_Params extends Visualizer_Source_Query {

	/**
	 * The params that will form the query.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_params;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $params The params that will form the query.
	 */
	public function __construct( $params = null ) {
		$this->_params = $params;
		if ( ! empty( $params ) ) {
			$this->build_query();
		}
	}

	/**
	 * Rearrange columns so that a string column, if present, is always first in the select list as this will form the x axis.
	 */
	private function rearrange_columns_for_x_axis( $columns, $select, $tables ) {
		if ( ! $select ) {
			return null;
		}

		$first  = null;
		$index  = 0;
		foreach ( $select as $column ) {
			if ( ! is_null( $first ) ) {
				break;
			}
			$table  = is_array( $tables ) ? $tables[0] : $tables;
			$col    = $column;
			if ( 0 !== strpos( $column, 'count(' ) ) {
				if ( count( $tables ) > 1 ) {
					$arr    = explode( '.', $column );
					$table  = $arr[0];
					$col    = $arr[1];
				}
				foreach ( $columns[ $table ] as $table_cols ) {
					if ( $table_cols['name'] === $column && 'n' !== $table_cols['type'] ) {
						$first  = $column;
						break;
					}
				}
			}
			$index++;
		}

		if ( $first ) {
			unset( $select[ --$index ] );
			array_unshift( $select, $first );
		}

		return implode( ',', $select );
	}

	/**
	 * Build the final query.
	 *
	 * @access private
	 */
	private function build_query() {
		$args       = $this->_params;

		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Building query with params %s', print_r( $args, true ) ), 'debug', __FILE__, __LINE__ );

		$table      = $args['from'];
		$tables     = array( $table );
		$mapping    = self::get_db_table_mapping();
		if ( array_key_exists( $table, $mapping ) ) {
			$tables[] = $mapping[ $table ];
		}

		$cols       = array();
		foreach ( $tables as $table ) {
			$cols[ $table ] = self::get_db_table_columns( $table, count( $tables ) > 1 );
		}

		if ( ! isset( $args['select'] ) ) {
			return;
		}

		$query      = '';
		$select     = 'SELECT ' . $this->rearrange_columns_for_x_axis( $cols, $args['select'], $tables );
		$from       = ' FROM ' . vsprintf( implode( ',', array_fill( 0, count( $tables ), '%s' ) ), $tables );
		$group      = '';
		$order      = '';
		$limit      = '';
		$fk         = '';
		$where      = array();

		if ( isset( $args['group'] ) ) {
			$group  = ' GROUP BY ' . implode( ', ', $args['group'] );
		}
		if ( isset( $args['order'] ) ) {
			$order  = ' ORDER BY ' . implode( ', ', $args['order'] );
		}
		if ( ! empty( $args['limit'] ) ) {
			$limit  = ' LIMIT ' . $args['limit'];
		}

		$index      = 0;
		if ( ! empty( $args['where'] ) ) {
			$scraps     = array();
			foreach ( $args['where'] as $column ) {
				if ( empty( $column ) ) {
					continue;
				}
				if ( strpos( $column, '.' ) !== false ) {
					$table  = strstr( $column, '.', true );
				} else {
					$table  = $args['from'];
				}
				$scraps[] = array(
					'table'     => $table,
					'col'       => $column,
					'index'     => $index++,
				);
			}

			if ( $scraps ) {
				foreach ( $scraps as $scrap ) {
					foreach ( $cols[ $scrap['table'] ] as $col ) {
						if ( $scrap['col'] === $col['name'] ) {
							$operator   = $args[ $col['type'] . '-operator' ][ $scrap['index'] ];
							$operand    = $args[ $col['type'] ][ $scrap['index'] ];
							$where[]    = $scrap['col'] . " $operator '$operand'";
						}
					}
				}
			}
		}

		if ( count( $tables ) > 1 ) {
			$fk         = $this->get_foreign_key_constraint( $tables[0], $tables[1] );
			if ( ! empty( $fk ) ) {
				$where[]    = $fk;
			}
		}

		if ( empty( $where ) ) {
			$where      = '';
		} else {
			$where      = ' WHERE ' . implode( ' AND ', $where );
		}

		$query          = $select . $from . $where . $group . $order . $limit;
		$this->_query   = $query;

		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Firing query: %s', $this->_query ), 'debug', __FILE__, __LINE__ );
	}

	/**
	 * Gets the relationship between tables in the database.
	 *
	 * @access public
	 * @return array
	 */
	public static function get_db_table_mapping() {
		global $wpdb;
		$mapping = get_transient( 'visualizer_db_table_mapping' );
		if ( $mapping ) {
			return $mapping;
		}
		// no need to provide x=>y and then y=>x as we will flip the array shortly.
		$mapping = array(
			$wpdb->prefix . 'posts' => $wpdb->prefix . 'postmeta',
			$wpdb->prefix . 'users' => $wpdb->prefix . 'usermeta',
			$wpdb->prefix . 'terms' => $wpdb->prefix . 'termmeta',
			$wpdb->prefix . 'comments' => $wpdb->prefix . 'commentmeta',
		);
		$mapping = apply_filters( 'visualizer_db_table_mapping', $mapping );
		$mapping += array_flip( $mapping );
		set_transient( 'visualizer_db_table_mapping', $mapping, HOUR_IN_SECONDS );
		return $mapping;
	}

	/**
	 * Returns the foreign key relationship constraint between the tables.
	 *
	 * @param string $table1 Table 1.
	 * @param string $table2 Table 2.
	 * @access private
	 * @return string
	 */
	private function get_foreign_key_constraint( $table1, $table2 ) {
		global $wpdb;

		$posts = array( $wpdb->prefix . 'posts', $wpdb->prefix . 'postmeta' );
		$users = array( $wpdb->prefix . 'users', $wpdb->prefix . 'usermeta' );
		$terms = array( $wpdb->prefix . 'terms', $wpdb->prefix . 'termmeta' );
		$comments = array( $wpdb->prefix . 'comments', $wpdb->prefix . 'commentmeta' );

		if ( in_array( $table1, $posts, true ) && in_array( $table2, $posts, true ) ) {
			return $wpdb->prefix . 'posts.ID = ' . $wpdb->prefix . 'postmeta.post_id';
		}
		if ( in_array( $table1, $users, true ) && in_array( $table2, $users, true ) ) {
			return $wpdb->prefix . 'users.ID = ' . $wpdb->prefix . 'usermeta.user_id';
		}
		if ( in_array( $table1, $terms, true ) && in_array( $table2, $terms, true ) ) {
			return $wpdb->prefix . 'terms.term_id = ' . $wpdb->prefix . 'termmeta.term_id';
		}
		if ( in_array( $table1, $comments, true ) && in_array( $table2, $comments, true ) ) {
			return $wpdb->prefix . 'comments.comment_id = ' . $wpdb->prefix . 'commentmeta.comment_id';
		}

		return apply_filters( 'visualizer_db_fk_constraint', '', $table1, $table2 );
	}

	/**
	 * Gets the column information for the table.
	 *
	 * @param string $table The table.
	 * @param bool   $prefix_with_table Whether to prefix column name with the name of the table.
	 * @access private
	 * @return array
	 */
	private static function get_db_table_columns( $table, $prefix_with_table = false ) {
		global $wpdb;
		$columns    = get_transient( "visualizer_db_{$table}_columns" );
		if ( $columns ) {
			return $columns;
		}
		$columns    = array();
		// @codingStandardsIgnoreStart
		$rows       = $wpdb->get_results( "SHOW COLUMNS IN `$table`", ARRAY_N );
		// @codingStandardsIgnoreEnd
		if ( $rows ) {
			// n => numeric, d => date-ish, s => string-ish.
			foreach ( $rows as $row ) {
				$col        = ( $prefix_with_table ? "$table." : '' ) . $row[0];
				$type       = $row[1];
				if ( strpos( $type, 'int' ) !== false || strpos( $type, 'float' ) !== false ) {
					$type   = 'n';
				} elseif ( strpos( $type, 'date' ) !== false || strpos( $type, 'time' ) !== false ) {
					$type   = 'd';
				} else {
					$type   = 's';
				}
				$columns[]  = array( 'name' => $col, 'type' => $type );
			}
		}
		$mapping    = apply_filters( 'visualizer_db_table_columns', $columns, $table );
		set_transient( "visualizer_db_{$table}_columns", $columns, DAY_IN_SECONDS );
		return $columns;
	}

	/**
	 * Gets the dependent tables and then gets column information for all the tables.
	 *
	 * @param string $table The table.
	 * @access public
	 * @return array
	 */
	public static function get_table_columns( $table ) {
		$columns    = array();
		if ( ! $table ) {
			return $columns;
		}

		$tables = array( $table );
		$mapping = Visualizer_Source_Query_Params::get_db_table_mapping();
		if ( array_key_exists( $table, $mapping ) ) {
			$tables[] = $mapping[ $table ];
		}
		foreach ( $tables as $table ) {
			$cols = self::get_db_table_columns( $table, count( $tables ) > 1 );
			$columns = array_merge( $columns, $cols );
		}
		return $columns;
	}

	/**
	 * Gets the tables in the database;
	 *
	 * @access public
	 * @return array
	 */
	public static function get_db_tables() {
		global $wpdb;
		$tables = get_transient( 'visualizer_db_tables' );
		if ( $tables ) {
			return $tables;
		}

		$prefix = apply_filters( 'visualizer_db_prefix', $wpdb->prefix );
		$sql    = $wpdb->get_col( 'SHOW TABLES', 0 );
		foreach ( $sql as $table ) {
			if ( empty( $prefix ) || 0 === strpos( $table, $prefix ) ) {
				$tables[] = $table;
			}
		}
		$tables = apply_filters( 'visualizer_db_tables', $tables );
		set_transient( 'visualizer_db_tables', $tables, HOUR_IN_SECONDS );
		return $tables;
	}

	/**
	 * Gets all tables and their columns.
	 *
	 * @access public
	 * @return array
	 */
	public static function get_all_db_tables_column_mapping() {
		$mapping    = array();
		$tables     = self::get_db_tables();
		foreach ( $tables as $table ) {
			$cols   = self::get_db_table_columns( $table, true );
			$names  = wp_list_pluck( $cols, 'name' );
			$mapping[ $table ] = $names;
		}
		return $mapping;
	}


	/**
	 * Returns source name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string The name of source.
	 */
	public function getSourceName() {
		return __CLASS__;
	}
}
