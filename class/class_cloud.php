<?php

namespace GO_Cloud;

class GO_Cloud_API
{
    use GO_Zip;

    # Title
    public $plugin_title;

    # Slug
    public $slug;

    # Cloud sites variable holder
    public $cloud_sites_option;

    # Cloud directory variable
    public $cloud_dir;

    # Cloud url path
    public $cloud_url;

    # Plugins Repo
    public $repo_folder;

    # Plugins
    public $plugins;

    # DB
    public $db_name;

    /**
     * Initialize
     * 
     * @param string $title
     * @param string $slug
     * @param string $cloud_dir
     * @param string $cloud_url
     * @param string $cloud_sites_option
     * @param array $plugins
     * @param string $repo_folder
     * @param string $db_name
     */
    function __construct( $title, $slug, $cloud_dir, $cloud_url, $cloud_sites_option, $plugins, $repo_folder, $db_name )
    {
        $this->plugin_title       = $title;
        $this->slug               = $slug;
        $this->cloud_dir          = $cloud_dir;
        $this->cloud_url          = $cloud_url;
        $this->cloud_sites_option = $cloud_sites_option;
        $this->plugins            = $plugins;
        $this->repo_folder        = $repo_folder;
        $this->db_name            = $db_name;
    }


    /**
     * Let's generate plugin gip files
     * 
     * @return void
     */
    public function start()
    {
        # Start the system configuration
        $this->actions();

        # Generate plugin zip file
        $this->generate();
    }


    /**
     * Register actions
     * @return void
     */
    public function actions()
    {
        # Create database table
        add_action('admin_init', [$this, 'wp_table']);

        # Admin library
        add_action('admin_enqueue_scripts', [$this, 'library']);

        # End point for template library
        add_action('rest_api_init', [$this, 'rest_api']);

        # Add admin menu
        add_action( 'admin_menu', [$this, 'admin_menu'], 99 );     
       

        # Add metabox to handle visibility of each template
        add_action('admin_init', [$this, 'meta_box']);

        # Ajax add new site
        add_action('wp_ajax_go_add_new_site', [$this, 'add_new_site']);

        # Load subscribed sites
        add_action('wp_ajax_go_subscribed_sites', [$this, 'subscribed_sites']);

        # Adjax site status change
        add_action('wp_ajax_go_site_status_change', [$this, 'site_status_change']);

        # Site item
        add_action('site-item', [$this, 'site_item'], 10, 1);
            
    }


