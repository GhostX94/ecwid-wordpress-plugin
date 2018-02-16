<?php

require_once __DIR__ . '/class-ecwid-importer.php';

class Ecwid_Import_Page
{
	const PAGE_SLUG = 'ec-store-import';
	const PAGE_SLUG_WOO = 'ec-store-import-woocommerce';
	const AJAX_ACTION_CHECK_IMPORT = 'ec-store-check-import';
	const AJAX_ACTION_DO_WOO_IMPORT = 'ec-store-do-woo-import';
	
	protected $importer;
	
	public function __construct()
	{
		$this->importer = new Ecwid_Importer();
	}

	public function init_actions()
	{
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION_CHECK_IMPORT, array( $this, 'check_import') );
		add_action( 'wp_ajax_' . self::AJAX_ACTION_DO_WOO_IMPORT, array( $this, 'do_woo_import') );
	}
	
	
	public function build_menu()
	{
		add_submenu_page(
			Ecwid_Admin::ADMIN_SLUG,
			'Import',
			'Import',
			Ecwid_Admin::get_capability(),
			self::PAGE_SLUG,
			array( $this, 'do_page' ),
			'',
			'2.562347345'
		);
		add_submenu_page(
			self::PAGE_SLUG,
			'Import your products from WooCommerce to Ecwid',
			'Import your products from WooCommerce to Ecwid',
			Ecwid_Admin::get_capability(),
			self::PAGE_SLUG_WOO,
			array( $this, 'do_woo_page' ),
			'',
			'2.562347345'
		);
	}
	
	public function enqueue_scripts()
	{
		wp_enqueue_style( 'ecwid-importer', ECWID_PLUGIN_URL . '/css/importer.css' );
		wp_enqueue_script( 'ecwid-importer', ECWID_PLUGIN_URL . '/js/importer.js' );
		wp_localize_script( 'ecwid-importer', 'ecwid_importer', array(
			'check_token_action' => self::AJAX_ACTION_CHECK_IMPORT,
			'do_woo_import_action' => self::AJAX_ACTION_DO_WOO_IMPORT
		) );
	}
	
	public function check_import()
	{
		if ( !current_user_can( Ecwid_Admin::get_capability() ) ) {
			return;
		}
		
		$data = array();
		$token_ok = $this->_is_token_ok();
		if ( !$token_ok ){
			$data['has_good_token'] = false;
		} else {
			$data['has_good_token'] = true;
			$data = Ecwid_Import::gather_import_data();
		}
		
		echo json_encode( $data );
		
		die();
	}
	
	protected function _is_token_ok()
	{
		$oauth = new Ecwid_OAuth();
		
		return $oauth->has_scope( 'create_catalog' ) && $oauth->has_scope( 'update_catalog' );
	}
	
	public function do_woo_import()
	{
		require_once __DIR__ . '/class-ecwid-importer.php';
		$importer = new Ecwid_Importer();

		if ( !$importer->has_begun() ) {
			$importer->initiate();
		}
		
		$result = $importer->proceed();
		
		echo json_encode( $result );
		
		die();
	}
	
	protected function _get_woo_url()
	{
		return 'admin.php?page=' . self::PAGE_SLUG_WOO;
	}
	
	
	public function do_page()
	{
		require_once ECWID_IMPORTER_TEMPLATES_DIR . '/landing.tpl.php';
	}
	
	public function do_woo_page()
	{
		$import_data = Ecwid_Import::gather_import_data();

		require_once ECWID_IMPORTER_TEMPLATES_DIR . '/woo-main.tpl.php';
	}}