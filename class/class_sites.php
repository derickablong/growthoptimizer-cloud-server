<?php

namespace GO_Cloud;

trait GO_Sites
{
    /**
     * Admin menu
     * @return void
     */
    public function admin_menu()
    {
        # Add custom admin management for sites subscription
        add_menu_page(
            __( $this->plugin_title, 'go-cloud-server' ),
            __( $this->plugin_title, 'go-cloud-server' ),
            'manage_options',
            $this->slug,
            [$this, 'admin_page_subscribe_sites'],
            $this->cloud_url . 'admin/css/logo-icon-white.svg',
            2
        );         
    }


    /**
     * Admin library
     * @return void
     */
    public function library()
    {
        wp_register_style( 
            'go-cloud-server-css', 
            $this->css('admin/css/style'), 
            array(), 
            uniqid(), 
            'all' 
        );

        wp_register_script( 
            'go-cloud-server-script', 
            $this->script('admin/js/script'), 
            array( 'jquery' ), 
            uniqid(), 
            true
        );   

        wp_localize_script( 'go-cloud-server-script', 'cloud_server', array(
            'ajaxurl'     => esc_url( admin_url( 'admin-ajax.php' ) )        
        ) );
    }
    

    /**
     * Get subscribed sites
     * @return void
     */
    public function get_sites()
    {
        global $wpdb;        
        return $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}{$this->db_name}`");
    }


    /**
     * Admin page for sites subscription
     * @return void
     */
    public function admin_page_subscribe_sites()
    {        
        wp_enqueue_style('go-cloud-server-css');
        wp_enqueue_script('go-cloud-server-script');       
        $sites = $this->get_sites();

        $this->template('/admin/sites', [
            'sites' => $sites
        ]);
    }


    /**
     * Site loop item
     * @param object $site
     * @return void
     */
    public function site_item( $site )
    {
        $this->template('/admin/site-item', [
            'site' => $site
        ]);
    }


    /**
     * Ajax Load subscribe sites
     * @return void
     */
    public function subscribed_sites()
    {
        ob_start();
        $sites = $this->get_sites();
        foreach ($sites as $site) {
            do_action('site-item', $site);
        }
        wp_send_json([
            'sites' => ob_get_clean()
        ]);
        wp_die();
    }


    /**
     * Ajax add new site
     * @return void
     */
    public function add_new_site()
    {
        global $wpdb;

        $domain = parse_url($_POST['domain']);
        $token  = $_POST['token'];

        $insert = $wpdb->insert(
            "{$wpdb->prefix}{$this->db_name}",
            [
                'domain' => $domain['host'],
                'token'  => $token
            ]
        );

        wp_send_json([
            'success' => !$insert
        ]);
        wp_die();
    }

    /**
     * Site status change
     * @return void
     */
    public function site_status_change()
    {
        global $wpdb;

        $site_id = $_POST['site_id'];
        $status = $_POST['status'];

        $wpdb->update(
            "{$wpdb->prefix}{$this->db_name}",
            [
                'status' => $status
            ],
            [
                'id' => $site_id
            ]
        );

        wp_send_json([
            'site_id' => $site_id,
            'status' => $status
        ]);
        wp_die();
    }   
}