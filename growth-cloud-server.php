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

# Classes
define('GROWTH_OPTIMIZER_CLOUD_CLASSES', [
    'helper',
    'sites',
    'zip',
    'meta',
    'cloud'
]);


add_action('plugins_loaded', function() {
    # If loaded
    $is_loaded = false;
    # Config for cloud plugins
    require_once( GROWTH_OPTIMIZER_CLOUD_DIR . 'config/plugins.php' );
    # Load classes
    foreach (GROWTH_OPTIMIZER_CLOUD_CLASSES as $index => $class) {
        if ($index+1 == count(GROWTH_OPTIMIZER_CLOUD_CLASSES))
            $is_loaded = true;
        require_once( GROWTH_OPTIMIZER_CLOUD_DIR . "class/class_{$class}.php" );
    }    
    if ($is_loaded) {
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
    }
});