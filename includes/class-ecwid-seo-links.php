<?php


class Ecwid_Seo_Links {

	const OPTION_ENABLED = 'ecwid_seo_links_enabled';

	public function __construct()
	{
		if ( self::is_enabled() ) {

			add_action( 'rewrite_rules_array', array( $this, 'build_rewrite_rules' ), 1, 1 );
			add_filter( 'redirect_canonical', array( $this, 'redirect_canonical' ), 10, 2 );
			add_action( 'template_redirect', array( $this, 'redirect_escaped_fragment' ) );

			add_action( 'ecwid_print_inline_js_config', array( $this, 'add_js_config') );
		}
	}

	public function redirect_canonical( $redir, $req ) {
		global $wp_query;

		$page_id = $wp_query->get( 'page_id' );

		if ( $page_id && ecwid_page_has_productbrowser($page_id)  && $req . '/' == $redir ) {
			return false;
		}

		return $redir;
	}

	public function redirect_escaped_fragment() {
		if ( ecwid_can_display_html_catalog() ) {
			$params = ecwid_parse_escaped_fragment( $_GET['_escaped_fragment_'] );

			if ( !isset( $params['mode'] ) ) {
				return;
			}

			if ( $params['mode'] == 'product' ) {
				$redirect = Ecwid_Store_Page::get_product_url( $params['id'] );
			} else if ($params['mode'] == 'category') {
				$redirect = Ecwid_Store_Page::get_category_url( $params['id'] );
			}

			if ($redirect) {
				wp_redirect( $redirect, 301 );
			}
		}
	}

	public function add_js_config() {

		global $wp_query;
		$page_id = $wp_query->get( 'page_id' );

		$has_store = ecwid_page_has_productbrowser($page_id);

		if ( !$has_store ) return;

		$url = esc_js( ecwid_get_store_page_base_url() );

		echo <<<JS
			window.ec.config.storefrontUrls.cleanUrls = true;
			window.ec.config.baseUrl = '$url';
JS;
	}

	public function build_rewrite_rules( $original_rules ) {

		$pages = get_pages( array( 'status' => 'publish,private' ) );

		$rules = array();
		foreach ( $pages as $page ) {
			if ( ecwid_page_has_productbrowser( $page->ID ) ) {
				$link = get_page_uri($page);

				$rules['^' . $link . '/.*'] = 'index.php?page_id=' . $page->ID;
			}
		}

		return array_merge( $rules, $original_rules );
	}

	public static function is_enabled() {

		return self::is_feature_available() && get_option( self::OPTION_ENABLED );
	}

	public static function is_feature_available() {
		$permalink = get_option( 'permalink_structure' );

		return $permalink != '';
	}
}

$ecwid_seo_links = new Ecwid_Seo_Links();