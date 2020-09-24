<?php
namespace WOPB;

defined('ABSPATH') || exit;

class Options{
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'menu_page_callback' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public static function menu_page_callback() {
        add_menu_page(
            esc_html__( 'Woo Blocks', 'product-blocks' ),
            esc_html__( 'Woo Blocks', 'product-blocks' ),
            'manage_options',
            'wopb-settings',
            array( self::class, 'create_admin_page' ),
            WOPB_URL.'assets/img/menu-panel.svg'
        );
    }

    /**
     * Register a setting and its sanitization callback.
     */
    public static function register_settings() {
       register_setting( 'wopb_options', 'wopb_options', array( self::class, 'sanitize' ) );
    }

    /**
     * Sanitization callback
     */
    public static function sanitize( $options ) {
        if ($options) {
            $settings = self::get_option_settings();
            foreach ($settings as $key => $setting) {
                if (!empty($key)) {
                    $options[$key] = sanitize_text_field($options[$key]);
                }
            }
        }
        return $options;
    }

    public static function get_option_settings(){
        return array(
            'css_save_as' => array(
                'type' => 'select',
                'label' => __('CSS Save Method', 'product-blocks' ),
                'options' => array(
                    'wp_head'   => __( 'Header','product-blocks' ),
                    'filesystem' => __( 'File System','product-blocks' ),
                ),
                'default' => 'wp_head',
                'desc' => __('Select where you want to save CSS.', 'product-blocks' )
            ),
            'container_width' => array(
                'type' => 'number',
                'label' => __('Container Width', 'product-blocks' ),
                'default' => '1140',
                'desc' => __('Change Container Width.', 'product-blocks' )
            ),
            'hide_import_btn' => array(
                'type' => 'switch',
                'label' => __('Hide Import Button', 'ultimate-post'),
                'default' => '',
                'desc' => __('Hide Import Layout Button from the Gutenberg Editor.', 'ultimate-post')
            ),
        );
    }

    public static function get_recommended_themes(){
        $recommended_themes = array(
            'coblog' => array(
                'name'  => 'Coblog',
                'slug'  => 'coblog',
                'url'   => 'https://wordpress.org/themes/coblog/',
                'logo'  => WOPB_URL.'assets/img/WordPress.png'
            ),
            'storefront' => array(
                'name'  => 'Storefront',
                'slug'  => 'storefront',
                'url'   => 'https://wordpress.org/themes/storefront/',
                'logo'  => WOPB_URL.'assets/img/WordPress.png'
            )
        );

        $html = '';
        foreach ($recommended_themes as $key => $value) {
            $html .= '<div class="wopb-recommended-theme">';
                $html .= '<div class="wopb-recommended-image">';
                    $html .= '<img src="'.$value['logo'].'" alt="'.$value['name'].'" />';
                $html .= '</div>';
                $html .= '<div class="wopb-recommended-name"><a target="_blank" href="'.$value['url'].'">'.$value['name'].'</a></div>';
                $html .= '<div class="wopb-recommended-button">';
                    $html .= '<a target="_blank" href="'.$value['url'].'" class="button button-success">'.__('Download Now').'</a>';
                $html .= '</div>';
            $html .= '</div>';
        }
        echo $html;
    }

    public static function get_changelog_data() {
        $html = '';
        $resource_data = file_get_contents(WOPB_PATH.'/readme.txt', "r");
        $data = array();
        if ($resource_data) {
            $resource_data = explode('== Changelog ==', $resource_data);
            if (isset($resource_data[1])) {
                $resource_data = $resource_data[1];
                $resource_data = explode("\n", $resource_data);
                $inner = false;
                $count = -1;
                
                foreach ($resource_data as $element) {
                    if ($element){
                        if (substr_count($element, '=') > 1) {
                            $count++;
                            $temp = trim(str_replace('=', '', $element));
                            if (strpos($temp, '-') !== false) {
                                $temp = explode('-', $temp);
                                $data[$count]['date'] = trim($temp[1]);
                                $data[$count]['version'] = trim($temp[0]);
                            }
                        }
                        if (strpos($element, '* New:') !== false) {
                            $data[$count]['new'][] = trim(str_replace('* New:', '', $element));
                        }
                        if (strpos($element, '* Fix:') !== false) {
                            $data[$count]['fix'][] = trim(str_replace('* Fix:', '', $element));
                        }
                        if (strpos($element, '* Update:') !== false) {
                            $data[$count]['update'][] = trim(str_replace('* Update:', '', $element));
                        }
                    }
                }
            }
        }
        if (!empty($data)) {
            foreach ($data as $k => $inner_data) {
                $html .= '<div class="wopb-changelog-wrap">';
                foreach ($inner_data as $key => $changelog) {
                    if ($key == 'date') {
                        $html .= '<div class="wopb-changelog-date">'.__('Released on ', 'product-blocks' ).' '.$changelog.'</div>';
                    } elseif($key == 'version') {
                        $html .= '<div class="wopb-changelog-version">'.__('Version', 'product-blocks' ).' : '.$changelog.'</div>';
                    } else {
                        foreach ($changelog as $keyword => $val) {
                            $html .= '<div class="wopb-changelog-title"><span class="changelog-'.$key.'">'.$key.'</span>'.$val.'</div>';
                        }
                    }
                }
                $html .= '</div>';
            }
        }
        echo $html;
    }
    
