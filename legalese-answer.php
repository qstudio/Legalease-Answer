<?php
/**
 * Plugin Name: Legalese Answer
 * Plugin URL: https://legal500.com
 * Description: Legalese Answer Plugin for Legalease.com
 * Author: Ryan Syntax
 * Author URI: https://ryansyntax.com
 * Version: 1.1
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
    /**
	 * main
	 *
	 * Pulls core functions into use
	 *
	 * @date	27/4/21
	 * @since	1.1
	 *
	 * @param	void
	 * @return	void
	 */
    public function main() {
		// Check post data
		$this->post_data_check();

		// Check virtual page
		$this->virtual_page();

		// Adds new fields to profile page
		$this->profile_additions();

		// Pull in rest calls
		$this->rest_api();
    } 

	/**
	 * post_data_check
	 *
	 * Checks the post data for inputs
	 *
	 * @date	27/4/21
	 * @since	1.1
	 *
	 * @param	void
	 * @return	void
	 */
	public function post_data_check() {
		if (is_user_logged_in()) {
			if (isset($_POST['legalese_answer_value'])) {
				$le_answer = (string) $_POST['legalese_answer_value'];
				// Validation rules
				$val_args = ['maxlen' => 40];
				
				if ($this->validation($val_args, $le_answer)) {
					$this->update_answer_field( get_current_user_id(), $le_answer);
				}
			}
		}
	}

	/**
	 * validation
	 *
	 * Checks variables are using correct characters
	 *
	 * @date	27/4/21
	 * @since	1.1
	 *
	 * @param	array $args The array of validation arguments to pass
	 * @return	bool
	 */
	public function validation($args, $value) {
		// Max character length
		if (isset($args['maxlen'])) {
			if ( strlen($value) > $args['maxlen']) {
				$fail_count++;
			}
		}
		// Regex categories
		if (isset($args['chars'])) {
			if ($args['chars'] == 'number') {
				// Checks if value is numeric
				if (!is_numeric($value)) {
					$fail_count++;
				}
			} elseif ($args['chars'] == 'alphabet') {
				// Checks if value is alphabetical
				if (!ctype_alpha($value)) {
					$fail_count++;
				}
			}
		}

		if ($fail_count > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * virtual_page
	 *
	 * Creates path to virtual page legalese-answer
	 *
	 * @date	27/4/21
	 * @since	1.1
	 *
	 * @param	void
	 * @return	void
	 */
	public function virtual_page() {
		// Rewrite Path
		add_rewrite_rule('^legalese-answer/?', 'index.php?legalese-answer=virtual_page', 'top');
		
		// Check for variables
		add_filter('query_vars', function( $query_vars ){
			$query_vars[] = 'legalese-answer';
			return $query_vars;
		});
		// Redirect to template
		add_action('template_redirect', 'virtual_legalese_answer_page');
		
		/**
	 	* virtual_legalese_answer_page
	 	*
	 	* Displays legalese-answer template page when criteria is met
	 	*
	 	* @date	27/4/21
	 	* @since	1.1
	 	*
	 	* @param	void
	 	* @return	void
	 	*/
		function virtual_legalese_answer_page(){
			$custom =  get_query_var('legalese-answer');
			if ( $custom ) {
				include plugin_dir_path( __FILE__ ) . 'templates/legalese-answer-page.php';
				die;
			}
		}
	}

	/**
	 * profile_additions
	 *
	 * Adds additional values to admin/profile page
	 *
	 * @date	27/4/21
	 * @since	1.1
	 *
	 * @param	void
	 * @return	void
	 */
	public function profile_additions() {
		// Show user profile page
		add_action( 'show_user_profile', 'legalese_answer_field' );

		// Edit user profile page
		add_action( 'edit_user_profile', 'legalese_answer_field');

		/**
	 	* legalese_answer_field
	 	*
	 	* Displays html form table for legalese_answer
	 	*
	 	* @date	27/4/21
	 	* @since	1.1
		*
	 	* @param	void
	 	* @return	void
	 	*/
		function legalese_answer_field() {
			$meta = get_user_meta( get_current_user_id() ); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr class="user-url-wrap">
					<th><label for="url">Legalese Answer</label></th>
					<td><input type="text" name="legalese_answer" id="legalese_answer_field" value="<?php the_field('legalese_answer', 'user_'. get_current_user_id() ); ?>" class="regular-text code"></td>
					</tr>
				</tbody>
			</table>
			<?php 
		}
		

		// Allow save of additional field
		add_action( 'personal_options_update', 'save_extra_user_profile_field' );
		add_action( 'edit_user_profile_update', 'save_extra_user_profile_field' );

		/**
	 	* save_extra_user_profile_field
	 	*
	 	* Saves the additional profile field on edit success
	 	*
	 	* @date	27/4/21
	 	* @since	1.1
		 *
	 	* @param	$user_id
	 	* @return	void
	 	*/
		function save_extra_user_profile_field( $user_id ) {

			if ( !current_user_can( 'edit_user', $user_id ) ) { 
				return false; 
			}
			// Push update of answer
			update_user_meta( $user_id, 'legalese_answer', $_POST['legalese_answer'] );
		}

	}
	/**
	 * rest_api
	 *
	 * Deals with Rest API Requests
	 *
	 * @date	27/4/21
	 * @since	1.1
	 *
	 * @param	void
	 * @return	void
	 */
	public function rest_api() {
		// Preparing to serve a REST API request
		add_action( 'rest_api_init', 'adding_user_meta');

		/**
	 	* adding_user_meta
	 	*
	 	* Adds user meta 
	 	*
	 	* @date	27/4/21
	 	* @since	1.1
	 	*
	 	* @param	void
	 	* @return	void
	 	*/
		function adding_user_meta() {
			register_rest_field( 'user',
								 'collapsed_widgets',
								  array(
									'get_callback'      => 'user_meta_call',
									'update_callback'   => null,
									'schema'            => null,
									 )
							   );
		}
		/**
	 	* user_meta_call
	 	*
	 	* Callback of meta value
	 	*
	 	* @date	27/4/21
	 	* @since	1.1
	 	*
	 	* @param	string $user
		* @param	string $field_name
		* @param	string $request
	 	* @return	string
	 	*/
		function user_meta_call( $user, $field_name, $request) {
			return get_user_meta( $user[ 'id' ], $field_name, true );
		}
	}


 	/**
	 * update_answer_field
	 *
	 * Updates the answer value for set user
	 *
	 * @date	27/4/21
	 * @since	1.1
	 *
	 * @param	int $user_id The user ID
	 * @param	string $value The answer value
	 * @return	void
	 */
	 public function update_answer_field($user_id, $value) {
		// Typecast inputs
		$user_id = (int) $user_id;
		$post_id = (string) 'user_' . $user_id;
		$value = (string) $value;
	
		// Update field using ACF function
		update_field('legalese_answer', $value, $post_id);
	}
}

// Load Class
new LegaleseAnswer;
