<?php


namespace WSAL\MainWPExtension\Views;

use function WSAL\MainWPExtension\mwpal_extension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/admin.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

abstract class AuditLogView extends \WP_List_Table {

	/**
	 * GMT Offset
	 *
	 * @var int
	 */
	protected $gmt_offset_sec = 0;

	/**
	 * Datetime Format
	 *
	 * @var string
	 */
	protected $datetime_format;

	/**
	 * MainWP Child Sites
	 *
	 * @var array
	 */
	protected $mwp_child_sites;

	/**
	 * Events Meta.
	 *
	 * @since 1.1
	 *
	 * @var array
	 */
	protected $item_meta = array();

	/**
	 * Events Query Arguments.
	 *
	 * @since 1.1
	 *
	 * @var stdClass
	 */
	protected $query_args;

	/**
	 * Constructor.
	 *
	 * @param stdClass $query_args - Events query arguments.
	 */
	public function __construct( $query_args ) {
		$this->query_args = $query_args;
		$settings         = mwpal_extension()->settings;

		$timezone = $settings->get_timezone(); // Set GMT offset.
		if ( 'utc' === $timezone ) {
			$this->gmt_offset_sec = date( 'Z' );
		} else {
			$this->gmt_offset_sec = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		}

		// Get MainWP child sites.
		$this->mwp_child_sites = $settings->get_mwp_child_sites();

		parent::__construct(
			array(
				'singular' => 'activity-log',
				'plural'   => 'activity-logs',
				'ajax'     => true,
				'screen'   => 'interval-list',
			)
		);
	}

	protected function get_view_types() {
		return [
			'list' => esc_html__( 'List View', 'mwp-al-ext' ),
			'grid' => esc_html__( 'Grid View', 'mwp-al-ext' )
		];
	}

	/**
	 * Provides access to private query args property.
	 *
	 * @return stdClass
	 */
	public function get_query_args() {
		return $this->query_args;
	}

	public function set_item_meta( $item_id, $data ) {
		$this->item_meta[ $item_id ] = $data;
	}

	/**
	 * Empty View.
	 */
	public function no_items() {
		esc_html_e( 'No events so far.', 'mwp-al-ext' );
	}

