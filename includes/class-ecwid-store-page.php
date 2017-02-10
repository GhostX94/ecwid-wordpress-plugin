<?php

class Ecwid_Store_Page {
	public static function get_product_url( $id )
	{
		if ( Ecwid_Products::is_enabled() ) {
			global $ecwid_products;

			$url = $ecwid_products->get_product_link( $id );

			if ( $url ) {
				return $url;
			}
		}

		$url = self::get_product_url_from_api( $id );
		if ( $url ) {
			return $url;
		}

		return self::get_product_url_default_fallback( $id );
	}

	public static function get_product_url_from_api($id ) {
		$cached = EcwidPlatform::cache_get( 'product_url_' . $id );

		if ( $cached ) {
			return $cached;
		}

		$api = new Ecwid_Api_V3();

		if ( $api->is_available() ) {

			$product = $api->get_product( $id );

			if ( $product ) {
				self::register_product( $product );

				return $product->url;
			}
		}

	}

	public static function get_product_url_default_fallback ( $id ) {
		return self::get_store_url() . '#!/p/' . $id;
	}

	public static function register_product( $product ) {
		EcwidPlatform::cache_set( 'product_url_' . $product->id, $product->url, DAY_IN_SECONDS * 30 );
	}

	public static function register_category( $category ) {
		EcwidPlatform::cache_set( 'category_url_' . $category->id, $category->url, DAY_IN_SECONDS * 30 );
	}

	public static function get_category_url( $id )
	{
		if ( $id == 0 ) {
			return self::get_store_url();
		}

		$api = new Ecwid_Api_V3();
		if ( $api->is_available() ) {
			$cached = EcwidPlatform::cache_get( 'category_url_' . $id );

			if ( $cached ) {
				return $cached;
			}

			$category = $api->get_category( $id );

			if ( $category ) {
				$url = $category->url;

				self::register_category( $category );
			}

			return $url;
		}

		return self::get_store_url() . '#!/c/' . $id;
	}

	public static function get_menu_item_url()
	{

	}

	public static function get_cart_url()
	{
		if ( Ecwid_Seo_Links::is_enabled() ) {
			return self::get_store_url() . '/cart';
		} else {
			return self::get_store_url() . '#!/cart';
		}
	}

	public static function get_store_url()
	{
		static $link = null;

		if (is_null($link)) {
			$link = get_page_link( self::get_current_store_page_id() );
		}

		return $link;
	}

	public static function get_current_store_page_id()
	{
		static $page_id = null;

		if (is_null($page_id)) {
			$page_id = false;
			foreach(array('ecwid_store_page_id', 'ecwid_store_page_id_auto') as $option) {
				$id = get_option($option);
				if ($id) {
					$status = get_post_status($id);

					if ($status == 'publish' || $status == 'private') {
						$page_id = $id;
						break;
					}
				}
			}
		}

		return $page_id;
	}

}