    public static function get_settings_data() {
        $html = '';
        $option_data = get_option( 'wopb_options' );
        if(!$option_data){
			$option_data = wopb_function()->init_set_data();	
        }
        $data = self::get_option_settings();
        $html .= '<div class="wopb-settings">';
            $html .= '<input type="hidden" name="option_page" value="wopb_options" />';
            $html .= '<input type="hidden" name="action" value="update" />';
            $html .= wp_nonce_field( "wopb_options-options" );
            foreach ($data as $key => $value) {
                $html .= '<div class="wopb-settings-wrap">';
                    $html .= '<div class="wopb-settings-label">'.$value['label'].'</div>';
                    $html .= '<div class="wopb-settings-field-wrap">';
                        switch ($value['type']) {

                            case 'select':
                                $html .= '<div class="wopb-settings-field">';
                                    $val = isset($option_data[$key]) ? $option_data[$key] : (isset($value['default']) ? $value['default'] : '');
                                    $html .= '<select name="wopb_options['.$key.']">';
                                        foreach ( $value['options'] as $id => $label ) {
                                            $html .= '<option value="'.$id.'" '.( $val == $id ? ' selected="selected"':'').'>';
                                            $html .= strip_tags( $label );
                                            $html .= '</option>';
                                        }
                                        $html .= '</select>';
                                    $html .= '<p class="description">'.$value['desc'].'</p>';
                                $html .= '</div>';
                                break;

                            case 'color':
                                $html .= '<div class="wopb-settings-field">';
                                    $val = isset($option_data[$key]) ? $option_data[$key] : (isset($value['default']) ? $value['default'] : '');
                                    $html .= '<input name="wopb_options['.$key.']" value="'.$val.'" class="wopb-color-picker" />';
                                    $html .= '<p class="description">'.$value['desc'].'</p>';
                                $html .= '</div>';
                                break;

                            case 'number':
                                $html .= '<div class="wopb-settings-field">';
                                    $val = isset($option_data[$key]) ? $option_data[$key] : (isset($value['default']) ? $value['default'] : '');
                                    $html .= '<input type="number" name="wopb_options['.$key.']" value="'.$val.'"/>';
                                    $html .= '<p class="description">'.$value['desc'].'</p>';
                                $html .= '</div>';
                                break;


                            case 'switch':
                                $html .= '<div class="wopb-settings-field">';
                                    $val = isset($option_data[$key]) ? $option_data[$key] : (isset($value['default']) ? $value['default'] : '');
                                    $html .= '<input type="checkbox" value="yes" name="wopb_options['.$key.']" '.($val == 'yes' ? 'checked' : '').' />';
                                    $html .= '<p class="description">'.$value['desc'].'</p>';
                                $html .= '</div>';
                                break;

                            default:
                                # code...
                                break;

                        }
                    $html .= '</div>';
                $html .= '</div>';        
            }
            $html .= '<div class="wopb-settings-wrap">';
            $html .= '<div></div>'.get_submit_button();
            $html .= '</div>';

        $html .= '</div>';

        
        
        echo '<form method="post" action="options.php">'.$html.'</form>';
    }