    /**
     * Create end point to share template
     * kit libraries to the customers
     * @return void
     */
    public function rest_api()
    {
        register_rest_route( 'template-kit', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_template_kit']
        ));
        register_rest_route( 'template-categories', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_template_categories']
        ));
        register_rest_route( 'global-settings', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_global_settings']
        ));
        register_rest_route( 'activate-token', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_activate_token']
        )); 
        register_rest_route( 'is-active', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_site_status']
        ));        
        register_rest_route( 'required-plugins', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_required_plugins']
        ));
        register_rest_route( 'custom-fonts', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_custom_fonts']
        ));
        register_rest_route( 'loop-items', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_loop_items']
        ));
        register_rest_route( 'acf', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_acf']
        ));
        register_rest_route( 'gforms', '/v2', array(
            'methods' => 'GET',
            'callback' => [$this, 'api_gforms']
        ));
    }


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
            $this->cloud_url . 'admin/css/style.css', 
            array(), 
            uniqid(), 
            'all' 
        );

        wp_register_script( 
            'go-cloud-server-script', 
            $this->cloud_url . 'admin/js/script.js', 
            array( 'jquery' ), 
            uniqid(), 
            true
        );   

        wp_localize_script( 'go-cloud-server-script', 'cloud_server', array(
            'ajaxurl'     => esc_url( admin_url( 'admin-ajax.php' ) )        
        ) );
    }


    /**
     * Create table to record subscribed sites
     * @return void
     */
    public function wp_table()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}{$this->db_name} (
        id mediumint(9) NOT NULL AUTO_INCREMENT,        
        domain tinytext NOT NULL,
        token text NOT NULL,  
        status VARCHAR(10) NULL DEFAULT 'inactive',
        date_registered TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,      
        PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    
    /**
     * Check fi referrer is subscribe
     * to the cloud server
     * @param mixed $header
     * @return bool
     */
    public function is_authorize( $header )
    {
        global $wpdb;
        
        $domain = $header['referrer'][0];
        $token  = $header['token'][0];
        
        $site = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}{$this->db_name} WHERE token=%s AND domain=%s",
                $token,
                $domain
            )
        );

        if ($site) {
            return $site->status == 'active';
        }
        return false;
    }


    /**
     * Check if site is still subscribed
     * @param WP_REST_Request $req
     * @return bool
     */
    public function api_site_status(\WP_REST_Request $req)
    {
        return $this->is_authorize(
            $req->get_headers()
        );
    }


    /**
     * Required plugins
     * @param WP_REST_Request $req
     * @return mixed
     */
    public function api_required_plugins(\WP_REST_Request $req)
    {        
        $request = $req->get_headers();
        if ( !$this->is_authorize($request) ) {
            return new \WP_Error( '401', esc_html__( 'Not Authorized', 'go-cloud-server' ), array( 'status' => 401 ) );
        }
        $requested_plugin = $request['plugin'][0];
        if (isset($requested_plugin))
            return array_key_exists($requested_plugin, $this->plugins) ? $this->plugins[$requested_plugin] : [];
        return $this->plugins;
    }


    /**
     * Activate API token
     * @param WP_REST_Request $req
     * @return bool
     */
    public function api_activate_token( \WP_REST_Request $req ) {
        global $wpdb;
        
        $request = $req->get_headers();
        $token   = $request['token'][0];
        $domain  = $request['referrer'][0];
        
        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->db_name}",
            [
                'status' => 'active'
            ],
            [
                'token'  => $token,
                'domain' => $domain
            ]
        );
        
        return $result === false ? false : true;        
    }


    /**
     * Custom fonts
     * @param WP_REST_Request $req
     * @return mixed
     */
    public function api_custom_fonts(\WP_REST_Request $req)
    {
        if ( !$this->is_authorize($req->get_headers()) ) {
            return new \WP_Error( '401', esc_html__( 'Not Authorized', 'go-cloud-server' ), array( 'status' => 401 ) );
        }
        $custom_fonts = new \WP_Query(array(
            'post_type'      => 'elementor_font',
            'post_status'    => 'publish',
            'posts_per_page' => -1            
        ));
        $fonts = [];
        if ($custom_fonts->have_posts()): while($custom_fonts->have_posts()): $custom_fonts->the_post();
            global $post;
            $fonts[] = [
                'ID'         => $post->ID,
                'title'      => $post->post_title,
                'slug'       => $post->post_name,
                'font_files' => get_post_meta( $post->ID, 'elementor_font_files', true ),
                'font_face'  => get_post_meta( $post->ID, 'elementor_font_face', true ),
                'edit_last'  => get_post_meta( $post->ID, '_edit_last', true ),
                'edit_lock'  => get_post_meta( $post->ID, '_edit_lock', true )
            ];
        endwhile; endif;
    
        return $fonts;
    }


    /**
     * API for loop items
     * @param WP_REST_Request $req
     * @return mixed
     */
    public function api_loop_items(\WP_REST_Request $req)
    {
        if ( !$this->is_authorize($req->get_headers()) ) {
            return new \WP_Error( '401', esc_html__( 'Not Authorized', 'go-cloud-server' ), array( 'status' => 401 ) );
        }
        $loop_items = new \WP_Query(array(
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [[
                'key'   => '_elementor_template_type',
                'value' => 'loop-item'
            ]]
        ));
        $items = [];
        if ($loop_items->have_posts()): while($loop_items->have_posts()): $loop_items->the_post();
            global $post;
            
            $items[] = [
                'ID'       => $post->ID,
                'title'    => $post->post_title,
                'slug'     => $post->post_name,
                'content'  => $post->post_content,
                'postmeta' => [
                    '_elementor_edit_mode'      => get_post_meta($post->ID, '_elementor_edit_mode', true),
                    '_elementor_template_type'  => get_post_meta($post->ID, '_elementor_template_type', true),
                    '_elementor_version'        => get_post_meta($post->ID, '_elementor_version', true),
                    '_elementor_pro_version'    => get_post_meta($post->ID, '_elementor_pro_version', true),
                    '_edit_lock'                => get_post_meta($post->ID, '_edit_lock', true),
                    '_wp_page_template'         => get_post_meta($post->ID, '_wp_page_template', true),
                    '_elementor_page_settings'  => get_post_meta($post->ID, '_elementor_page_settings', true),
                    '_elementor_data'           => get_post_meta($post->ID, '_elementor_data', true),
                    '_elementor_page_assets'    => get_post_meta($post->ID, '_elementor_page_assets', true),
                    '_elementor_controls_usage' => get_post_meta($post->ID, '_elementor_controls_usage', true),
                    '_elementor_css'            => get_post_meta($post->ID, '_elementor_css', true),
                    '_elementor_screenshot'     => get_post_meta($post->ID, '_elementor_screenshot', true),
                    '_edit_last'                => get_post_meta($post->ID, '_edit_last', true),
                    '_thumbnail_id'             => get_post_meta($post->ID, '_thumbnail_id', true)
                ]
            ];
        endwhile; endif;
    
        return $items;
    }


    /**
     * Get ACF fields
     * @param integer $ID
     * @param string $host
     * @param string $referrer
     * @return array{ID: mixed, comment_status: mixed, guid: array|string, ping_status: mixed, post_content: mixed, post_excerpt: mixed, post_name: mixed, post_parent: mixed, post_title: mixed, post_type: mixed[]}
     */
    public function get_acf_fields($ID, $host, $referrer)
    {
        $acf_fields = new \WP_Query(array(
            'post_type'      => 'acf-field',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'post_parent'    => $ID
        ));
        $items = [];
        if ($acf_fields->have_posts()): while($acf_fields->have_posts()): $acf_fields->the_post();
            global $post;
            
            $items[] = [
                'ID'             => $post->ID,
                'post_type'      => $post->post_type,
                'post_title'     => $post->post_title,
                'post_name'      => $post->post_name,
                'post_parent'    => $post->post_parent,
                'post_content'   => $post->post_content,
                'post_excerpt'   => $post->post_excerpt,
                'comment_status' => $post->comment_status,
                'ping_status'    => $post->ping_status,
                'guid'           => str_replace($host, $referrer, $post->guid)
            ];
        endwhile; endif;
    
        return $items;
    }


    /**
     * API for ACF
     * @param WP_REST_Request $req
     * @return mixed
     */
    public function api_acf( \WP_REST_Request $req )
    {
        if ( !$this->is_authorize($req->get_headers()) ) {
            return new \WP_Error( '401', esc_html__( 'Not Authorized', 'go-cloud-server' ), array( 'status' => 401 ) );
        }
        $header    = $req->get_headers();
        $domain    = parse_url(home_url());
        $acf_items = new \WP_Query(array(
            'post_type'      => 'acf-field-group',
            'post_status'    => 'publish',
            'posts_per_page' => -1
        ));
        $items = [];
        if ($acf_items->have_posts()): while($acf_items->have_posts()): $acf_items->the_post();
            global $post;
            
            $items[] = [
                'ID'             => $post->ID,
                'post_type'      => $post->post_type,
                'post_title'     => $post->post_title,
                'post_name'      => $post->post_name,
                'post_content'   => $post->post_content,
                'post_excerpt'   => $post->post_excerpt,
                'comment_status' => $post->comment_status,
                'ping_status'    => $post->ping_status,
                'guid'           => str_replace($domain['host'], $header['referrer'][0], $post->guid),
                'postmeta'       => [
                    '_edit_last' => get_post_meta($post->ID, '_edit_last', true),
                    '_edit_lock' => get_post_meta($post->ID, '_edit_lock', true)
                ],
                'fields' => $this->get_acf_fields($post->ID, $domain['host'], $header['referrer'][0])
            ];
        endwhile; endif;
    
        return $items;
    }


    /**
     * API for Gravity Forms
     * @param WP_REST_Request $req
     * @return mixed
     */
    public function api_gforms(\WP_REST_Request $req)
    {
        if ( !$this->is_authorize($req->get_headers()) ) {
            return new \WP_Error( '401', esc_html__( 'Not Authorized', 'go-cloud-server' ), array( 'status' => 401 ) );
        }
        global $wpdb;

        $forms    = [];
        $db_forms = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}gf_form WHERE is_active=%d",
                1
            )
        );
        
        if ($db_forms) {
            foreach ($db_forms as $form) {

                # Get form meta
                $db_form_meta = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}gf_form_meta WHERE form_id=%d",
                        $form->id
                    )
                );

                $form_meta = [];

                if ($db_form_meta) {
                    foreach ($db_form_meta as $meta) {
                        $form_meta[] = [
                            'form_id'           => $meta->form_id,
                            'display_meta'      => !empty($meta->display_meta) ? json_decode($meta->display_meta) : [],
                            'entries_grid_meta' => !empty($meta->entries_grid_meta) ? json_decode($meta->entries_grid_meta) : [],
                            'confirmations'     => !empty($meta->confirmations) ? json_decode($meta->confirmations) : [],
                            'notifications'     => !empty($meta->notifications) ? json_decode($meta->notifications) : []
                        ];
                    }
                }


                # Get form revisions
                $db_form_revisions = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}gf_form_revisions WHERE form_id=%d",
                        $form->id
                    )
                );
                
                $form_revisions = [];

                if ($db_form_revisions) {
                    foreach ($db_form_revisions as $revision) {
                        $form_revisions[] = [
                            'form_id'      => $revision->form_id,
                            'display_meta' => !empty($revision->display_meta) ? json_decode($revision->display_meta) : []
                        ];
                    }
                }

                $forms[] = [
                    'form_id'   => $form->id,
                    'title'     => $form->title,
                    'is_active' => $form->is_active,
                    'is_trash'  => $form->is_trash,
                    'meta'      => $form_meta,
                    'revisions' => $form_revisions
                ];
            }
        }
        return $forms;
    }


    /**
     * API for templates
     * @param WP_REST_Request $req
     * @return mixed
     */
    public function api_template_kit( \WP_REST_Request $req )
    {
        
        if ( !$this->is_authorize($req->get_headers()) ) {
            return new \WP_Error( '401', esc_html__( 'Not Authorized', 'go-cloud-server' ), array( 'status' => 401 ) );
        }

        $templates = new \WP_Query(array(
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'     => '_elementor_template_type',
                    'value'   => ['loop-item'],
                    'compare' => 'NOT IN'

                ]
            ]
        ));
    
        $kit = [];
        if ($templates->have_posts()): while($templates->have_posts()): $templates->the_post();
            $post_id = get_the_ID();       
            $is_public = get_post_meta( $post_id, 'template-is-public', true );
            if ($is_public == 'yes') continue;
            $kit[] = [
                'ID'         => $post_id,
                'title'      => get_the_title(),
                'data'       => get_post_meta( $post_id, '_elementor_data', true ),
                'image'      => get_the_post_thumbnail_url(),
                'categories' => get_the_terms( $post_id, 'elementor_library_category' )
            ];
        endwhile; endif;
    
        return $kit;
    }


    /**
     * API for template categories
     * @param WP_REST_Request $req
     * @return mixed
     */
    public function api_template_categories( \WP_REST_Request $req )
    {
        if ( !$this->is_authorize($req->get_headers()) ) {
            return new \WP_Error( '401', esc_html__( 'Not Authorized', 'go-cloud-server' ), array( 'status' => 401 ) );
        }
        return get_terms(array(
            'taxonomy'   => 'elementor_library_category',
            'hide_empty' => false
        ));
    }

    
    /**
     * API for global settings
     * @param WP_REST_Request $req
     * @return mixed
     */
    public function api_global_settings( \WP_REST_Request $req )
    {
        if ( !$this->is_authorize($req->get_headers()) ) {
            return new \WP_Error( '401', esc_html__( 'Not Authorized', 'go-cloud-server' ), array( 'status' => 401 ) );
        }
        $elementor_active_kit = get_option('elementor_active_kit');
        return get_post_meta( $elementor_active_kit, '_elementor_page_settings' );
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
        include $this->cloud_dir . '/admin/sites.php';
    }


    /**
     * Site loop item
     * @param object $site
     * @return void
     */
    public function site_item( $site )
    {
        include $this->cloud_dir . '/admin/site-item.php';
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

    /**
     * Meta box for template privacy
     * @return void
     */
    public function meta_box()
    {
        add_meta_box( 
            'go_cloud_meta', 
            __( 'Template Privacy', 'go-cloud-server' ), 
            [$this, 'metabox_callback'], 
            array( 'elementor_library'), 
            'side', 
            'default' 
        );    
        add_action( 
            'add_meta_boxes', 
            [$this, 'go_cloud_metabox_save']
        );
        add_action( 
            'save_post', 
            [$this, 'go_cloud_metabox_save']
        );
    }


    /**
     * Metabox privacy form field
     * @param object $post
     * @return void
     */
    public function metabox_callback($post)
    {
        wp_nonce_field( basename( __FILE__ ), 'go_cloud_nonce' );
        $go_cloud_stored_meta = get_post_meta( $post->ID );

        $status = isset( $go_cloud_stored_meta['template-is-public'] ) ? 'checked' : '';
        ?>

        <p>
            <label for="meta-text" class="go_cloud-row-title"><?php _e( 'Is public?', 'go-cloud-server' )?></label>
            <input type="checkbox" name="template-is-public" id="template-is-public" value="yes" <?php echo $status ?> />
        </p>

        <?php
    }


    /**
     * Metabox save template privacy
     * @param integer $post_id
     * @return void
     */
    public function go_cloud_metabox_save($post_id)
    {
        // Checks save status
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset( $_POST[ 'go_cloud_nonce' ] ) && wp_verify_nonce( $_POST[ 'go_cloud_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

        // Exits script depending on save status
        if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
            return;
        }

        // Checks for input and sanitizes/saves if needed
        if( isset( $_POST[ 'template-is-public' ] ) ) {
            update_post_meta( $post_id, 'template-is-public', sanitize_text_field( $_POST[ 'template-is-public' ] ) );
        }
    }

}