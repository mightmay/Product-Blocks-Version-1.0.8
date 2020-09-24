<?php
namespace WOPB;

defined('ABSPATH') || exit;

class Styles {
    public function __construct(){
		$this->require_block_css();
		add_action('rest_api_init', array($this, 'save_block_css_callback'));
	}

	// API Routes for save CSS
	public function save_block_css_callback(){
		register_rest_route(
			'wopb/v1', 
			'/save_block_css/',
			array(
				array(
					'methods'  => 'POST', 
					'callback' => array( $this, 'save_block_content_css'),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
					'args' => array()
				)
			)
		);
		
		register_rest_route(
			'wopb/v1',
			'/get_posts/',
			array(
				array(
					'methods'  => 'POST',
					'callback' => array($this, 'get_posts_call'),
					'permission_callback' => function () {
						return current_user_can('edit_posts');
					},
					'args' => array()
				)
			)
		);

		register_rest_route(
			'wopb/v1',
			'/appened_css/',
			array(
				array(
					'methods'  => 'POST',
					'callback' => array($this, 'appened_css_call'),
					'permission_callback' => function () {
						return current_user_can('edit_posts');
					},
					'args' => array()
				)
			)
		);

	}

	public function appened_css_call($server) {
			$post = $server->get_params();
			$css = $post['inner_css'];
			$post_id = (int) sanitize_text_field($post['post_id']);
			if( $post_id ){
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				$filename = "wopb-css-{$post_id}.css";
				$dir = trailingslashit(wp_upload_dir()['basedir']).'product-blocks/';
				if(file_exists($dir.$filename)) {
					$file = fopen($dir.$filename, "a");
					fwrite($file, $css);
					fclose($file);
				}
				$get_data = get_post_meta($post_id, '_wopb_css', true);
				update_post_meta($post_id, '_wopb_css', $get_data.$css);
				wp_send_json_success(array('success' => true, 'message' => __('Data retrive done', 'product-blocks' )));
			}else{
				return array('success' => false, 'message' => __('Data not found!!', 'product-blocks' ));
			}
	}

	public function get_posts_call($server) {
		$post = $server->get_params();
		if (isset($post['postId'])) {
			return array('success' => true, 'data'=> get_post($post['postId'])->post_content, 'message' => __('Data retrive done', 'product-blocks' ));
		} else {
			return array('success' => false, 'message' => __('Data not found!!', 'product-blocks' ));
		}
	}

	// Save CSS Action in File
	public function  save_block_content_css($request){
		try{
			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			$params 		= $request->get_params();
			$post_id 		= (int) sanitize_text_field($params['post_id']);
			$filename 		= "wopb-css-{$post_id}.css";
			$upload_dir_url = wp_upload_dir();
			$dir 			= trailingslashit($upload_dir_url['basedir']) . 'product-blocks/';

			if ($params['has_block']) {
				update_post_meta($post_id, '_wopb_active', 'yes');
				$wopb_block_css = $this->set_top_css($params['block_css']);
				WP_Filesystem( false, $upload_dir_url['basedir'], true );
				if( ! $wp_filesystem->is_dir( $dir ) ) {
					$wp_filesystem->mkdir( $dir );
				}
				if ( ! $wp_filesystem->put_contents( $dir . $filename, $wopb_block_css ) ) {
					throw new Exception(__('CSS can not be saved due to permission!!!', 'product-blocks' )); 
				}
				update_post_meta($post_id, '_wopb_css', $wopb_block_css);
				return ['success'=>true, 'message'=>__('Product Blocks css file has been updated.', 'product-blocks' )];
			} else {
				delete_post_meta($post_id, '_wopb_active');
				if (file_exists($dir.$filename)) {
					unlink($dir.$filename);
				}
				delete_post_meta($post_id, '_wopb_css');
			}
		}catch(Exception $e){
			return [ 'success'=> false, 'message'=> $e->getMessage() ];
        }
	}

	// Save Import CSS in the top of the File
	public function set_top_css($get_css = ''){
		$css_url = "@import url('https://fonts.googleapis.com/css?family=";
		$font_exists = substr_count($get_css, $css_url);
		if ($font_exists){
			$pattern = sprintf('/%s(.+?)%s/ims', preg_quote($css_url, '/'), preg_quote("');", '/'));
			if (preg_match_all($pattern, $get_css, $matches)) {
				$fonts = $matches[0];
				$get_css = str_replace($fonts, '', $get_css);
				if( preg_match_all( '/font-weight[ ]?:[ ]?[\d]{3}[ ]?;/' , $get_css, $matche_weight ) ){
					$weight = array_map( function($val){
						$process = trim( str_replace( array( 'font-weight',':',';' ) , '', $val ) );
						if( is_numeric( $process ) ){
							return $process;
						}
					}, $matche_weight[0] );
					foreach ( $fonts as $key => $val ) {
						$fonts[$key] = str_replace( "');",'', $val ).':'.implode( ',',$weight )."');";
					}
				}
				$fonts = array_unique($fonts);
				$get_css = implode('', $fonts).$get_css;
			}
		}
		return $get_css;
	}


	// Require CSS 
	public function require_block_css(){
		$option_data = get_option( 'wopb_options' );
		if(!$option_data){
			$option_data = wopb_function()->init_set_data();	
        }
		if( isset($option_data['css_save_as']) ){
			$save_as = $option_data['css_save_as'];
			if ($save_as === 'filesystem') {
				add_action('wp_enqueue_scripts', array($this, 'add_block_css_file'));
			} else {
				add_action('wp_head', array( $this, 'add_block_inline_css' ), 100);	
			}
		}
	}


	// Set CSS in File
	public function add_block_css_file(){
        $post_id = get_the_ID();
		if( $post_id ){
			$upload_dir_url = wp_get_upload_dir();
			$upload_css_dir_url = trailingslashit( $upload_dir_url['basedir'] );
			$css_dir_path = $upload_css_dir_url . "product-blocks/wopb-css-{$post_id}.css";
			if ( file_exists( $css_dir_path ) ) {
				$css_dir_url = trailingslashit( $upload_dir_url['baseurl'] );
				$css_url = $css_dir_url . "product-blocks/wopb-css-{$post_id}.css";
				wp_enqueue_style( "wopb-post-{$post_id}", $css_url, array(), WOPB_VER, 'all' );
			} else {
				$css = get_post_meta($post_id, '_wopb_css', true);
				if( $css ) {
					wp_enqueue_style("wopb-post-{$post_id}", $css, false, WOPB_VER);
				}
			}
		}
	}


	// Set Inline CSS in Head
	public function add_block_inline_css() {
        $post_id = get_the_ID();
		if( $post_id ){ 
            $upload_dir_url = wp_get_upload_dir();
            $upload_css_dir_url = trailingslashit( $upload_dir_url['basedir'] );
			$css_dir_path = $upload_css_dir_url."product-blocks/wopb-css-{$post_id}.css";
			if (file_exists( $css_dir_path )) {
				echo '<style type="text/css">'.file_get_contents($css_dir_path).'</style>';
			} else {
				$css = get_post_meta($post_id, '_wopb_css', true);
				if($css) {
					echo '<style type="text/css">'.$css.'</style>';
				}
			}
		}
	}
}