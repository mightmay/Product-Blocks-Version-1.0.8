<?php
namespace WOPB;

defined('ABSPATH') || exit;

class Blocks {

    private $all_blocks;
    private $api_endpoint = 'https://demo.wpxpo.com/wp-json/restapi/v2/';

    public function __construct(){
        $this->blocks();
		add_action('wp_ajax_wopb_load_more',        array($this, 'wopb_load_more_callback'));       // Next Previous AJAX Call
		add_action('wp_ajax_nopriv_wopb_load_more', array($this, 'wopb_load_more_callback'));       // Next Previous AJAX Call
        add_action('wp_ajax_wopb_filter',           array($this, 'wopb_filter_callback'));          // Next Previous AJAX Call
        add_action('wp_ajax_nopriv_wopb_filter',    array($this, 'wopb_filter_callback'));          // Next Previous AJAX Call
        add_action('wp_ajax_wopb_pagination',       array($this, 'wopb_pagination_callback'));      // Page Number AJAX Call
        add_action('wp_ajax_nopriv_wopb_pagination',array($this, 'wopb_pagination_callback'));      // Page Number AJAX Call
        add_action('wp_ajax_wopb_quick_view',       array($this, 'wopb_quick_view_callback'));      // Quick View AJAX Call
        add_action('wp_ajax_nopriv_wopb_quick_view',array($this, 'wopb_quick_view_callback'));      // Quick View AJAX Call
        add_action('wp_ajax_get_all_layouts',       array($this, 'get_all_layouts_callback'));      // All Layout AJAX Call
        add_action('wp_ajax_nopriv_get_all_layouts',array($this, 'get_all_layouts_callback'));      // All Layout AJAX Call
        add_action('wp_ajax_get_all_sections',      array($this, 'get_all_sections_callback'));     // All Section AJAX Call
        add_action('wp_ajax_nopriv_get_all_sections',array($this, 'get_all_sections_callback'));    // All Section AJAX Call
        add_action('wp_ajax_get_single_section',    array($this, 'get_single_section_callback'));   // Page Number AJAX Call
        add_action('wp_ajax_nopriv_get_single_section',array($this, 'get_single_section_callback'));// Page Number AJAX Call
    }

