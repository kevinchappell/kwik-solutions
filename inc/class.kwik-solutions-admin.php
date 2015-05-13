<?php

/**
 * KwikSolutions Admin Class
 * @category    Admin
 * @package     KwikSolutions
 * @subpackage  KwikSolutionsAdmin
 * @author      Kevin Chappell <kevin.b.chappell@gmail.com>
 * @license     http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link        http://kevin-chappell.com/kwik-solutions/docs/inc/class.kwik-solutions-admin.php/
 * @since       KwikSolutions 1.0
 */

class KwikSolutionsAdmin {
	/**
	 * Main constructor
	 */
	public function __construct() {
		include_once 'class.kwik-solutions-meta.php';
		add_action('admin_enqueue_scripts', array( $this, 'add_solutions_script'));
		add_action('manage_solutions_posts_custom_column', array( $this, 'solutions_columns_content'), 10, 2);
		add_action('wp_ajax_solutions_update_post_order', array( $this, 'solutions_update_post_order'));
		add_action('admin_menu', array( $this, 'register_solutions_menu'));
		add_shortcode('membership_table', array( $this, 'membership_table'));

		// Utils/Helpers
		add_filter('gettext', array('K_SOLUTIONS_HELPERS', 'k_solutions_logo_text_filter'), 20, 3);
		add_filter('manage_solutions_posts_columns', array('K_SOLUTIONS_HELPERS', 'add_solutions_columns'));
		add_action('dashboard_glance_items', array('K_SOLUTIONS_HELPERS', 'solutions_at_a_glance'), 'solutions');
		new K_SOLUTIONS_META();
	}

	/**
	 * Add the scripts and styling for the admin
	 * @param [string] $hook current admin page hook
	 * @return scripts and styles
	 */
	public function add_solutions_script($hook) {
		$screen = get_current_screen();

		$post_types_array = array(
		  "solutions",
		  "solutions_page_slide-order"
		);

		// Check screen hook and current post type
		if ( in_array($screen->post_type, $post_types_array)) {
			wp_enqueue_style(K_SOLUTIONS_BASENAME . '-admin-css', K_SOLUTIONS_URL . '/css/' . K_SOLUTIONS_BASENAME . '-admin.css', false, '2015-1-27');
			wp_enqueue_script(K_SOLUTIONS_BASENAME . '-admin', K_SOLUTIONS_URL . '/js/' . K_SOLUTIONS_BASENAME . '-admin.js', array('jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery'), null, true);
			wp_enqueue_script(K_SOLUTIONS_BASENAME, K_SOLUTIONS_URL . '/js/' . K_SOLUTIONS_BASENAME . '.js', array('jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery'), null, true);
		}
	}

	/**
	 * Setup custom columns for the cpt list page
	 * @param  [string] $column_name current column name
	 * @param  [int]    $post_ID     post_id for the current row in the list
	 * @return [string]              markup for various fields
	 */
	public function solutions_columns_content($column_name, $post_ID) {
		switch ( $column_name ) {
		case "featured_image":
			$thumb = wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium');
			$thumb = $thumb['0'];
			if ($thumb) {
				echo '<img width="75" src="' . $thumb . '" />';
			}
			break;
		}
	}

	public function register_solutions_menu() {
		add_submenu_page('edit.php?post_type=solutions', 'Order Solutions', 'Order', 'edit_pages', 'solutions-order', array($this,'solutions_order_page'));
	}

	public function solutions_order_page() {
	  global $typenow;

	  $settings = get_option(K_SOLUTIONS_SETTINGS);
	?>

	  <div class="wrap">

		<?php echo '<h2>'. __("Sort {$settings['name_plural']}", 'kwik').'</h2>'; ?>

		<p>Drag the solutions up or down and they will be saved in the order the appear here.</p>

	  <?php

		$terms = get_terms('solution_categories', 'orderby=id&hide_empty=1');

	if(empty($terms)){
		$terms[0] = new stdClass();
		$terms[0]->taxonomy = 'none';
		$terms[0]->name = '';
	}
		foreach ($terms as $term) {
			$solutions = new WP_Query(array(
				'post_type' => 'solutions',
				'posts_per_page' => -1,
				'order' => 'ASC',
				'orderby' => 'menu_order'
			));
			if($term->taxonomy !== 'none'){
				$solutions['tax_query'] = array(
				  array(
					'taxonomy' => $term->taxonomy,
					'field' => 'id',
					'terms' => $term->term_id, // Where term_id of Term 1 is "1".
					'include_children' => false
				  )
				);
			}
			echo '<h1>'.$term->name.'</h1>';
			if ($solutions->have_posts()): ?>
			<table class="wp-list-table widefat fixed posts" id="sortable-table">
			  <thead>
				<tr>
				  <th class="column-order">Order</th>
				  <th class="column-thumbnail">Thumbnail</th>
				  <th class="column-title">Title</th>
				</tr>
			  </thead>
			  <tbody data-post-type="solutions">
			  <?php
					while ($solutions->have_posts()): $solutions->the_post();
					?>

				<tr id="post-<?php the_ID(); ?>">
				  <td class="column-order"><img src="<?php echo get_stylesheet_directory_uri() . '/images/icons/move.png'; ?>" title="" alt="Move Slide" width="30" height="30" class="" /></td>
				  <td class="column-thumbnail"><?php the_post_thumbnail('solution_image'); ?></td>
				  <td class="column-title">
								<strong><?php the_title();?></strong>
								<div class="excerpt"><?php the_excerpt(); ?></div>
							</td>
				</tr>
			  <?php endwhile; ?>
			  </tbody>
			  <tfoot>
				<tr>
				  <th class="column-order">Order</th>
				  <th class="column-thumbnail">Thumbnail</th>
				  <th class="column-title">Title</th>
				</tr>
			  </tfoot>
			</table>

	  <?php else: ?>
		<p>No solutions found, why not <a href="post-new.php?post_type=solutions">add one?</a></p>
	  <?php endif; ?>

	  <?php wp_reset_postdata(); // Don't forget to reset again!
	  }?>


	  </div><!-- .wrap -->

	<?php
	}


	public function solutions_update_post_order() {
		global $wpdb;
		$post_type = $_POST['postType'];
		$order     = $_POST['order'];
		/**
		*    Expect: $sorted = array(
		*                menu_order => post-XX
		*            );
		*/
		foreach ($order as $menu_order => $post_id) {
		  $post_id    = intval(str_ireplace('post-', '', $post_id));
		  $menu_order = intval($menu_order);
		  wp_update_post(array(
			  'ID' => $post_id,
			  'menu_order' => $menu_order
		  ));
		}
		// die('1');
	}
}