	/**
	 * Method: Prepare items.
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$query_events = $this->query_events();
		$this->items  = isset( $query_events['items'] ) ? $query_events['items'] : false;
		$total_items  = isset( $query_events['total_items'] ) ? $query_events['total_items'] : false;
		$per_page     = isset( $query_events['per_page'] ) ? $query_events['per_page'] : false;

		if ( ! mwpal_extension()->settings->is_infinite_scroll() ) {
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page ),
				)
			);
		}
	}

	/**
	 * Query Events from WSAL DB.
	 *
	 * @param integer $paged - Page number.
	 *
	 * @return array
	 * @since 1.1
	 *
	 */
	public function query_events( $paged = 0 ) {
		// Query for events.
		$events_query = new \WSAL\MainWPExtension\Models\OccurrenceQuery();

		// Get site id for specific site events.
		$bid = $this->query_args->site_id;
		if ( $bid && 'dashboard' !== $bid ) {
			$events_query->addCondition( 'site_id = %s ', $bid );
		} elseif ( 'dashboard' === $bid ) {
			$events_query->addCondition( 'site_id = %s ', '0' );
		}

		/**
		 * Filter: `mwpal_auditlog_query`
		 *
		 * This filter can be used to modify the query for events.
		 * It is helpful while performing search operations on the
		 * audit log events.
		 *
		 * @param \WSAL\MainWPExtension\Models\OccurrenceQuery $events_query - Occurrence query instance.
		 */
		$events_query = apply_filters( 'mwpal_auditlog_query', $events_query );

		if ( ! mwpal_extension()->settings->is_infinite_scroll() ) {
			$total_items = $events_query->getAdapter()->Count( $events_query );
			$per_page    = mwpal_extension()->settings->get_view_per_page();
			$offset      = ( $this->get_pagenum() - 1 ) * $per_page;
		} else {
			$total_items = false;
			$per_page    = 25; // Manually set per page events for infinite scroll.
			$offset      = ( max( 1, $paged ) - 1 ) * $per_page;
		}

		// Set query order arguments.
		$order_by = isset( $this->query_args->order_by ) ? $this->query_args->order_by : false;
		$order    = isset( $this->query_args->order ) ? $this->query_args->order : false;

		if ( ! $order_by ) {
			$events_query->addOrderBy( 'created_on', true );
		} else {
			$is_descending = true;
			if ( ! empty( $order ) && 'asc' === $order ) {
				$is_descending = false;
			}

			// TO DO: Allow order by meta values.
			if ( 'scip' === $order_by ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'ClientIP' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "ClientIP" THEN meta.value END', $is_descending );
			} elseif ( 'user' === $order_by ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'CurrentUserID' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "CurrentUserID" THEN meta.value END', $is_descending );
			} elseif ( 'event_type' === $order_by ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'EventType' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "EventType" THEN meta.value END', $is_descending );
			} elseif ( 'object' === $order_by ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'Object' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "Object" THEN meta.value END', $is_descending );
			} else {
				$tmp = new \WSAL\MainWPExtension\Models\Occurrence();
				// Making sure the field exists to order by.
				if ( isset( $tmp->{$order_by} ) ) {
					// TODO: We used to use a custom comparator ... is it safe to let MySQL do the ordering now?.
					$events_query->addOrderBy( $order_by, $is_descending );

				} else {
					$events_query->addOrderBy( 'created_on', true );
				}
			}
		}

		$events_query->setOffset( $offset );  // Set query offset.
		$events_query->setLimit( $per_page ); // Set number of events per page.

		if ( ! in_array( $bid, [ 'dashboard', 0 ] ) ) {
			$result = apply_filters( 'mwpal_query_events', [], $this, $per_page, $offset );
			//  if there are no events, the filter is still expected to return na array with key total_items, per_page and items
			if ( ! empty( $result ) ) {
				return $result;
			}
		}

		return array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'items'       => $events_query->getAdapter()->Execute( $events_query ),
		);
	}

	/**
	 * Method: Get checkbox column.
	 *
	 * @param object $item - Item.
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" value="' . $item->id . '" name="' . esc_attr( $this->_args['singular'] ) . '[]" />';
	}

	/**
	 * Adds some classes to the table.
	 *
	 * @method get_table_classes
	 * @return array
	 * @since  1.4.0
	 */
	protected function get_table_classes() {
		return array(
			'widefat',
			'fixed',
			'striped',
			$this->_args['plural'],
			'almwp-table',
			'almwp-table-' . $this->get_view_type()
		);
	}

	abstract protected function get_view_type();

	/**
	 * Table navigation.
	 *
	 * @param string $which - Position of the nav.
	 */
	public function extra_tablenav( $which ) {
		// If the position is not top then render.
		if ( 'top' !== $which && ! mwpal_extension()->settings->is_infinite_scroll() ) :
			// Items-per-page widget.
			$per_page = mwpal_extension()->settings->get_view_per_page();
			$items    = array( 5, 10, 15, 30, 50 );
			if ( ! in_array( $per_page, $items, true ) ) {
				$items[] = $per_page;
			}
			?>
			<div class="mwp-ipp mwp-ipp-<?php echo esc_attr( $which ); ?>">
				<?php esc_html_e( 'Show ', 'mwp-al-ext' ); ?>
				<select class="mwp-ipps">
					<?php foreach ( $items as $item ) { ?>
						<option
							value="<?php echo is_string( $item ) ? '' : esc_attr( $item ); ?>"
							<?php echo ( $item === $per_page ) ? 'selected="selected"' : false; ?>
						>
							<?php echo esc_html( $item ); ?>
						</option>
					<?php } ?>
				</select>
				<?php esc_html_e( ' Items', 'mwp-al-ext' ); ?>
			</div>
		<?php
		endif;

		if ( 'top' !== $which && mwpal_extension()->settings->is_infinite_scroll() ) :
			?>
			<div id="mwpal-auditlog-end"><p><?php esc_html_e( '— End of Activity Log —', 'mwp-al-ext' ); ?></p></div>
			<div id="mwpal-event-loader"><div class="mwpal-lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>
		<?php
		endif;

		if ( 'top' === $which ) :
			// Get child sites with WSAL installed.
			$wsal_child_sites = mwpal_extension()->settings->get_wsal_child_sites();
			if ( count( $wsal_child_sites ) > 0 ) :
				$current_site = mwpal_extension()->settings->get_view_site_id();
				?>
				<div class="mwp-ssa mwp-ssa-<?php echo esc_attr( $which ); ?>">
					<select class="mwp-ssas">
						<option value="0"><?php esc_html_e( 'All Sites', 'mwp-al-ext' ); ?></option>
						<option value="dashboard" <?php selected( $current_site, 'dashboard' ); ?>><?php esc_html_e( 'MainWP Dashboard', 'mwp-al-ext' ); ?></option>
						<?php
						if ( is_array( $wsal_child_sites ) ) {
							foreach ( $wsal_child_sites as $site_id => $site_data ) {
								$key = array_search( $site_id, array_column( $this->mwp_child_sites, 'id' ), false );
								if ( false !== $key ) {
									?>
									<option value="<?php echo esc_attr( $this->mwp_child_sites[ $key ]['id'] ); ?>"
										<?php selected( (int) $this->mwp_child_sites[ $key ]['id'], $current_site ); ?>>
										<?php echo esc_html( $this->mwp_child_sites[ $key ]['name'] ) . ' (' . esc_html( $this->mwp_child_sites[ $key ]['url'] ) . ')'; ?>
									</option>
									<?php
								}
							}
						}
						?>
					</select>
					<input type="button" class="almwp-button" id="mwpal-wsal-manual-retrieve" value="<?php esc_html_e( 'Retrieve Activity Logs Now', 'mwp-al-ext' ); ?>" />
				</div>
			<?php
			endif;
			?>
			<div class="display-type-buttons">
				<?php foreach ( $this->get_view_types() as $view_type => $view_name ): ?>
					<?php if ( $this->get_view_type() !== $view_type ): ?>
						<a href="<?php echo esc_url( add_query_arg( 'view', $view_type ) ); ?>" class="almwp-button dashicons-before dashicons-list-view almwp-list-view-toggle"><?php echo $view_name; ?></a>
					<?php else: ?>
						<span class="almwp-button dashicons-before dashicons-grid-view almwp-grid-view-toggle disabled"><?php echo $view_name; ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php
		endif;
	}
}