    public static function get_support_data() {
        $html = '';
        $html .= '<div class="wopb-admin-sidebar">';

            $html .= '<div class="wopb-admin-card wopb-sidebar-card">';
                $html .= '<h3 class="wopb-sidebar-title">'.esc_html__( 'Coblog', 'product-blocks' ).'</h3>';
                $html .= '<p class="wopb-sidebar-content">'.esc_html__( 'Coblog is beautifully designed clean WordPress blog theme.', 'product-blocks' ).'</p>';
                $html .= '<h4>'.esc_html__( 'Core Features', 'product-blocks' ).'</h4>';
                $html .= '<ul class="wopb-sidebar-list">';
                    $html .= '<li>'.esc_html__( 'Responsive Ready', 'product-blocks' ).'</li>';
                    $html .= '<li>'.esc_html__( 'Beautifully Crafted ', 'product-blocks' ).'</li>';
                    $html .= '<li>'.esc_html__( 'Advanced Option Panel', 'product-blocks' ).'</li>';
                    $html .= '<li>'.esc_html__( 'Unlimited Color Options', 'product-blocks' ).'</li>';
                    $html .= '<li>'.esc_html__( 'WooCommerce Integrated', 'product-blocks' ).'</li>';
                $html .= '</ul>';
                $html .= '<a class="button button-success" target="_blank" href="https://wordpress.org/themes/coblog/">'.__('Free Download', 'product-blocks').'</a>';
            $html .= '</div>';//wopb-admin-card


            $html .= '<div class="wopb-admin-card wopb-sidebar-card">';
                $html .= '<h3 class="wopb-sidebar-title">'.esc_html__( 'Storefront', 'product-blocks' ).'</h3>';
                $html .= '<p class="wopb-sidebar-content">'.esc_html__( 'Responsive WordPress Theme store theme built for shop and product showcase', 'product-blocks' ).'</p>';
                $html .= '<h4>'.esc_html__( 'Core Features', 'product-blocks' ).'</h4>';
                $html .= '<ul class="wopb-sidebar-list">';
                    $html .= '<li>'.esc_html__( 'Based On WooCommerce', 'product-blocks' ).'</li>';
                    $html .= '<li>'.esc_html__( 'Responsive Ready', 'product-blocks' ).'</li>';
                    $html .= '<li>'.esc_html__( 'Beautifully Crafted ', 'product-blocks' ).'</li>';
                    $html .= '<li>'.esc_html__( 'Advanced Option Panel', 'product-blocks' ).'</li>';
                    $html .= '<li>'.esc_html__( 'Unlimited Color Options', 'product-blocks' ).'</li>';
                $html .= '</ul>';
                $html .= '<a class="button button-success" target="_blank" href="https://wordpress.org/themes/storefront/">'.__('Free Download', 'product-blocks').'</a>';
            $html .= '</div>';//wopb-admin-card
            
        $html .= '</div>';//wopb-admin-sidebar
        echo $html;
    }

