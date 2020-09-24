<?php
namespace WOPB;

defined('ABSPATH') || exit;

class Templates{
    public function __construct(){
        add_filter( 'template_include', array($this, 'set_template_callback') );
        $post_types = get_post_types();
		if( !empty($post_types) ){
			foreach ($post_types as $post_type){
				add_filter( "theme_{$post_type}_templates", array( $this, 'add_template_callback' ) );
			}
        }
    }

    public function set_template_callback($template){
		if ( is_singular() ) {
            if ( get_post_meta( get_the_ID(), '_wp_page_template', true ) === 'wopb_page_template' ) {
                $template = WOPB_PATH . 'classes/template-without-title.php';
            }
		}
		return $template;
    }

    public function add_template_callback($templates){
		$templates['wopb_page_template'] = __('WooCommerce Template', 'product-blocks');
		return $templates;
	}
}