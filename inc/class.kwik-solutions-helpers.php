<?php

class K_SOLUTIONS_HELPERS extends KwikSolutions {

	public function __construct() {
		$this->name = "K_SOLUTIONS_HELPERS";
	}

	public static function array_insert_at_position($array, $values, $pivot, $position = 'after') {

		$offset = 0;
		foreach ($array as $key => $value) {
			++$offset;
			if ($key == $pivot) {
				break;
			}
		}

		if ($position == 'before') {
			--$offset;
		}

		return array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, null, true);
	}

	// ADD NEW COLUMN
	public static function add_solutions_columns($columns) {
		$columns = self::array_insert_at_position($columns, array('featured_image' => __('Image')), 'cb');
		return $columns;
	}

	public static function icons() {
		  return array(
			'dashicons-admin-users'  => 'User',
			'dashicons-businessman'  => 'Business Person',
			'dashicons-universal-access' => 'Universal Access',
			'dashicons-awards' => 'Award',
			'dashicons-networking' => 'Networking',
			'dashicons-star-empty' => 'Star'
			);
	}

	public static function k_solutions_logo_text_filter($translated_text, $untranslated_text, $domain) {
		global $post, $typenow, $current_screen;
		$settings = get_option(K_SOLUTIONS_SETTINGS);

		$plugin = array(
			'name' => isset($settings['name']) ? $settings['name'] : 'Solution'
		);

		if (is_admin() && K_SOLUTIONS_CPT === $typenow) {
			switch ($untranslated_text) {

				case 'Insert into post':
					$translated_text = __("Add to {$plugin['name']} description", 'kwik');
					break;

				case 'Set featured image':
					$translated_text = __("Set {$plugin['name']} image", 'kwik');
					break;

				case 'Set Featured Image':
					$translated_text = __("Set {$plugin['name']} Image", 'kwik');
					break;

				case 'Remove featured image':
					$translated_text = __("Remove {$plugin['name']} Image", 'kwik');
					break;

				case 'Featured Image':
					$translated_text = __("{$plugin['name']} Image", 'kwik');
					break;

				case 'Enter title here':
					$translated_text = __("Enter {$plugin['name']} Name", 'kwik');
					break;
			}
		}
		return $translated_text;
	}

	public static function solutions_at_a_glance() {
		KwikUtils::cpt_at_a_glance(K_SOLUTIONS_CPT);
	}

}
