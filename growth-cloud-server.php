<?php
/**
 * Plugin Name:     Growth Optimizer Cloud Server
 * Plugin URI:      https://growthoptimizer.com
 * Description:     Cloud server for template kit
 * Author:          Growth Optimizer
 * Author URI:      https://growthoptimizer.com/
 * Text Domain:     go-cloud-server
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         growthoptimizer-starter-template
 */
namespace GO_Cloud;


if ( ! defined( 'ABSPATH' ) ) {
	exit; # Exit if accessed directly.
}

# If plugin - 'Elementor' not exist then return.
if ( ! class_exists( '\Elementor\Plugin' ) ) {
	return;
}

# Constant variables
define('GROWTH_OPTIMIZER_TITLE', 'GO Sites');
define('GROWTH_OPTIMIZER_SLUG', 'go-sites');
define('GROWTH_OPTIMIZER_CLOUD_DIR', plugin_dir_path( __FILE__ ));
define('GROWTH_OPTIMIZER_CLOUD_URL', plugin_dir_url( __FILE__ ));
define('GROWTH_OPTIMIZER_SITES', 'growth_optimizer_cloud_sites');
define('GROWTH_OPTIMIZER_DB', 'subscribed_sites');

# Plugins repository
define('REPO_FOLDER', 'required-plugin');
define('GROWTH_OPTIMIZER_PLUGINS_REPO', ABSPATH . REPO_FOLDER);


add_action('plugins_loaded', function() {
    # Config for cloud plugins
    require_once( GROWTH_OPTIMIZER_CLOUD_DIR . 'config/plugins.php' );
    # Zip generator
    require_once( GROWTH_OPTIMIZER_CLOUD_DIR . 'class/class_zip.php' );
    # Meta
    require_once( GROWTH_OPTIMIZER_CLOUD_DIR . 'class/class_meta.php' );    
    # Config for cloud plugins
    require_once( GROWTH_OPTIMIZER_CLOUD_DIR . 'class/class_cloud.php' );
    # Start the cloud server API
    $go_template_cloud_api = new GO_Cloud_API(
        GROWTH_OPTIMIZER_TITLE,
        GROWTH_OPTIMIZER_SLUG,
        GROWTH_OPTIMIZER_CLOUD_DIR,
        GROWTH_OPTIMIZER_CLOUD_URL,
        GROWTH_OPTIMIZER_SITES,
        GROWTH_OPTIMIZER_PLUGINS,
        GROWTH_OPTIMIZER_PLUGINS_REPO,
        GROWTH_OPTIMIZER_DB
    );
    # Let's start the cloud api
    $go_template_cloud_api->start();
});