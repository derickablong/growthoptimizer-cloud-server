<?php
/**
 * The plugins zip fils is located in the root folder and named required-plugins.
 * Don't remove the source plugins zip files. Please also update the plugin zip files to
 * it's latest version.
 */

# License key for Ultimate Add-ons for Elementor Pro
define('ULTIMATE_ELEMENTOR_LICENSE_KEY', '294fe0613831a64b5a4d282ad7630e82');

# License key for Gravity Forms
define('GRAVITY_FORMS_LICENSE_KEY', '562db04f8c2b02fcffa7f7c8e56ba56e');

# Plugin installation data
define('GROWTH_OPTIMIZER_PLUGINS', [


    # Elementor PRO
    'elementor-pro' => [

        # Plugin name and source file
        'name' => 'Elementor Pro',
        'file' => 'elementor-pro/elementor-pro.php',
        'url'  => home_url( '/required-plugin/elementor-pro.zip' ),

        # License key
        'license_key' => ''
    ],


    # Ultimate Addons for Elementor Pro
    'ultimate-elementor' => [

        # Plugin name and source file
        'name' => 'Ultimate Addons for Elementor Pro',
        'file' => 'ultimate-elementor/ultimate-elementor.php',
        'url'  => home_url( '/required-plugin/ultimate-elementor.zip' ),

        # License key
        'license_key' => ULTIMATE_ELEMENTOR_LICENSE_KEY
    ],


    # Advance Custom Field PRO
    'advanced-custom-fields-pro' => [

        # Plugin name and source file
        'name' => 'Advance Custom Fields Pro',
        'file' => 'advanced-custom-fields-pro/acf.php',
        'url'  => home_url( '/required-plugin/advanced-custom-fields-pro.zip' ),

        # License key
        # Unique sharing of license key for the starter template easy process.
        # It will no longer call the needed files for the ACF to activate license key
        # since we can only bypass using the wp option value for ACF
        'license_key' => [
            'key'    => get_option('acf_pro_license', ''),
            'status' => get_option('acf_pro_license_status', '')
        ]
    ],      


    # Gravity Forms
    'gravityforms' => [

        # Plugin name and source file
        'name' => 'Gravity Forms',
        'file' => 'gravityforms/gravityforms.php',
        'url'  => home_url( '/required-plugin/gravityforms.zip' ),

        # License key
        'license_key' => GRAVITY_FORMS_LICENSE_KEY
    ],    


    # Growth Optimizer CPT Filter
    'growthoptimizer-cpt-filter' => [

        # Plugin name and source file
        'name' => 'Growth Optimizer CPT Filter',
        'file' => 'growthoptimizer-cpt-filter/growthoptimizer-cpt-filter.php',
        'url'  => home_url( '/required-plugin/growthoptimizer-cpt-filter.zip' ),

        # License key
        'license_key' => ''
    ],


    # Growth Optimizer CPT Filter
    'codeoptimizer-tooltip' => [

        # Plugin name and source file
        'name' => 'Growth Optimizer Tooltip',
        'file' => 'codeoptimizer-tooltip/derick-tooltip.php',
        'url'  => home_url( '/required-plugin/codeoptimizer-tooltip.zip' ),

        # License key
        'license_key' => ''
    ],


    # Classic Editor
    'classic-editor' => [

        # Plugin name and source file
        'name' => 'Classic Editor',
        'file' => 'classic-editor/classic-editor.php',
        'url'  => home_url( '/required-plugin/classic-editor.zip' ),

        # License key
        'license_key' => ''
    ],  


    # Growth Optimizer CPT Filter
    'wp-google-maps' => [

        # Plugin name and source file
        'name' => 'WP Go Maps (formerly WP Google Maps)',
        'file' => 'wp-google-maps/wpGoogleMaps.php',
        'url'  => home_url( '/required-plugin/wp-google-maps.zip' ),

        # License key
        'license_key' => ''
    ],


    # Yoast SEO
    'wordpress-seo' => [

        # Plugin name and source file
        'name' => 'Yoast SEO',
        'file' => 'wordpress-seo/wp-seo.php',
        'url'  => home_url( '/required-plugin/wordpress-seo.zip' ),

        # License key
        'license_key' => ''
    ]

]);