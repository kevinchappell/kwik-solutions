<?php

require_once 'class.kwik-solutions-helpers.php';

class KwikSolutions {
	static $helpers;
	$taxonomy = 'solution_categories';
	$cpt = K_SOLUTIONS_CPT;

	public function __construct() {

		add_action( 'init', array( $this, 'solutions_create_post_type' ) );
		add_shortcode( 'member_table', array( $this, 'member_table' ) );
		add_filter( 'kwik_left_menu', array( $this, 'solutions_category_links' ) );

		if ( is_admin() ) {
			$this->admin();
		} else {
			add_action( 'wp_enqueue_scripts', array( &$this, 'scripts_and_styles' ) );
		}

		// widgets
		self::load_widgets();

		// Cleanup on deactivation
		register_activation_hook( __FILE__, array( &$this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
	}

	public function __destruct() {
		// Garbage cleanup
	}

	public function activate() {
		flush_rewrite_rules(false);
	}

	public function deactivate() {
		flush_rewrite_rules(false);
	}

	public function admin() {
		if ( ! isset( $this->admin ) ) {
			include_once __DIR__ . '/class.kwik-solutions-admin.php';
			include_once __DIR__ . '/class.kwik-solutions-settings.php';
			$this->admin = new KwikSolutionsAdmin( $this);
		}
		return $this->admin;
	}

	public function scripts_and_styles() {
		wp_enqueue_style( 'kwik-solutions-css', K_SOLUTIONS_URL . '/css/' . K_SOLUTIONS_BASENAME . '.css', false, '2014-12-31' );
	}

	public function solutions_create_post_type() {
		$settings = get_option(K_SOLUTIONS_SETTINGS);
		$plugin = array(
			'name' => isset( $settings['name']) ? $settings['name'] : 'Solution',
			'name_plural' => isset( $settings['name_plural']) ? $settings['name_plural'] : 'Solutions',
			'dash_icon' => isset( $settings['dash_icon']) ? $settings['dash_icon']['icon_select'] : 'dashicons-awards',
		);

		self::create_solutions_taxonomies();

		register_post_type(
			K_SOLUTIONS_CPT,
			array(
				'labels' => array(
					'name' => __( 'Solutions', 'kwik' ),
					'all_items' => __( $plugin['name_plural'], 'kwik' ),
					'singular_name' => __( $plugin['name'], 'kwik' ),
					'add_new' => __("Add ${plugin['name']}", 'kwik' ),
					'add_new_item' => __("Add New ${plugin['name']}", 'kwik' ),
					'edit_item' => __("Edit ${plugin['name']}", 'kwik' ),
					'menu_name' => __( $plugin['name_plural'], 'kwik' ),
				),
				'menu_icon' => $plugin['dash_icon'],
				'menu_position' => 5,

				'supports' => array( 'title', 'editor', 'thumbnail', 'author' ),
				'public' => true,
				'exclude_from_search' => false,
				'has_archive' => true,
				'taxonomies' => array( 'solution_categories' ),
				'register_meta_box_cb' => array( 'K_SOLUTIONS_META', 'add_solutions_metabox' ),
				// 'rewrite' => array( 'slug' => K_SOLUTIONS_CPT ),
				'query_var' => true,
			)
		);

		add_image_size( 'solution_image', 240, 240, false );
	}

	/**
	 * Custom taxonomy for solutions
	 * @return bool [description]
	 */
	public function create_solutions_taxonomies() {

		register_taxonomy(
			'solution_categories',
			array( $this->cpt ),
			array(
				'hierarchical' => true,
				'labels' => self::make_labels( 'Category', 'Categories' ),
				'show_ui' => true,
				'query_var' => true,
				'show_admin_column' => true,
				'rewrite' => array( 'slug' => 'solutions/category', 'hierarchical' => true),
			)
		);

		return true;
	}

	private static function make_labels( $single, $plural) {
		return array(
			'name' => _x( $plural, 'taxonomy general name' ),
			'singular_name' => _x( $plural, 'taxonomy singular name' ),
			'search_items' => __( 'Search ' . $plural),
			'all_items' => __( 'All ' . $plural),
			'edit_item' => __( 'Edit ' . $single),
			'update_item' => __( 'Update ' . $single),
			'add_new_item' => __( 'Add New ' . $single),
			'new_item_name' => __( 'New ' . $single),
		);
	}

	public function member_table() {
		?>
		<div class="member_table">
		<?php $terms = get_terms( 'solution_categories', 'orderby=id&hide_empty=0' );
		foreach ( $terms as $term) {
			$solutions = new WP_Query(
				array(
					'post_type' => K_SOLUTIONS_CPT,
					'posts_per_page' => 50,
					$term->taxonomy => $term->slug,
					'order' => 'ASC',
					'orderby' => 'menu_order',
				)
			);
			echo '<h2>' . $term->name . ' Level</h2>';

			if ( $solutions->have_posts()): ?>
			<ul class="mem_level-<?php echo $term->slug; ?> clear">
				<?php while ( $solutions->have_posts()):$solutions->the_post();
				$solutions = has_post_thumbnail() ? KwikInputs::markup( 'a', get_the_post_thumbnail(get_the_ID(), 'solutions_logo' ), array( 'href' => get_the_permalink())) : KwikInputs::markup( 'a', get_the_title(), array( 'href' => get_the_permalink()) );
				echo KwikInputs::markup( 'li', $solutions, array( 'class' => 'solutions-'.$solutions->post->ID) );
			endwhile; ?>
			</ul>
			<?php else:
				echo '<p>' . $term->name . ' membership level available</p>';
			endif; ?>
		<?php wp_reset_postdata(); // Don't forget to reset again!
		} ?>
	</div><?php
}// member_table()

	/**
	 * Adds `membership_table` shortcode.
	 * Usage: [membership_table foo="foo-value"]
	 *
	 * @todo   use Kwik Framework markup generator, no concat, make tax meta generator
	 *
	 * @param  [Array]  $atts  array of attribute to pass
	 * @return [String] Markup to display array of solutions data
	 */
	public function membership_table( $atts) {
		// extract(shortcode_atts(array(
		//     'foo' => 'something',
		//     'bar' => 'something else',
		// ), $atts) );

		$memb_table = '<!-- BEGIN [membership_table] -->';
		$terms = get_terms("solution_categories", 'orderby=id&hide_empty=0&exclude=27' );

		$memb_table .= '<table class="mem_table" cellpadding="5">
	<thead>
	  <tr>';
		$memb_table .= '<th class="column-mem_level_img"></th>';
		$memb_table .= '<th class="column-mem_level">' . __( 'Membership Level', 'kwik' ) . '</th>';
		$memb_table .= '<th class="column-fee">' . __( 'Annual Fee*', 'kwik' ) . '</th>';
		$memb_table .= '<th class="column-fte">' . __( 'FTEs', 'kwik' ) . '</th>';
		// $memb_table .= '<th class="column-ipc">'.__( 'IP Contribution', 'kwik' ).'</th>';
		$memb_table .= '<th class="column-tsc">' . __( 'Technical Steering Commitee', 'kwik' ) . '</th>';
		$memb_table .= '<th class="column-position">' . __( 'Board/Voting <br/>Position', 'kwik' ) . '</th>';
		$memb_table .= '</tr>
	</thead>
	<tbody data-post-type="solution_categories">';

		foreach ( $terms as $term) {
			$t_id = $term->term_id;
			$term_meta = get_option("taxonomy_$t_id");
			$img = '';

			if (function_exists( 'taxonomy_image_plugin_get_image_src' )) {
				$associations = taxonomy_image_plugin_get_associations();
				if (isset( $associations[$term->term_id])) {
					$attachment_id = (int)$associations[$term->term_id];
					$img = wp_get_attachment_image( $attachment_id, 'medium' );
				}
			}

			$memb_table .= '<tr>';
			$memb_table .= '<td class="mem_level_img">' . $img . '</td>';
			$memb_table .= '<td class="mem_level_name">' . $term->name . '</td>';
			$memb_table .= '<td>' . (esc_attr( $term_meta['fee'][0]) ? esc_attr( $term_meta['fee'][0]) : '0' );
			$memb_table .= (esc_attr( $term_meta['fee'][1]) ? '<br><em>' . esc_attr( $term_meta['fee'][1]) . '</em>' : '' );

			$memb_table .= '</td>';
			$memb_table .= '<td>' . (esc_attr( $term_meta['fte']) ? esc_attr( $term_meta['fte']) : '0' ) . '</td>';
			// $memb_table .= '<td>'.(esc_attr( $term_meta['ipc'] ) ? esc_attr( $term_meta['ipc'] ) : '' ).'</td>';
			$memb_table .= '<td>' . (esc_attr( $term_meta['tsc']) ? esc_attr( $term_meta['tsc']) : '' ) . '</td>';
			$memb_table .= '<td>' . (esc_attr( $term_meta['position']) ? esc_attr( $term_meta['position']) : '' ) . '</td>';
			$memb_table .= '</tr>';
		}
		$memb_table .= '</tbody></table><em style="font-size: 12px;">*' . __( 'Fee in US Dollars.', 'kwik' ) . '</em>';
		$memb_table .= '<!-- END [membership_table] -->';

		return $memb_table;
	}

	public function solutions_logos( $args) {
		$inputs = new KwikInputs();
		$query_args = array(
			'post_status' => 'publish',
			'post_type' => K_SOLUTIONS_CPT,
			'posts_per_page' => 50,
		);

		if ( isset( $args['orderby'] ) ){
			$query_args['orderby'] = $args['orderby'];
		}

		if ( isset( $args['order'] ) ){
			$query_args['order'] = $args['order'];
		}

		if ( isset( $args['level'] ) ) {
			$query_args['solution_categories'] = $args['level'];
			if ( $args['group_by_level'] ) {
				$term = get_term_by( 'slug', $args['level'], 'solution_categories' );
				$solutions_logos = $inputs->markup( 'h3', $term->name . ' Members' );
			}
		}

		$solutions_query = new WP_Query( $query_args);

		$index = 1;
		$total = $solutions_query->post_count;
		if ( $solutions_query->have_posts()):
			$solutions_logos = '';
			while ( $solutions_query->have_posts()):$solutions_query->the_post();
				global $more;
				$more = 0;

				$solutions_id = get_the_ID();
				$solutions_name = get_the_title( $solutions_id);
				$logo_or_name = (has_post_thumbnail() && $args['show_thumbs']) ? get_the_post_thumbnail( $solutions_id, 'solutions_logo' ) : $solutions_name;
				$solutions = $inputs->markup( 'a', $logo_or_name, array( 'href' => get_the_permalink( $solutions_id ), 'title' => $solutions_name ) );
				$solutions_logos .= $inputs->markup( 'div', $solutions, array( 'class' => 'solutions solutions-' . $solutions_id . ' nth-solutions-' . $index) );
				$index++;
			endwhile;
		endif;
		wp_reset_postdata();

		if( $args['group_by_level'] ) {
			$term_class = isset( $term) ? $term->slug . '-members' : null;
			$solutions_logos = $inputs->markup( 'div', $solutions_logos, array( 'class' => array( 'member-level', $term_class, 'clear' )) );
		}

		echo $solutions_logos;
	}

	public function load_widgets() {
		foreach ( glob( K_SOLUTIONS_PATH . '/widgets/*.php' ) as $inc_filename ) {
			include_once $inc_filename;
		}
	}


	public function solutions_category_links($links){
		$output = $links;

		if ( is_tax( $this->taxonomy ) ) {
			$output .= $this->get_solutions_category_links();
		} else if( is_single() && K_SOLUTIONS_CPT === get_post_type() ){
			$output .= $this->get_solutions_category_links();
		}
		return $output;
	}

	public function get_solutions_category_links(){
		$output = '';
		$tax_obj = get_queried_object();
		$terms = get_terms( $this->taxonomy );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			foreach ( $terms as $term ) {
				$term_link = get_term_link( $term );
				if ( is_single() ){
					$term_list = wp_get_post_terms( get_the_ID(), $this->taxonomy, array('fields' => 'ids'));
					$current_item = ( in_array( $term->term_id, $term_list ) ) ? 'current_page_item' : '';
				} else {
					$current_item = ( $tax_obj->term_id == $term->term_id) ? 'current_page_item' : '';
				}
				$output .= '<li class="'.$current_item.'"><a href="' . esc_url( $term_link ) . '">' . $term->name . '</a></li>';
			}
		}
		return $output;
	}


}// / Class KwikSolutions

// Singleton
function kwiksolutions() {
	global $kwiksolutions;
	if ( ! $kwiksolutions ) {
		$kwiksolutions = new KwikSolutions();
	}
	return $kwiksolutions;
}
