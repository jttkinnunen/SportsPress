<?php
/**
 * SportsPress Team Settings
 *
 * @author 		ThemeBoy
 * @category 	Admin
 * @package 	SportsPress/Admin
 * @version     0.7
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'SP_Settings_Teams' ) ) :

/**
 * SP_Settings_Teams
 */
class SP_Settings_Teams extends SP_Settings_Page {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id    = 'teams';
		$this->label = __( 'Teams', 'sportspress' );

		add_filter( 'sportspress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'sportspress_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'sportspress_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = array(

			array(	'title' => __( 'Team Options', 'sportspress' ), 'type' => 'title','desc' => '', 'id' => 'team_options' ),

			array( 'type' => 'sectionend', 'id' => 'team_options' ),

			array( 'title' => __( 'League Tables', 'sportspress' ), 'type' => 'title', 'id' => 'table_options' ),
			
			array(
				'title'     => __( 'Teams', 'sportspress' ),
				'desc' 		=> __( 'Display logos', 'sportspress' ),
				'id' 		=> 'sportspress_table_show_logos',
				'default'	=> 'yes',
				'type' 		=> 'checkbox',
				'checkboxgroup'	=> 'start',
			),

			array(
				'desc' 		=> __( 'Link teams', 'sportspress' ),
				'id' 		=> 'sportspress_table_link_teams',
				'default'	=> 'no',
				'type' 		=> 'checkbox',
				'checkboxgroup'		=> 'end',
			),

			array(
				'title'     => __( 'Pagination', 'sportspress' ),
				'desc' 		=> __( 'Paginate', 'sportspress' ),
				'id' 		=> 'sportspress_table_paginated',
				'default'	=> 'yes',
				'type' 		=> 'checkbox',
			),
			
			array(
				'title' 	=> __( 'Limit', 'sportspress' ),
				'id' 		=> 'sportspress_table_rows',
				'class' 	=> 'small-text',
				'default'	=> '10',
				'desc' 		=> __( 'teams', 'sportspress' ),
				'type' 		=> 'number',
				'custom_attributes' => array(
					'min' 	=> 1,
					'step' 	=> 1
				),
			),

			array( 'type' => 'sectionend', 'id' => 'table_options' ),

			array( 'title' => __( 'Text', 'sportspress' ), 'type' => 'title', 'desc' => __( 'The following options affect how words are displayed on the frontend.', 'sportspress' ), 'id' => 'text_options' ),

		);

		$strings = sp_get_text_options();

		foreach ( sp_array_value( $strings, 'team', array() ) as $key => $value ):
			$settings[] = array(
				'title'   => $value,
				'id'      => 'sportspress_team_' . $key . '_text',
				'default' => '',
				'placeholder' => $value,
				'type'    => 'text',
			);
		endforeach;

		$settings[] = array( 'type' => 'sectionend', 'id' => 'text_options' );

		return apply_filters( 'sportspress_event_settings', $settings ); // End team settings
	}
}

endif;

return new SP_Settings_Teams();
