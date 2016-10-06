<?php

if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices',
		function () {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
		} );

	return;
}

include get_template_directory() . '/features/ChiselPost.php';

// set default twig templates directory
Timber::$dirname = array( 'templates' );

class StarterSite extends TimberSite {
    const DIST_PATH = 'dist/';

	private $manifestPath = 'dist/rev-manifest.json';
	private $manifest = array();

	public function __construct() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );

		// load filenames from manifest file
		// used to determinate asset real path
		if ( ! isset( $_SERVER['WP_ENV_DEV'] ) && file_exists( get_template_directory() . '/' . $this->manifestPath ) ) {
			$this->manifest = json_decode( file_get_contents( get_template_directory() . '/' . $this->manifestPath ),
				true );
		}

		add_filter('Timber\PostClassMap', array($this, 'override_timber_post_class'));

		parent::__construct();
	}

	public function override_timber_post_class($post_class) {
		return 'ChiselPost';
	}

	public function register_post_types() {
		//this is where you can register custom post types
	}

	public function register_taxonomies() {
		//this is where you can register custom taxonomies
	}

	public function add_to_context( $context ) {
		$context['menu'] = new TimberMenu();
		$context['post'] = new ChiselPost();

		return $context;
	}

	public function add_to_twig( $twig ) {
		/* this is where you can add your own fuctions to twig */
		$assetPathFunction = new Twig_SimpleFunction( 'assetPath', array( $this, 'twigAssetPath' ) );
		$twig->addFunction( $assetPathFunction );

		return $twig;
	}

	/**
	 * Returns the real path of the asset. When WP_ENV_DEV is not defined in the current environment then it returns
	 * path based on the manifest file content.
	 *
	 * @param $asset
	 *
	 * @return string
	 */
	public function twigAssetPath( $asset ) {
		$pathinfo = pathinfo( $asset );

		if ( ! isset( $_SERVER['WP_ENV_DEV'] ) && array_key_exists( $pathinfo['basename'], $this->manifest ) ) {
			return get_template_directory_uri() . '/' . self::DIST_PATH . $pathinfo['dirname'] . '/' . $this->manifest[ $pathinfo['basename'] ];
		} else {
			return get_template_directory_uri() . '/' . self::DIST_PATH . trim( $asset, '/' );
		}
	}
}

new StarterSite();
