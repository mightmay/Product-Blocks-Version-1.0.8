<?php
namespace WOPB;

defined('ABSPATH') || exit;

class Functions{

    // Init data
    public function init_set_data(){
        $option_data = array(
            'css_save_as' => 'wp_head',
            'preloader_style' => 'style1',
            'preloader_color' => '#FF4747',
            'container_width' => '1140',
            'hide_import_btn' => ''
        );
        update_option('wopb_options', $option_data);
        return $option_data;
    }

    // is WooCommerce Ready
    public function is_wc_ready(){
        if (file_exists(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php') && in_array('woocommerce/woocommerce.php', (array)get_option('active_plugins', array()))) {
            return true;
        } else {
            return false;
        }
    }

    // Excerpt
    public function excerpt( $post_id, $limit = 55 ) {
        return apply_filters( 'the_excerpt', wp_trim_words( get_the_content( $post_id ) , $limit ) );
    }

    public function get_add_to_cart($product , $cart_text = ''){
        $attributes = array(
            'aria-label'       => $product->add_to_cart_description(),
            'data-quantity'    => '1',
            'data-product_id'  => $product->get_id(),
            'data-product_sku' => $product->get_sku(),
            'rel'              => 'nofollow',
            'class'            => 'add_to_cart_button ajax_add_to_cart',
        ); 
        return apply_filters(
            'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
            sprintf(
                '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
                esc_url( $product->add_to_cart_url() ),
                esc_attr( $product->get_stock_quantity() ),
                $attributes['class'],
                wc_implode_html_attributes( $attributes ),
                $cart_text ? $cart_text : esc_html( $product->add_to_cart_text() )
            ),
            $product
        );
    }

    public function slider_responsive_split($data) {
        if( is_string($data) ) {
            return $data.'-'.$data.'-2-1';
        } else {
            $data = (array)$data;
            return $data['lg'].'-'.$data['md'].'-'.$data['sm'].'-'.$data['xs'];
        }
    }

    // Woo Category Data
    public function get_category_data($catSlug, $number = 40, $type = ''){
        $data = array();

        if($type == 'child'){
            $image = '';
            if( !empty($catSlug) ){
                foreach ($catSlug as $cat) {
                    $parent_term = get_term_by('slug', $cat, 'product_cat');
                    $term_data = get_terms( 'product_cat', array(
                        'hide_empty' => true,
                        'parent' => $parent_term->term_id
                    ));
                    if( !empty($term_data) ){
                        foreach ($term_data as $terms) {
                            $temp = array();
                            $image = '';
                            $thumbnail_id = get_term_meta( $terms->term_id, 'thumbnail_id', true ); 
                            if( $thumbnail_id ){
                                $image = wp_get_attachment_url( $thumbnail_id ); 
                            }
                            $temp['url'] = get_term_link($terms);
                            $temp['name'] = $terms->name;
                            $temp['desc'] = $terms->description;
                            $temp['count'] = $terms->count;
                            $temp['image'] = $image;
                            $temp['image2'] = $number;
                            $data[] = $temp;
                        }
                    }
                }
            }
            return $data;
        }

        if( !empty($catSlug) ){
            foreach ($catSlug as $cat) {
                $image = '';
                $terms = get_term_by('slug', $cat, 'product_cat');
                $thumbnail_id = get_term_meta( $terms->term_id, 'thumbnail_id', true ); 
                if( $thumbnail_id ){
                    $image = wp_get_attachment_url( $thumbnail_id ); 
                }
                $temp['url'] = get_term_link($terms);
                $temp['name'] = $terms->name;
                $temp['desc'] = $terms->description;
                $temp['count'] = $terms->count;
                $temp['image'] = $image;
                $temp['image1'] = $image;
                $data[] = $temp;
            }
        }else{
            $term_data = get_terms( 'product_cat', array(
                'hide_empty' => true,
                'number' => $number
            ));
            if( !empty($term_data) ){
                foreach ($term_data as $terms) {
                    $temp = array();
                    $image = '';
                    $thumbnail_id = get_term_meta( $terms->term_id, 'thumbnail_id', true ); 
                    if( $thumbnail_id ){
                        $image = wp_get_attachment_url( $thumbnail_id ); 
                    }
                    $temp['url'] = get_term_link($terms);
                    $temp['name'] = $terms->name;
                    $temp['desc'] = $terms->description;
                    $temp['count'] = $terms->count;
                    $temp['image'] = $image;
                    $temp['image2'] = $number;
                    $data[] = $temp;
                }
            }
        }
        return $data;
    }

    // Query Builder
    public function get_query($attr) {
        $query_args = array(
            'posts_per_page'    => isset($attr['queryNumber']) ? $attr['queryNumber'] : 3,
            'post_type'         => isset($attr['queryType']) ? $attr['queryType'] : 'product',
            'orderby'           => isset($attr['queryOrderBy']) ? $attr['queryOrderBy'] : 'date',
            'order'             => 'desc',
            'post_status'       => 'publish',
            'paged'             => isset($attr['paged']) ? $attr['paged'] : 1,
        );

        if ( isset($attr['queryOrderBy']) ) {
            switch ($attr['queryOrderBy']) {
                case 'new_old':
                    $query_args['order'] = 'DESC';
                    unset($query_args['orderby']);
                    break;

                case 'title':
                    $query_args['orderby'] = 'title';
                    $query_args['order'] = 'ASC';
                    break;

                case 'title_reversed':
                    $query_args['orderby'] = 'title';
                    $query_args['order'] = 'DESC';
                    break;

                case 'price_low':
                    $query_args['meta_key'] = '_price';
                    $query_args['orderby'] = 'meta_value_num';
                    $query_args['order'] = 'ASC';
                    break;
                    
                case 'price_high':
                    $query_args['meta_key'] = '_price';
                    $query_args['orderby'] = 'meta_value_num';
                    $query_args['order'] = 'DESC';
                    break;

                case 'popular':
                    $query_args['meta_key'] = 'total_sales';
                    $query_args['orderby'] = 'meta_value_num';
                    $query_args['order'] = 'DESC';
                    break;

                case 'popular_view':
                    $query_args['meta_key'] = '__post_views_count';
                    $query_args['orderby'] = 'meta_value_num';
                    $query_args['order'] = 'DESC';
                    break;

                case 'date':
                    unset($query_args['orderby']);
                    $query_args['order'] = 'DESC';
                    break;     
                
                default:
                    break;
            }
        }

        if(isset($attr['queryOffset']) && $attr['queryOffset'] && !($query_args['paged'] > 1) ){
            $query_args['offset'] = isset($attr['queryOffset']) ? $attr['queryOffset'] : 0;
        }

        if(isset($attr['queryInclude']) && $attr['queryInclude']){
            $query_args['post__in'] = explode(',', $attr['queryInclude']);
        }

        if(isset($attr['queryExclude']) && $attr['queryExclude']){
            $query_args['post__not_in'] = explode(',', $attr['queryExclude']);
        }

        if(isset($attr['queryCat'])) {
            if( !empty($attr['queryCat']) ) {
                $var = array('relation'=>'OR');
                foreach (json_decode($attr['queryCat']) as $val) {
                    $var[] = array('taxonomy'=>'product_cat', 'field' => 'slug', 'terms' => $val );
                }
                $query_args['tax_query'] = $var;
            }
        }

        if ( isset($attr['queryStatus']) ) {
            switch ($attr['queryStatus']) {
                case 'featured':
                    $query_args['post__in'] = wc_get_featured_product_ids();
                    break;
    
                case 'onsale':
                    unset($query_args['meta_key']);
                    $query_args['orderby'] = 'date';
                    $query_args['order'] = 'DESC';
                    $query_args['meta_query'] = array(
                        'relation' => 'AND',
                        array(
                            'key'           => '_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        ),
                        array(
                            'key'           => '_regular_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        )
                    );
                    break;
        
                default:
                    break;
            }
        }
        $query_args['wpnonce'] = wp_create_nonce( 'wopb-nonce' );
        
        return $query_args;
    }


    public function get_page_number($attr, $post_number) {
        if($post_number > 0){
            $post_per_page = isset($attr['queryNumber']) ? $attr['queryNumber'] : 3;
            $pages = floor($post_number/$post_per_page);
            return $pages ? $pages : 1;
        }else{
            return 1;
        }
    }

    public function get_image_size() {
        $sizes = get_intermediate_image_sizes();
        $filter = array('full' => 'Full');
        foreach ($sizes as $value) {
            $filter[$value] = ucwords(str_replace(array('_', '-'), array(' ', ' '), $value));
        }
        return $filter;
    }


    // Pagination
    public function pagination($pages = '', $paginationNav, $range = 1) {
        $html = '';
        $showitems = 3;
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        if($pages == '') {
            global $wp_query;
            $pages = $wp_query->max_num_pages;
            if(!$pages) {
                $pages = 1;
            }
        }
        $data = ($paged>=3?[($paged-1),$paged,$paged+1]:[1,2,3]);

 
        if(1 != $pages) {
            $html .= '<ul class="wopb-pagination">';            
                $display_none = 'style="display:none"';
                if($pages > 4) {
                    $html .= '<li class="wopb-prev-page-numbers" '.($paged==1?$display_none:"").'><a href="'.get_pagenum_link($paged-1).'">'.wopb_function()->svg_icon('leftAngle2').' '.($paginationNav == 'textArrow'?__("Previous", "product-blocks"):"").'</a></li>';
                }
                if($pages > 4){
                    $html .= '<li class="wopb-first-pages" '.($paged<2?$display_none:"").' data-current="1"><a href="'.get_pagenum_link(1).'">1</a></li>';
                }
                if($pages > 4){
                    $html .= '<li class="wopb-first-dot" '.($paged<2? $display_none : "").'><a href="#">...</a></li>';
                }
                foreach ($data as $i) {
                    if($pages >= $i){
                        $html .= ($paged == $i) ? '<li class="wopb-center-item pagination-active" data-current="'.$i.'"><a href="'.get_pagenum_link($i).'">'.$i.'</a></li>':'<li class="wopb-center-item" data-current="'.$i.'"><a href="'.get_pagenum_link($i).'">'.$i.'</a></li>';
                    }
                }
                if($pages > 4){
                    $html .= '<li class="wopb-last-dot" '.($pages<=$paged+1?$display_none:"").'><a href="#">...</a></li>';
                }
                if($pages > 4){
                    $html .= '<li class="wopb-last-pages" '.($pages<=$paged+1?$display_none:"").' data-current="'.$pages.'"><a href="'.get_pagenum_link($pages).'">'.$pages.'</a></li>';
                }
                if ($paged != $pages) {
                    $html .= '<li class="wopb-next-page-numbers"><a href="'.get_pagenum_link($paged + 1).'">'.($paginationNav == 'textArrow' ? __("Next", "product-blocks") : "").wopb_function()->svg_icon('rightAngle2').'</a></li>';
                }
            $html .= '</ul>';
        }
        return $html;
    }

    public function excerpt_word($charlength = 200) {
        $html = '';
        $charlength++;
        $excerpt = get_the_excerpt();
        if ( mb_strlen( $excerpt ) > $charlength ) {
            $subex = mb_substr( $excerpt, 0, $charlength - 5 );
            $exwords = explode( ' ', $subex );
            $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
            if ( $excut < 0 ) {
                $html = mb_substr( $subex, 0, $excut );
            } else {
                $html = $subex;
            }
            $html .= '...';
        } else {
            $html = $excerpt;
        }
        return $html;
    }


    public function taxonomy( $prams = 'product_cat' ) {
        $data = array();
        $terms = get_terms( $prams, array(
            'hide_empty' => true,
        ));
        if( !empty($terms) ){
            foreach ($terms as $val) {
                $data[$val->slug] = $val->name;
            }
        }
        return $data;
    }

    public function filter($filterText = '', $filterType = '', $filterCat = '[]', $filterTag = '[]'){
        $html = '';
        $html .= '<ul class="wopb-flex-menu">';
            if($filterText){
                $html .= '<li class="filter-item"><a data-taxonomy="" href="#">'.$filterText.'</a></li>';
            }
            if ($filterType == 'product_cat') {
                $cat = $this->taxonomy('product_cat');
                foreach (json_decode($filterCat) as $val) {
                    $html .= '<li class="filter-item"><a data-taxonomy="'.($val=='all'?'':$val).'" href="#">'.(isset($cat[$val]) ? $cat[$val] : $val).'</a></li>';
                }
            } else {
                $tag = $this->taxonomy('product_tag');
                foreach (json_decode($filterTag) as $val) {
                    $html .= '<li class="filter-item"><a data-taxonomy="'.($val=='all'?'':$val).'" href="#">'.(isset($tag[$val]) ? $tag[$val] : $val).'</a></li>';
                }
            }
        $html .= '</ul>';
        return $html;
    }

    public function svg_icon($icons = 'view'){
        $icon_lists = array(
            'eye' 			=> file_get_contents(WOPB_PATH.'assets/img/svg/eye.svg'),
            'user' 			=> file_get_contents(WOPB_PATH.'assets/img/svg/user.svg'),
            'calendar'      => file_get_contents(WOPB_PATH.'assets/img/svg/calendar.svg'),
            'comment'       => file_get_contents(WOPB_PATH.'assets/img/svg/comment.svg'),
            'book'  		=> file_get_contents(WOPB_PATH.'assets/img/svg/book.svg'),
            'tag'           => file_get_contents(WOPB_PATH.'assets/img/svg/tag.svg'),
            'clock'         => file_get_contents(WOPB_PATH.'assets/img/svg/clock.svg'),
            'leftAngle'     => file_get_contents(WOPB_PATH.'assets/img/svg/leftAngle.svg'),
            'rightAngle'    => file_get_contents(WOPB_PATH.'assets/img/svg/rightAngle.svg'),
            'leftAngle2'    => file_get_contents(WOPB_PATH.'assets/img/svg/leftAngle2.svg'),
            'rightAngle2'   => file_get_contents(WOPB_PATH.'assets/img/svg/rightAngle2.svg'),
            'leftArrowLg'   => file_get_contents(WOPB_PATH.'assets/img/svg/leftArrowLg.svg'),
            'refresh'       => file_get_contents(WOPB_PATH.'assets/img/svg/refresh.svg'),
            'rightArrowLg'  => file_get_contents(WOPB_PATH.'assets/img/svg/rightArrowLg.svg'),
        ); 
        return $icon_lists[ $icons ];
    }
    
    public function isActive(){
        if (file_exists(WP_PLUGIN_DIR.'/product-blocks-pro/product-blocks-pro.php') && ! is_plugin_active('product-blocks-pro/product-blocks-pro.php')) {
			return true;
		} else {
            return false;
        }
    }
}
