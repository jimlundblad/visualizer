<?php

class Visualizer_Module_Builder extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addAjaxAction( Visualizer_Plugin::ACTION_CREATE_CHART, 'renderChartPages' );
	}

	/**
	 * Renders appropriate page for chart builder. Creates new auto draft chart
	 * if no chart has been specified.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function renderChartPages() {
		// check chart, if chart not exists, will create new one and redirects to the same page with proper chart id
		$chart_id = filter_input( INPUT_GET, 'chart', FILTER_VALIDATE_INT );
		if ( !$chart_id || !( $chart = get_post( $chart_id ) ) || $chart->post_type != Visualizer_Plugin::CPT_VISUALIZER ) {
			$chart_id = wp_insert_post( array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_title'  => 'Visualization',
				'post_author' => get_current_user_id(),
				'post_status' => 'auto-draft',
			) );

			if ( $chart_id && !is_wp_error( $chart_id ) ) {
				add_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );
			}

			wp_redirect( add_query_arg( 'chart', (int)$chart_id ) );
			exit;
		}

		// creates a render object and renders page
		switch ( filter_input( INPUT_GET, 'tab' ) ) {
			case 'data':
				$render = new Visualizer_Render_Page_Data();
				break;
			case 'settings':
				$render = new Visualizer_Render_Page_Settings();
				$render->type = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
				break;
			case 'type':
			default:
				$render = new Visualizer_Render_Page_Types();
				$render->type = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
				$render->types = Visualizer_Plugin::getChartTypes();
				break;
		}

		$render->chart = $chart;
		$render->render();

		exit;
	}

}