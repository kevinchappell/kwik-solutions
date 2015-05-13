<?php

class K_SOLUTIONS_META extends KwikSolutions{

	public function __construct() {

		// Taxonomy Meta Fields
		// add_action( 'solution_categories_add_form_fields', array( $this, 'solution_categories_add_new_meta_field' ), 10, 2 );
		// add_action( 'edited_solution_categories', array( $this, 'save_solution_categories_custom_meta' ), 10, 2 );
		// add_action( 'create_solution_categories', array( $this, 'save_solution_categories_custom_meta' ), 10, 2 );
		// add_action( 'solution_categories_edit_form_fields', array( $this, 'solution_categories_edit_meta_field' ), 10, 2 );

		add_action( 'save_post_solutions', array( $this, 'save_solutions_meta' ), 1, 2);
	}

	// Add term page
	public function solution_categories_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
	?>
		<div class="form-field">
		  <label for="term_meta[fee]"><?php _e( 'Annual Fee', 'kwik' );?></label>
		  <input type="text" name="term_meta[fee][]" id="term_meta[fee]" value="">
		  <input type="text" name="term_meta[fee][]" id="term_meta[fee][1]" value="">
		  <p class="description"><?php _e( 'What is the Annual fee for this Membership Level?', 'kwik' );?></p>
		</div>
		<div class="form-field">
		  <label for="term_meta[fte]"><?php _e( 'FTEs', 'kwik' );?></label>
		  <input type="text" name="term_meta[fte]" id="term_meta[fte]" value="">
		  <p class="description"><?php _e( 'How many FTEs?', 'kwik' );?></p>
		</div>
		<div class="form-field">
		  <label for="term_meta[ipc]"><?php _e( 'IP Contribution', 'kwik' );?></label>
		  <input type="text" name="term_meta[ipc]" id="term_meta[ipc]" value="">
		  <p class="description"><?php _e( 'How much?', 'kwik' );?></p>
		</div>
		<div class="form-field">
		  <label for="term_meta[tsc]"><?php _e( 'Technical Steering Commitee', 'kwik' );?></label>
		  <input type="text" name="term_meta[tsc]" id="term_meta[tsc]" value="">
		  <p class="description"><?php _e( '', 'kwik' );?></p>
		</div>
		<div class="form-field">
		  <label for="term_meta[position]"><?php _e( 'Board/Voting Position', 'kwik' );?></label>
		  <input type="text" name="term_meta[position]" id="term_meta[position]" value="">
		  <p class="description"><?php _e( '', 'kwik' );?></p>
		</div>
	<?php
	}

  // Edit term page
  public function solution_categories_edit_meta_field($term) {

	// put the term ID into a variable
	$t_id = $term->term_id;

	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option("taxonomy_$t_id");?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[fee]"><?php _e( 'Annual Fee', 'kwik' );?></label></th>
	  <td>
		<input type="text" name="term_meta[fee][]" id="term_meta[fee]" value="<?php echo esc_attr($term_meta['fee'][0]) ? esc_attr($term_meta['fee'][0]) : '';?>">
		<input type="text" name="term_meta[fee][]" id="term_meta[fee][1]" value="<?php echo esc_attr($term_meta['fee'][1]) ? esc_attr($term_meta['fee'][1]) : '';?>">
		<p class="description"><?php _e( 'What is the Annual fee for this Membership Level?', 'kwik' );?></p>
	  </td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[fte]"><?php _e( 'FTEs', 'kwik' );?></label></th>
	  <td>
		<input type="text" name="term_meta[fte]" id="term_meta[fte]" value="<?php echo esc_attr($term_meta['fte']) ? esc_attr($term_meta['fte']) : '';?>">
		<p class="description"><?php _e( 'Enter the number of FTEs', 'kwik' );?></p>
	  </td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[ipc]"><?php _e( 'IP Contribution', 'kwik' );?></label></th>
	  <td>
		<input type="text" name="term_meta[ipc]" id="term_meta[ipc]" value="<?php echo esc_attr($term_meta['ipc']) ? esc_attr($term_meta['ipc']) : '';?>">
		<p class="description"><?php _e( 'How much for IPC?', 'kwik' );?></p>
	  </td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[tsc]"><?php _e( 'Technical Steering Commitee', 'kwik' );?></label></th>
	  <td>
		<input type="text" name="term_meta[tsc]" id="term_meta[tsc]" value="<?php echo esc_attr($term_meta['tsc']) ? esc_attr($term_meta['tsc']) : '';?>">
		<p class="description"><?php _e( '', 'kwik' );?></p>
	  </td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[position]"><?php _e( 'Board/Voting Position', 'kwik' );?></label></th>
	  <td>
		<input type="text" name="term_meta[position]" id="term_meta[position]" value="<?php echo esc_attr($term_meta['position']) ? esc_attr($term_meta['position']) : '';?>">
		<p class="description"><?php _e( '', 'kwik' );?></p>
	  </td>
	</tr>
  <?php
  }

	/**
	 * saves the custom taxonomy meta for solutions CPT
	 * @param  [type] $t_id [description]
	 * @return [type]       [description]
	 */
	public function save_solution_categories_custom_meta($t_id)
	{
		if (isset($_POST['term_meta'], $t_id)) {
			$term_meta = get_option("taxonomy_$t_id");
			$keys = array_keys($_POST['term_meta']);
			foreach ($keys as $key) {
				if (isset($_POST['term_meta'][$key])) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}
			// Save the option array.
			update_option("taxonomy_$t_id", $term_meta);
		}
	}

	// Add the meta box
	public static function add_solutions_metabox(){
		$settings = get_option(K_SOLUTIONS_SETTINGS);
		add_meta_box( 'solutions_meta', __("{$settings['name']} Meta Data", 'kwik' ), array( 'K_SOLUTIONS_META', 'solutions_meta' ), K_SOLUTIONS_CPT, 'normal', 'default' );
		$solutions_name = $string = preg_replace( '/\s+/', '', strtolower($settings['name']));
		$solutions_info_fields = array(
			'website_link_text' => array(
				'type' => 'text',
				'title' => __( "{$settings['name']} Website Text: ", 'kwik' ),
				'value' => '',
				'attrs' => array(
					'placeholder' => __("Learn More", 'kwik' )
					)
			),
			'website_link' => array(
				'type' => 'link',
				'title' => __( "{$settings['name']} Website Link: ", 'kwik' ),
				'value' => '',
				'attrs' => array(
					'placeholder' => __("http://{$solutions_name}-website.com", 'kwik' )
					)
			),
			'certified' => array(
				'type' => 'toggle',
				'title' => __( "Certified: ", 'kwik' ),
				),
			'hr' => array(
				'type' => 'element',
				'attrs' => array('class' => 'clear'),
				),
		);

		// if Kwik Clients is installed, add a field allowing us to link the solution to the client
		if (defined( 'K_CLIENTS_CPT' )) {
	        $clients_settings = get_option( K_CLIENTS_SETTINGS );
	        $string = preg_replace('/\s+/', '', $clients_settings['name']);
	        $clients_name = preg_replace('/\s+/', '', $clients_settings['name']);
		} else {
	        $clients_name = 'Member';
		}
        $type_name = 'Company';
        $alt_name = ( $clients_name === $type_name ) ? 'Organization' : $type_name;
		$solutions_info_fields['company'] = array(
			'type' => 'autocomplete',
			'title' => __( "$alt_name/$clients_name:", 'kwik' ),
			'value' => null,
			'attrs' => array(
				'placeholder' => __("{$clients_name} Name", 'kwik' ),
				'data-link-to' => K_CLIENTS_CPT,
				)
		);
		$solutions_info_fields['company_website'] = array(
			'type' => 'link',
			'title' => __( "$alt_name/$clients_name Website:", 'kwik' ),
			'value' => null,
			'attrs' => array(
				'placeholder' => __("{$clients_name} Website", 'kwik' ),
				)
		);

		set_transient( 'solutions_info_fields', $solutions_info_fields, WEEK_IN_SECONDS );
	}


	public static function solutions_meta($post)
	{
		$meta = new KwikMeta();
		echo $meta->get_fields($post, 'solutions_info_fields' );
	}

	// Save the Metabox Data
	public static function save_solutions_meta($post_id, $post)
	{
		if($post->post_status == 'auto-draft' || $post->post_type !== 'solutions' ) return;

		$meta = new KwikMeta();
		$meta->save_meta($post, 'solutions_info_fields' );
	}

}
