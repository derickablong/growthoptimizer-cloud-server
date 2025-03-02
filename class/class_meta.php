<?php

namespace GO_Cloud;

trait GO_Meta
{
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