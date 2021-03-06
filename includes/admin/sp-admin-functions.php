<?php
/**
 * SportsPress Admin Functions
 *
 * @author      ThemeBoy
 * @category    Core
 * @package     SportsPress/Admin/Functions
 * @version     0.7
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get all SportsPress screen ids
 *
 * @return array
 */
function sp_get_screen_ids() {
    return apply_filters( 'sportspress_screen_ids', array(
        'dashboard_page_sp-about',
        'toplevel_page_sportspress',
        'sportspress_page_sp-status',
    	'edit-sp_result',
    	'sp_result',
    	'edit-sp_outcome',
    	'sp_outcome',
    	'edit-sp_performance',
    	'sp_performance',
    	'edit-sp_column',
    	'sp_column',
    	'edit-sp_metric',
    	'sp_metric',
    	'edit-sp_statistic',
    	'sp_statistic',
    	'edit-sp_event',
    	'sp_event',
    	'edit-sp_calendar',
    	'sp_calendar',
    	'edit-sp_team',
    	'sp_team',
    	'edit-sp_table',
    	'sp_table',
    	'edit-sp_player',
    	'sp_player',
    	'edit-sp_list',
    	'sp_list',
    	'edit-sp_staff',
    	'sp_staff',
    	'edit-sp_venue',
    	'edit-sp_league',
    	'edit-sp_season',
    	'edit-sp_position',
    ) );
}
