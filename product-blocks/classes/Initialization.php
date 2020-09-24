<?php
namespace WOPB;

defined('ABSPATH') || exit;

class Initialization{

    public function __construct(){
        
        $this->requires();

        add_action('wp_head',                       array($this, 'popular_posts_tracker_callback')); // Popular Post Callback
        add_filter('block_categories',              array($this, 'register_category_callback'), 10, 2); // Block Category Register

        add_action('enqueue_block_editor_assets',   array($this, 'register_scripts_back_callback')); // Only editor
        add_action('admin_enqueue_scripts',         array($this, 'register_scripts_option_panel_callback')); // Option Panel
        add_action('wp_enqueue_scripts',            array($this, 'register_scripts_front_callback')); // Both frontend

        register_activation_hook(WOPB_PATH.'product-blocks.php', array($this, 'install_hook')); // Initial Activation Call
        add_action('activated_plugin',              array($this, 'activation_redirect')); // Plugin Activation Call
        add_action('wp_footer',                     array($this, 'footer_modal_callback')); // Footer Text Added
    }

    public function footer_modal_callback(){
        if ('yes' == get_post_meta(get_the_ID(), '_wopb_active', true)) {
            echo '<div class="wopb-modal-wrap">';
                echo '<div class="wopb-modal-body-wrap">';
                    echo '<div class="wopb-modal-body-inner">';
                        echo '<div class="wopb-modal-body"></div>';
                        echo '<div class="wopb-modal-loading"><div class="wopb-loading"></div></div>';
                        echo '<div class="wopb-modal-close"></div>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';
        }
    }

    public function register_scripts_option_panel_callback(){
        if( null !== ( $screen = get_current_screen() ) && 'toplevel_page_wopb-settings' !== $screen->id ) {
            return;
        }
    }

    public function register_scripts_common(){
        wp_enqueue_style('dashicons');
        wp_enqueue_style('wopb-slick-style', WOPB_URL.'assets/css/slick.css', array(), WOPB_VER);
        wp_enqueue_style('wopb-slick-theme-style', WOPB_URL.'assets/css/slick-theme.css', array(), WOPB_VER);
        wp_enqueue_style('wopb-style', WOPB_URL.'assets/css/blocks.style.css', array(), WOPB_VER );
        if(is_rtl()){ 
            wp_enqueue_style('wopb-blocks-rtl-css', WOPB_URL.'assets/css/rtl.css', array(), WOPB_VER); 
        }
        wp_enqueue_script('wopb-flexmenu-script', WOPB_URL.'assets/js/flexmenu.js', array('jquery'), WOPB_VER, true);
        wp_enqueue_script('wopb-slick-script', WOPB_URL.'assets/js/slick.min.js', array('jquery'), WOPB_VER, true);
        wp_enqueue_script('wopb-script', WOPB_URL.'assets/js/wopb.js', array('jquery','wopb-flexmenu-script'), WOPB_VER, true);
        wp_localize_script('wopb-script', 'wopb_data', array(
            'url' => WOPB_URL,
            'ajax' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('wopb-nonce'),
            'isActive' => wopb_function()->isActive()
        ));
    }

    // Backend and Frontend Load script
    public function register_scripts_front_callback() {
        if ('yes' == get_post_meta(get_the_ID(), '_wopb_active', true)) {
            $this->register_scripts_common();
        }
    }

    // Only Backend
    public function register_scripts_back_callback() {
        $this->register_scripts_common();
        if (wopb_function()->is_wc_ready()) {
            wp_enqueue_script(
                'wopb-blocks-fields', 
                WOPB_URL.'assets/js/editor.fields.min.js', array('wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor' ), 
                WOPB_VER, 
                true
            );
            wp_enqueue_script(
                'wopb-blocks-editor-script', 
                WOPB_URL.'assets/js/editor.blocks.min.js', 
                array('wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor', 'wopb-blocks-fields'), 
                WOPB_VER, 
                true
            );
            wp_enqueue_style('wopb-blocks-editor-css', WOPB_URL.'assets/css/blocks.editor.css', array(), WOPB_VER);

            $import = '';
            $options = get_option('wopb_options');
            if(!$options){
                $options = wopb_function()->init_set_data();	
            }
            if (isset($options['hide_import_btn'])) {
                if ($options['hide_import_btn']=='yes') {
                    $import = 'yes';
                }
            }
            
            wp_localize_script('wopb-blocks-editor-script', 'wopb_data', array(
                'url' => WOPB_URL,
                'ajax' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('wopb-nonce'),
                'hide_import_btn' => $import,
                'isActive' => wopb_function()->isActive()
            ));
        }
    }

    // Fire When Plugin First Installs
    public function install_hook() {
        if (!get_option('wopb_options')) {
            wopb_function()->init_set_data();
        }
    }

    public function activation_redirect($plugin) {
        if( $plugin == 'product-blocks/product-blocks.php' ) {
            exit(wp_redirect(admin_url('admin.php?page=wopb-settings')));
        }
    }

    // Require Categories
    public function requires() {
        require_once WOPB_PATH.'classes/Notice.php';        
        require_once WOPB_PATH.'classes/Options.php';
        new \WOPB\Notice();
        new \WOPB\Options();

        if ( wopb_function()->is_wc_ready() ) {
            require_once WOPB_PATH.'classes/REST_API.php';
            require_once WOPB_PATH.'classes/Blocks.php';
            require_once WOPB_PATH.'classes/Styles.php';
            new \WOPB\REST_API();
            new \WOPB\Styles();
            new \WOPB\Blocks();
        }
    }

    // Block Categories
    public function register_category_callback( $categories, $post ) {
        return array_merge(
            array(
                array( 
                    'slug' => 'product-blocks', 
                    'title' => __( 'WooCommerce Blocks', 'product-blocks' ) 
                )
            ), $categories 
        );
    }

    public function popular_posts_tracker_callback($post_id) {
        if (!is_single()){ return; }
        if (function_exists('wopb_function')){ return; }
        global $post;
        if (empty($post_id)) { $post_id = $post->ID; }
        $count = (int)get_post_meta( $post_id, '__post_views_count', true );
        update_post_meta($post_id, '__post_views_count', $count ? (int)$count + 1 : 1 );
    }
}