    // Require Blocks
    public function blocks() {
        require_once WOPB_PATH.'blocks/Heading.php';
        require_once WOPB_PATH.'blocks/Product_Grid_1.php';
        require_once WOPB_PATH.'blocks/Product_Grid_2.php';
        require_once WOPB_PATH.'blocks/Product_Grid_3.php';
        require_once WOPB_PATH.'blocks/Product_List_1.php';
        require_once WOPB_PATH.'blocks/Product_Category_1.php';
        require_once WOPB_PATH.'blocks/Image.php';
        $this->all_blocks['product-blocks_heading'] = new \WOPB\blocks\Heading();
        $this->all_blocks['product-blocks_product-grid-1'] = new \WOPB\blocks\Product_Grid_1();
        $this->all_blocks['product-blocks_product-grid-2'] = new \WOPB\blocks\Product_Grid_2();
        $this->all_blocks['product-blocks_product-grid-3'] = new \WOPB\blocks\Product_Grid_3();
        $this->all_blocks['product-blocks_product-list-1'] = new \WOPB\blocks\Product_List_1();
        $this->all_blocks['product-blocks_product-category-1'] = new \WOPB\blocks\Product_Category_1();
        $this->all_blocks['product-blocks_image'] = new \WOPB\blocks\Image();
    }

    
    // Load More Action
    public function wopb_load_more_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local){
            return ;
        }

        $paged      = sanitize_text_field($_POST['paged']);
        $blockId    = sanitize_text_field($_POST['blockId']);
        $postId     = sanitize_text_field($_POST['postId']);
        $blockRaw   = sanitize_text_field($_POST['blockName']);
        $blockName  = str_replace('_','/', $blockRaw);

        if( $paged && $blockId && $postId && $blockName ) {
            $post = get_post($postId); 
            if (has_blocks($post->post_content)) {
                $blocks = parse_blocks($post->post_content);
                foreach ($blocks as $key => $value) {
                    if($blockName == $value['blockName']) {
                        if($value['attrs']['blockId'] == $blockId){
                            $attr = $this->all_blocks[$blockRaw]->get_attributes(true);
                            $attr['paged'] = $paged;
                            $attr = array_merge($attr, $value['attrs']);
                            echo $this->all_blocks[$blockRaw]->content($attr, true);
                            die();
                        }
                    }
                }
            }
        }
    }


    // Filter Callback
    public function wopb_filter_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local){
            return ;
        }        
     
        $taxtype    = sanitize_text_field($_POST['taxtype']);
        $blockId    = sanitize_text_field($_POST['blockId']);
        $postId     = sanitize_text_field($_POST['postId']);
        $taxonomy   = sanitize_text_field($_POST['taxonomy']);
        $blockRaw   = sanitize_text_field($_POST['blockName']);
        $blockName  = str_replace('_','/', $blockRaw);

        if( $taxtype ) {
            $post = get_post($postId); 
            if (has_blocks($post->post_content)) {
                $blocks = parse_blocks($post->post_content);
                foreach ($blocks as $key => $value) {
                    if($blockName == $value['blockName']) {
                        if($value['attrs']['blockId'] == $blockId) {
                            $attr = $this->all_blocks[$blockRaw]->get_attributes(true);
                            $attr['queryTax'] = $taxtype == 'product_cat' ? 'product_cat' : 'product_tag';
                            if($taxtype == 'product_cat' && $taxonomy) {
                                $attr['queryCat'] = json_encode(array($taxonomy));
                            }
                            if($taxtype == 'product_tag' && $taxonomy) {
                                $attr['queryTag'] = json_encode(array($taxonomy));
                            }
                            $attr = array_merge($value['attrs'], $attr);
                            echo $this->all_blocks[$blockRaw]->content($attr, true);
                            die();
                        }
                    }
                }
            }
        }
    }


    // Pagination Callback
    public function wopb_pagination_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local) {
            return ;
        }

        $paged      = sanitize_text_field($_POST['paged']);
        $blockId    = sanitize_text_field($_POST['blockId']);
        $postId     = sanitize_text_field($_POST['postId']);
        $blockRaw   = sanitize_text_field($_POST['blockName']);
        $blockName  = str_replace('_','/', $blockRaw);

        if($paged) {
            $post = get_post($postId); 
            if (has_blocks($post->post_content)) {
                $blocks = parse_blocks($post->post_content);
                foreach ($blocks as $key => $value) {
                    if($blockName == $value['blockName']) {
                        if($value['attrs']['blockId'] == $blockId) {
                            $attr = $this->all_blocks[$blockRaw]->get_attributes(true);
                            $attr['paged'] = $paged;
                            $attr = array_merge($attr, $value['attrs']);
                            echo $this->all_blocks[$blockRaw]->content($attr, true);
                            die();
                        }
                    }
                }
            }
        }
    }


    // Quick View Callback
    public function wopb_quick_view_callback() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')) {
            return ;
        }

        global $post;
        $postId = sanitize_text_field($_POST['postid']);

        if ($postId) {
            $args = array(
                'post_type'     => 'product',
                'post__in'      => array( $postId ),
                'orderby'       => 'date',
                'post_status'   => 'publish',
                'order'         => 'DESC'
            );
           $loop = new \WP_Query($args);
           $html = '';
            if($loop->have_posts()){
                while($loop->have_posts()) {
                    $loop->the_post(); 
                    $post_id = get_the_ID();
                    $product = wc_get_product($post_id);
                    if(has_post_thumbnail()){
                        $img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
                    }
                    $html .= '<div class="wopb-quick-view-modal">';
                        
                        $html .= '<div class="wopb-quick-view-image">';
                            $html .= '<a href="'.get_permalink().'"><img alt="'.get_the_title().'" src="'.$img[0].'" /></a>';
                            // Save - %
                            $save_percentage = ($product->get_sale_price() && $product->get_regular_price()) ? round( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() * 100 ).'%' : '';
                            if($save_percentage){
                                $html .= '<span class="wopb-quick-view-sale">-'.$save_percentage.' <span>'.__('Sale!', 'product-blocks').'</span></span>';
                            }
                        $html .= '</div>';//wopb-quick-view-image

                        $html .= '<div class="wopb-quick-view-content">';
                            
                            // Review
                            $html .= '<div class="wopb-star-rating">';
                                $html .= '<span style="width: '.(($product->get_average_rating() / 5 ) * 100).'%">';
                                    $html .= '<strong itemprop="ratingValue" class="wopb-rating">'.$product->get_average_rating().'</strong>';
                                    $html .= __('out of 5', 'product-blocks');
                                $html .= '</span>';
                            $html .= '</div>';
                            $html .= '<span class="wopb-review-count">( '.$product->get_rating_count().__(' customer review', 'product-blocks').' )</span>';
                            
                            // Title
                            $html .= '<h3 class="wopb-quick-title"><a href="'.get_permalink().'">'.get_the_title().'</a></h3>';
                            $html .= '<div class="wopb-quick-divider"></div>';
                            
                            // Price
                            $html .= '<div class="wopb-product-price">'.$product->get_price_html().'</div>';
                            
                            // Short Description
                            $html .= '<div class="wopb-quick-description">'.get_the_excerpt().'</div>';
                            
                            // Stock
                            if($product->get_stock_status() == 'outofstock'){
                                $html .= '<div class="wopb-quick-outofstock"><span>'.__('AVAILABILITY:', 'product-blocks').'</span> '.__('Out of Stock', 'product-blocks').'</div>';
                            }
                            if($product->get_stock_status() == 'instock'){
                                $html .= '<div class="wopb-quick-instock"><span>'.__('AVAILABILITY:', 'product-blocks').'</span> '.__('In Stock', 'product-blocks').'</div>';
                            }

                            // SKU
                            $html .= '<div class="wopb-quick-sku"><span>'.__('SKU:', 'product-blocks').'</span>'.$product->get_sku().'</div>';

                            // Add To Cart
                            $html .= '<div class="wopb-add-to-cart">';
                                $html .= '<div class="wopb-quantity-add">';
                                    $html .= '<span class="wopb-add-to-cart-minus">-</span>'; 
                                    $html .= '<input type="number" class="wopb-add-to-cart-quantity" value="1" />';
                                    $html .= '<span class="wopb-add-to-cart-plus">+</span>';
                                $html .= '</div>';
                                $html .= '<div class="wopb-cart-btn">';
                                    $html .= wopb_function()->get_add_to_cart($product);
                                $html .= '</div>';
                            $html .= '</div>';
                    
                            // Category
                            $cat = get_the_terms($post_id, 'product_cat');
                            if(!empty($cat)) {
                                $html .= '<div class="wopb-quick-category">';
                                $html .= '<span>'.__('Category:', 'product-blocks').'</span>';
                                foreach ($cat as $val) {
                                    $html .= '<a href="'.get_term_link($val->term_id).'">'.$val->name.'</a> ';
                                }
                                $html .= '</div>';
                            }

                            // Tag
                            $tag = get_the_terms($post_id, 'product_tag');
                            if(!empty($tag)) {
                                $html .= '<div class="wopb-quick-tag">';
                                $html .= '<span>'.__('Tags:', 'product-blocks').'</span>';
                                foreach ($tag as $val) {
                                    $html .= '<a href="'.get_term_link($val->term_id).'">'.$val->name.'</a> ';
                                }
                                $html .= '</div>';
                            }

                        $html .= '</div>';//wopb-quick-view-content
                    $html .= '</div>';//wopb-quick-view-modal
                }
                wp_reset_postdata();
                echo $html;   
            }      
        }
        die();
    }


    // All Layout Callback
    public function get_all_layouts_callback() {
        $request_data = wp_remote_post($this->api_endpoint.'layouts', array('timeout' => 150, 'body' => array('request_from' => 'product-blocks' )));
        if (!is_wp_error($request_data)) {
            return wp_send_json_success(json_decode($request_data['body'], true));
        } else {
			wp_send_json_error(array('messages' => $request_data->get_error_messages()));
        }
    }


    // All Sections Callback
    public function get_all_sections_callback() {
        $request_data = wp_remote_post($this->api_endpoint.'sections', array('timeout' => 150, 'body' => array('request_from' => 'product-blocks' )));
        if (!is_wp_error($request_data)) {
            return wp_send_json_success(json_decode($request_data['body'], true));
        } else {
			wp_send_json_error(array('messages' => $request_data->get_error_messages()));
        }
    }


    // Single Sections Callback
    public function get_single_section_callback(){        
        $template_id = (int) sanitize_text_field($_REQUEST['template_id']);
        if (!$template_id) {
			return false;
        }
        $request_data = wp_remote_post( $this->api_endpoint.'single-section', array('timeout' => 150, 'body' => array('request_from' => 'product-blocks', 'template_id' => $template_id)));
        if (!is_wp_error($request_data)) {
            return wp_send_json_success(json_decode($request_data['body'], true));
        } else {
			wp_send_json_error(array('messages' => $request_data->get_error_messages()));
        }
    }


}