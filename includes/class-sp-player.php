<?php
/**
 * Player Class
 *
 * The SportsPress player class handles individual player data.
 *
 * @class 		SP_Player
 * @version		0.8.1
 * @package		SportsPress/Classes
 * @category	Class
 * @author 		ThemeBoy
 */
class SP_Player extends SP_Custom_Post {

	/**
	 * Returns past teams
	 *
	 * @access public
	 * @return array
	 */
	public function past_teams() {
		return get_post_meta( $this->ID, 'sp_past_team', false );
	}

	/**
	 * Returns formatted player metrics
	 *
	 * @access public
	 * @return array
	 */
	public function metrics( $neg = null ) {
		$metrics = (array)get_post_meta( $this->ID, 'sp_metrics', true );
		$metric_labels = (array)sp_get_var_labels( 'sp_metric', $neg );
		$data = array();
		foreach( $metric_labels as $key => $value ):
			$metric = sp_array_value( $metrics, $key, null );
			if ( $metric == null )
				continue;
			$data[ $value ] = sp_array_value( $metrics, $key, '&nbsp;' );
		endforeach;
		return $data;
	}

	/**
	 * Returns formatted data
	 *
	 * @access public
	 * @param int $league_id
	 * @param bool $admin
	 * @return array
	 */
	public function data( $league_id, $admin = false ) {

		$seasons = (array)get_the_terms( $this->ID, 'sp_season' );
		$metrics = (array)get_post_meta( $this->ID, 'sp_metrics', true );
		$stats = (array)get_post_meta( $this->ID, 'sp_statistics', true );
		$leagues = sp_array_value( (array)get_post_meta( $this->ID, 'sp_leagues', true ), $league_id, array() );
		$usecolumns = get_post_meta( $this->ID, 'sp_columns', true );

		// Get labels from performance variables
		$performance_labels = (array)sp_get_var_labels( 'sp_performance' );

		// Get labels from outcome variables
		$outcome_labels = (array)sp_get_var_labels( 'sp_outcome' );

		// Generate array of all season ids and season names
		$div_ids = array();
		$season_names = array();
		foreach ( $seasons as $season ):
			if ( is_object( $season ) && property_exists( $season, 'term_id' ) && property_exists( $season, 'name' ) ):
				$div_ids[] = $season->term_id;
				$season_names[ $season->term_id ] = $season->name;
			endif;
		endforeach;

		$data = array();

		// Get all seasons populated with data where available
		$data = sp_array_combine( $div_ids, sp_array_value( $stats, $league_id, array() ) );

		// Get equations from statistic variables
		$equations = sp_get_var_equations( 'sp_statistic' );

		// Initialize placeholders array
		$placeholders = array();

		foreach ( $div_ids as $div_id ):

			$totals = array( 'eventsattended' => 0, 'eventsplayed' => 0, 'eventsstarted' => 0, 'eventssubbed' => 0, 'streak' => 0, 'last5' => null, 'last10' => null );

			foreach ( $performance_labels as $key => $value ):
				$totals[ $key ] = 0;
			endforeach;

			foreach ( $outcome_labels as $key => $value ):
				$totals[ $key ] = 0;
			endforeach;

			// Initialize streaks counter
			$streak = array( 'name' => '', 'count' => 0, 'fire' => 1 );

			// Initialize last counters
			$last5 = array();
			$last10 = array();

			// Add outcome types to last counters
			foreach( $outcome_labels as $key => $value ):
				$last5[ $key ] = 0;
				$last10[ $key ] = 0;
			endforeach;

			// Get all events involving the team in current season
			$args = array(
				'post_type' => 'sp_event',
				'numberposts' => -1,
				'posts_per_page' => -1,
				'order' => 'ASC',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'sp_player',
						'value' => $this->ID
					),
					array(
						'key' => 'sp_format',
						'value' => 'league'
					)
				),
				'tax_query' => array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'sp_league',
						'field' => 'id',
						'terms' => $league_id
					),
					array(
						'taxonomy' => 'sp_season',
						'field' => 'id',
						'terms' => $div_id
					),
				)
			);
			$events = get_posts( $args );

			// Event loop
			foreach( $events as $event ):
				$results = (array)get_post_meta( $event->ID, 'sp_results', true );
				$team_performance = (array)get_post_meta( $event->ID, 'sp_players', true );

				// Add all team performance
				foreach ( $team_performance as $team_id => $players ):
					if ( array_key_exists( $this->ID, $players ) ):

						$player_performance = sp_array_value( $players, $this->ID, array() );

						foreach ( $player_performance as $key => $value ):
							if ( array_key_exists( $key, $totals ) ):
								$totals[ $key ] += $value;
							endif;
						endforeach;

						$team_results = sp_array_value( $results, $team_id, array() );

						// Find the outcome
						if ( array_key_exists( 'outcome', $team_results ) ):

							// Increment events attended
							$totals['eventsattended']++;

							// Continue with incrementing values if active in event
							if ( sp_array_value( $player_performance, 'status' ) != 'sub' || sp_array_value( $player_performance, 'sub', 0 ) ): 
								$totals['eventsplayed']++;

								if ( sp_array_value( $player_performance, 'status' ) == 'lineup' ):
									$totals['eventsstarted']++;
								elseif ( sp_array_value( $player_performance, 'status' ) == 'sub' && sp_array_value( $player_performance, 'sub', 0 ) ):
									$totals['eventssubbed']++;
								endif;

								$value = $team_results['outcome'];

								// Convert to array
								if ( ! is_array( $value ) ):
									$value = array( $value );
								endif;

								foreach( $value as $outcome ):
									if ( $outcome && $outcome != '-1' ):

										// Increment outcome count
										if ( array_key_exists( $outcome, $totals ) ):
											$totals[ $outcome ]++;
										endif;

										// Add to streak counter
										if ( $streak['fire'] && ( $streak['name'] == '' || $streak['name'] == $outcome ) ):
											$streak['name'] = $outcome;
											$streak['count'] ++;
										else:
											$streak['fire'] = 0;
										endif;

										// Add to last 5 counter if sum is less than 5
										if ( array_key_exists( $outcome, $last5 ) && array_sum( $last5 ) < 5 ):
											$last5[ $outcome ] ++;
										endif;

										// Add to last 10 counter if sum is less than 10
										if ( array_key_exists( $outcome, $last10 ) && array_sum( $last10 ) < 10 ):
											$last10[ $outcome ] ++;
										endif;
									endif;
								endforeach;
							endif;
						endif;
					endif;
				endforeach;
			endforeach;

			// Compile streaks counter and add to totals
			$args = array(
				'name' => $streak['name'],
				'post_type' => 'sp_outcome',
				'post_status' => 'publish',
				'posts_per_page' => 1
			);
			$outcomes = get_posts( $args );

			if ( $outcomes ):
				$outcome = reset( $outcomes );
				$totals['streak'] = $outcome->post_title . $streak['count'];
			endif;

			// Add last counters to totals
			$totals['last5'] = $last5;
			$totals['last10'] = $last10;

			// Add metrics to totals
			$totals = array_merge( $metrics, $totals );

			// Generate array of placeholder values for each league
			$placeholders[ $div_id ] = array();
			foreach ( $equations as $key => $value ):
				$placeholders[ $div_id ][ $key ] = sp_solve( $value['equation'], $totals, $value['precision'] );
			endforeach;

			foreach ( $performance_labels as $key => $label ):
				$placeholders[ $div_id ][ $key ] = sp_array_value( $totals, $key, 0 );
			endforeach;

		endforeach;

		// Get stats from statistic variables
		$stats = sp_get_var_labels( 'sp_statistic' );

		// Merge the data and placeholders arrays
		$merged = array();

		foreach( $placeholders as $season_id => $season_data ):

			if ( ! sp_array_value( $leagues, $season_id, 0 ) )
				continue;

			$season_name = sp_array_value( $season_names, $season_id, '&nbsp;' );

			$team_id = sp_array_value( $leagues, $season_id, array() );

			if ( ! $team_id || $team_id == '-1' )
				continue;

			$team_name = get_the_title( $team_id );
			
			if ( get_option( 'sportspress_player_link_teams', 'no' ) == 'yes' ? true : false ):
				$team_permalink = get_permalink( $team_id );
				$team_name = '<a href="' . $team_permalink . '">' . $team_name . '</a>';
			endif;

			// Add season name to row
			$merged[ $season_id ] = array(
				'name' => $season_name,
				'team' => $team_name
			);

			foreach( $season_data as $key => $value ):

				// Use static data if key exists and value is not empty, else use placeholder
				if ( array_key_exists( $season_id, $data ) && array_key_exists( $key, $data[ $season_id ] ) && $data[ $season_id ][ $key ] != '' ):
					$merged[ $season_id ][ $key ] = $data[ $season_id ][ $key ];
				else:
					$merged[ $season_id ][ $key ] = $value;
				endif;

			endforeach;

		endforeach;

		$columns = array_merge( $performance_labels, $stats );

		if ( $admin ):
			return array( $columns, $data, $placeholders, $merged, $leagues );
		else:
			if ( ! is_array( $usecolumns ) )
				$usecolumns = array();
			foreach ( $columns as $key => $label ):
				if ( ! in_array( $key, $usecolumns ) ):
					unset( $columns[ $key ] );
				endif;
			endforeach;
			$labels = array_merge( array( 'name' => SP()->text->string('Season'), 'team' => SP()->text->string('Team') ), $columns );
			$merged[0] = $labels;
			return $merged;
		endif;
	}

}
