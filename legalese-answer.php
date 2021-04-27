<?php
/**
 * Plugin Name: Legalese Answer
 * Plugin URL: https://legal500.com
 * Description: Legalese Answer Plugin for Legalease.com
 * Author: Ryan Syntax
 * Author URI: https://ryansyntax.com
 * Version: 1.0
 * Text Domain: legalese-answer
 */
 
 if (!defined('ABSPATH')) {
 	exit;
 }
 
 class LegaleseAnswer {
 
 	public function __construct() {
		 // init main
    	add_action('init', array($this, 'main'));
    }
    
    public function main() {
		// Check post data
		$this->post_data_check();

		// Check virtual page
		$this->virtual_page();

		// Adds new fields to profile page
		$this->profile_additions();

		// Rest Calls (WIP)
		//$this->rest_api();
    } 

	// Check if post data
	public function post_data_check() {
		if (is_user_logged_in()) {
			if (isset($_POST['legalese_answer_value'])) {
				$le_answer = (string) $_POST['legalese_answer_value'];
				if ($this->validation($le_answer)) {
					$this->update_answer_field( get_current_user_id(), $le_answer);
				}
			}
		}
	}

	// Variable validation checks
	public function validation($args) {
		// Max character length
		if (isset($args['maxlen'])) {
			if ( strlen($args['value']) > $args['maxlen']) {
				$fail_count++;
			}
		}
		// Regex categories
		if (isset($args['chars'])) {
			if ($args['chars'] == 'num') {

			} elseif ($args['chars'] == 'letter') {

			}
		}

		if ($fail_count > 0) {
			return false;
		} else {
			return true;
		}
	}

	// Setup virtual page
	public function virtual_page() {
		// Rewrite Path
		add_rewrite_rule('^legalese-answer/?', 'index.php?legalese-answer=virtual_page', 'top');
		
		// Check for variables
		add_filter('query_vars', function( $query_vars ){
			$query_vars[] = 'legalese-answer';
			return $query_vars;
		});
		// Redirect to template
		add_action('template_redirect', function(){
			$custom =  get_query_var('legalese-answer');
			if ( $custom ) {
				include plugin_dir_path( __FILE__ ) . 'templates/legalese-answer-page.php';
				die;
			}
		});
	}

	// Profile page adaptations
	public function profile_additions() {
		// Show user profile page
		add_action( 'show_user_profile', function() {
			$meta = get_user_meta( get_current_user_id() );
			echo 'show profile'; ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr class="user-url-wrap">
					<th><label for="url">Legalese Answer</label></th>
					<td><input type="text" name="legalese_answer" id="legalese_answer_field" value="<?php the_field('legalese_answer', 'user_'. get_current_user_id() ); ?>" class="regular-text code"></td>
					</tr>
				</tbody>
			</table>
			<?php 
		} );
		// Edit user profile page
		add_action( 'edit_user_profile', function() {
			echo 'edit profile hook';
		} );

		// Allow save of additional field
		add_action( 'personal_options_update', 'save_extra_user_profile_field' );
		add_action( 'edit_user_profile_update', 'save_extra_user_profile_field' );
		// Save answer field behaviour
		function save_extra_user_profile_field( $user_id ) {

			if ( !current_user_can( 'edit_user', $user_id ) ) { 
				return false; 
			}
			// Push update of answer
			update_user_meta( $user_id, 'legalese_answer', $_POST['legalese_answer'] );
		}

	}
	// Rest catch
	public function rest_api() {
		// Preparing to serve a REST API request
		add_action( 'rest_api_init', 'adding_user_meta');

		function adding_user_meta() {
			register_rest_field( 'user',
								 'collapsed_widgets',
								  array(
									'get_callback'      => 'user_meta_callback',
									'update_callback'   => null,
									'schema'            => null,
									 )
							   );
		 }

		// Callback of Meta Value
		function user_meta_call( $user, $field_name, $request) {
			return get_user_meta( $user[ 'id' ], $field_name, true );
		}
	}


 	// Update new value to legalese answer
	 public function update_answer_field($user_id, $value) {
		// Typecast Inputs
		$user_id = (int) $user_id;
		$post_id = (string) 'user_' . $user_id;
		$value = (string) $value;
	
		// Update Field using ACF Function
		update_field('legalese_answer', $value, $post_id);
	}
}

// Load class once
//if( !class_exists('LegaleseAnswer') ) {
	new LegaleseAnswer;
//}