    /**
     * Settings page output
     */
    public static function create_admin_page() { ?>
        <style>
            /* ----Common--- */
            #wpbody-content, #wpwrap{
                background-color: #f2f2f2;
                -webkit-font-smoothing: subpixel-antialiased;
            }
            .error, .notice {
                display: none;
            }
            #wpcontent {
                padding-left: 0px;
            }
            .wopb-option-body {
                position: relative;
                font-size: 15px;
                max-width: 100%;
                display:block;
            }

            /* ----Header--- */
            .wopb-setting-header {
                width: 100%;
                display: flex;
                align-items: center;
                padding: 20px 40px;
                box-sizing: border-box;
                background: #fff;
            }
            .wopb-setting-header img {
                margin-left:auto;
                max-width: 180px;    
            }
            .wopb-setting-header-info h1 {
                margin: 0;
                font-weight: 300;
                font-size: 35px;
                line-height: normal;
                color: #000;
            }
            .wopb-setting-header-info p {
                font-size: 14px;
                margin-top: 5px;
                margin-bottom: 0;
                font-weight:300;
            }
            .wopb-setting-header-info p a {
                margin-left: 5px;
                text-decoration: none;
                color: #FF4747;
            }
            .wopb-setting-header-info p a span {
                color: #FF9920;
                margin-left: 10px;
                font-size: 18px;
                letter-spacing: 4px;
            }




            /* ----common--- */
            .wopb-admin-card {
                box-shadow: 0 0 10px -5px rgba(0,0,0,0.5);
                background-color: #fff;
            }
            .wopb-title {
                margin-top:0;
                color: #000;
            }
            .wopb-overview {
                padding: 50px;
                display:flex;
            }
            .wopb-overview-content {
                max-width: 50%;
                padding-right: 30px;
            }
            .wp-core-ui .button-primary {
                background: #006ade;
                border-color: #006ade;
                border-radius: 2px;
                padding: 3px 15px;
                font-weight: 400;
                height: auto;
            }
            .wp-core-ui .button.button-success {
                background: #14bd62;
                border-color: #14bd62;
                color: #fff;
                margin-left: 10px;
                transition: 400ms;
                border-radius: 2px;
                padding: 3px 15px;
                font-weight: 400;
                height: auto;
            }
            .wp-core-ui .button.button-success:hover {
                background: #14d26c;
                border-color: #14d26c;
            }
            .wp-core-ui .button.button-primary:hover {
                background: #007afe;
                border-color: #007afe;
            }
            .wp-core-ui .button.button-success:focus {
                box-shadow: 0 0 0 1px #10b35b;
            }
            .wp-core-ui .button.button-primary:focus {
                box-shadow: 0 0 0 1px #016BDF;
            }
            .wopb-overview-text {
                font-size: 14px;
                font-weight: 300;
                line-height: 25px;
            }
            .wopb-overview-text .button {
                margin-top: 30px;
            }
            .wopb-overview-feature {
                margin-top: 40px;
            }

            .wopb-dashboard-list {
                list-style: none;
                padding: 0;
            }
            .wopb-dashboard-list li {
                font-size: 14px;
                font-weight: 300;
                line-height: 26px;
                position: relative;
                padding: 0 20px;
                display: inline-block;
                width: 40%;
            }
            .wopb-dashboard-list li:after {
                content: "";
                left: 0;
                width: 8px;
                height: 8px;
                border-radius: 100px;
                background: #dcdcdc;
                position: absolute;
                top: 50%;
                margin-top: -4px;
            }



            /* ----Sidebar--- */
            .wopb-sidebar-card {
                padding: 0 20px 25px;
                margin-bottom: 30px;
            }
            .wopb-sidebar-title {
                margin: 0 -20px 0;
                background: #FAFAFA;
                padding: 15px 20px;
                font-size: 16px;
                border-bottom: 1px solid #EEEEEE;
                color: #000;
            }
            .wopb-sidebar-card h4 {
                color: #000;
                margin-top: 25px;
            }
            .wopb-sidebar-list {
                list-style: none;
                padding: 0;
                margin-bottom: 30px;
            }
            .wopb-sidebar-list li {
                font-size: 13px;
                padding: 0 20px;
                position: relative;
                margin-right: auto;
                line-height: 24px;
            }
            .wopb-sidebar-list li:after {
                content: "";
                left: 0;
                width: 8px;
                height: 8px;
                border-radius: 100px;
                background: #dcdcdc;
                position: absolute;
                top: 50%;
                margin-top: -4px;
            }
            .wopb-sidebar-content {
                margin-top: 25px;
            }

            /* ----Tab--- */
            .wopb-content-wrap {
                padding: 40px;
                display: flex;
                width: 100%;
                box-sizing: border-box;
            }
            .wopb-tab-content-wrap {
                width: 75%;
                margin-right: 30px;
                -webkit-box-flex: 0;
                -ms-flex: 0 0 75%;
                flex: 0 0 75%;
                max-width: 75%;
            }

            .wopb-tab-title-wrap{
                padding: 0 40px;
                background-color: #ffffff;
                border-bottom: 3px solid #d5d5d5;
            }
            .wopb-tab-title{
                font-weight: 300;
                color: #555;
                font-size: 15px;
                display: inline-block;
                margin-right: 25px;
                padding: 10px 0;
            }
            .wopb-tab-title:hover{
                cursor: pointer;
            }
            .wopb-tab-title.active{
                border-bottom: 3px solid #FF4747;
                color: #FF4747;
                margin-bottom: -3px;
            }
            .wopb-tab-content{
                display: none;
            }
            .wopb-tab-content.active{
                display: block;
            }

            /* ----Settings--- */
            .wopb-settings-wrap {
                margin-bottom: 20px;
                display: grid;
                grid-template-columns: 0.8fr 1fr;
            }


            /* ----Recomended--- */
            .wopb-recommended-theme{
                max-width: 100%;
                background-color: #ffffff;
                border-radius: 3px;
                box-shadow: 0 0 10px -5px rgba(0,0,0,0.5);
                margin-bottom: 30px;
            }
            .wopb-recommended-image, .wopb-recommended-name, .wopb-recommended-button {
                display: inline-block;
                vertical-align: middle;
            }
            .wopb-recommended-name {
                font-size: 18px;
                margin-left: 20px;
            }
            .wopb-recommended-name a {
                transition: 400ms;
                color: #000000;
                text-decoration: none;
                transition: 400ms;
            }
            .wopb-recommended-name a:hover {
                color: #FF4747;
            }
            .wopb-recommended-image {
                border-right: 1px solid #ececec;
            }
            .wopb-recommended-image img {
                width: 70px;
            }
            .wopb-recommended-button {
                float: right;
                margin: 20px 20px 0 0;
                position: relative;
            }
            .wopb-recommended-button a{
                float: right;
                transition: 200ms;
                text-decoration: none;
            }
            .wopb-recommended-button a:hover{
                cursor: pointer;
            }

            /* ---Video Tutorials--- */
            .wopb-overview-video iframe {
                box-shadow: 0 0 10px -5px rgba(0,0,0,0.5);
            }
            .wopb-video-tutorials {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                grid-gap: 30px;
            }
            .wopb-video-tutorial {
                box-shadow: 0 0 10px -5px rgba(0,0,0,0.5);
                background-color: #fff;
            }
            .wopb-video-tutorial iframe {
                box-shadow: 0 0 10px -5px rgba(0,0,0,0.5);
                width: 100%;
                height: 207px;
            }
            .wopb-video-tutorial h4 {
                color: #000;
                margin: 0;
                padding: 15px 20px 20px;
            }
            /* ---Changelog--- */
            .wopb-changelog-wrap {
                background-color: #ffffff;
                margin: 0 auto 30px;
                padding: 25px 30px;
                position: relative;
                box-shadow: 0 0 10px -5px rgba(0,0,0,0.5);
            }
            .wopb-changelog-date{
                font-size: 14px;
                display: inline-block;
                right: 30px;
                position: absolute;
                font-weight: 300;
            }
            .wopb-changelog-version{
                font-size: 16px;
                color: #000000;
                font-weight: 700;
                margin-bottom: 20px;
            }
            .wopb-changelog-title {
                text-transform: capitalize;
                font-size: 14px;
                color: #555555;
                line-height: 28px;
                font-weight: 300;
                margin-bottom: 8px;
            }
            .wopb-changelog-title > span {
                padding: 2px 10px;
                border-radius: 3px;
                margin-right: 10px;
            }
            .changelog-fix{
                color: #721c24;
                background-color: #f8d7da;
                border-color: #f5c6cb;
            }
            .changelog-update{
                color: #856404;
                background-color: #fff3cd;
                border-color: #ffeeba;
            }
            .changelog-new{
                color: #155724;
                background-color: #d4edda;
                border-color: #c3e6cb;
            }

            /* ----Responsive--- */
            @media (max-width: 1500px) {
                .wopb-overview {
                    flex-wrap: wrap;
                    flex-direction: column-reverse;
                }
                .wopb-overview-content {
                    max-width: 100%;
                }
                .wopb-overview-video {
                    margin-bottom: 40px;
                }
                .wopb-overview-video iframe {
                    width: 640px;
                    height: 360px;
                    box-shadow: 0 0 10px -5px rgba(0,0,0,0.5);
                }
                .wopb-video-tutorials {
                    grid-template-columns: 1fr 1fr;
                }
            }
            @media (max-width: 1400px) {
                .wopb-sidebar-card .button.button-primary {
                    text-align: center;
                    display: block;
                }
                .wopb-sidebar-card .button.button-success {
                    text-align: center;
                    display: block;
                    margin-left: 0;
                    margin-top: 10px;
                }
            }
            @media (max-width: 1200px) {
                .wopb-content-wrap {
                    flex-wrap: wrap;
                }
                .wopb-tab-content-wrap {
                    width: 100%;
                    margin-right: 0;
                    -webkit-box-flex: 0;
                    -ms-flex: 0 0 100%;
                    flex: 0 0 100%;
                    max-width: 100%;
                }
                .wopb-admin-sidebar {
                    margin-top: 40px;
                }
            }
            @media (max-width: 1000px) {
                .wopb-setting-header-info h1 {
                    font-size: 28px;
                }
                .wopb-overview-video iframe {
                    width: 350px;
                    height: 250px;
                }
                .wopb-setting-header {
                    padding: 20px 30px;
                }
                .wopb-tab-title-wrap {
                    padding: 0 30px;
                }
                .wopb-overview {
                    padding: 30px;
                }
                .wopb-video-tutorials {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="wopb-option-body">
            <div class="wopb-setting-header">
                <div class="wopb-setting-header-info">
                    <h1>
                        <?php _e('Welcome to <strong>Product Blocks</strong> - Version', 'product-blocks'); ?><span> <?php echo WOPB_VER; ?></span>
                    </h1>
                    <p><?php esc_html_e('Most Powerful & Advanced Gutenberg Product Blocks for WooCommerce', 'product-blocks'); ?><a href="https://wordpress.org/support/plugin/product-blocks/reviews/#new-post"><?php esc_html_e('Rate the plugin', 'product-blocks'); ?><span>★★★★★<span></a></p>
                </div>
                <img src="<?php echo WOPB_URL.'assets/img/logo-option.svg'; ?>" alt="<?php _e('WooCommerce Product Blocks', 'product-blocks'); ?>">
            </div>
            <div class="wopb-tab-wrap">
                <div class="wopb-tab-title-wrap">
                    <div class="wopb-tab-title active"><?php _e('Getting Started', 'product-blocks'); ?></div>
                    <div class="wopb-tab-title"><?php _e('General Settings', 'product-blocks'); ?></div>
                    <div class="wopb-tab-title"><?php _e('Recommended Theme', 'product-blocks'); ?></div>
                    <div class="wopb-tab-title"><?php _e('Changelog', 'product-blocks'); ?></div>
                </div>
                <div class="wopb-content-wrap">
                    <div class="wopb-tab-content-wrap">
                        <div class="wopb-tab-content active"><!-- #Recommended Theme Content -->
                            <div class="wopb-overview wopb-admin-card">
                                <div class="wopb-overview-content">
                                    <div class="wopb-overview-text">
                                        <h3 class="wopb-title"><?php esc_html_e( 'Quick Overview', 'product-blocks' ); ?></h3>
                                        <?php esc_html_e('Gutenberg WooCommerce Product Blocks is a Gutenberg Product Blocks plugins for WooCommerce to creating beautiful WooCommerce grid blocks, post listing blocks, post slider blocks and post carousel blocks within a few seconds.', 'product-blocks'); ?>
                                        <div>
                                        </div>
                                    </div><!--/.wopb-about-text-->
                                    <div class="wopb-overview-feature">
                                        <h3 class="wopb-title"><?php esc_html_e( 'Core Features', 'product-blocks' ); ?></h3>
                                        <ul class="wopb-dashboard-list">
                                            <li><?php _e( 'WooCommerce Category Filter', 'product-blocks' ); ?></li>
                                            <li><?php _e( '8 Beautifully Crafted Gutenberg Blocks', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'WooCommerce Product Listing', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'WooCommerce Product Grid', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'Google Font Support', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'Advanced Post Query Builder', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'SVG Custom Icon', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'Load More with AJAX Powered', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'Navigation with AJAX Powered', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'Responsive Settings', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'Advanced Typography Control', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'Animation Support', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'Custom CSS Option', 'product-blocks' ); ?></li>
                                            <li><?php _e( 'Section Title Control', 'product-blocks' ); ?></li>
                                        </ul>
                                    </div><!--/.wopb-overview-feature-->
                                </div><!--/.wopb-overview-content-->
                            </div><!--/.wopb-dashboard-->
                        
                        </div>
                        <div class="wopb-tab-content"><!-- #Settings Content -->
                            <div class="wopb-overview wopb-admin-card"><!-- #Settings Content --> 
                                <?php self::get_settings_data(); ?>
                            </div>
                        </div>
                        <div class="wopb-tab-content"><!-- #Recommended Theme Content -->
                            <div class="wopb-admin-themes"><!-- #Settings Content --> 
                                <?php self::get_recommended_themes(); ?>
                            </div>
                        </div>
                        <div class="wopb-tab-content"><!-- #Changelog Content -->
                            <?php self::get_changelog_data(); ?>
                        </div>
                    </div>
                    <?php self::get_support_data(); ?>
                </div>
            </div>
            
            <script type="text/javascript">
                jQuery( document ).ready(function() {
                    jQuery( document ).on( "click", '.wopb-tab-title', function(e){ 
                        jQuery(this).closest('.wopb-tab-wrap').find('.wopb-tab-title').removeClass('active').eq(jQuery(this).index()).addClass('active')
                        jQuery(this).closest('.wopb-tab-wrap').find('.wopb-tab-content').removeClass('active').eq(jQuery(this).index()).addClass('active');
                    });
                });
            </script>
        </div>

    <?php }
}

