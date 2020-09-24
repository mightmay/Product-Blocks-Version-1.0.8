<?php
namespace WOPB;

defined('ABSPATH') || exit;

class REST_API{
    public function __construct() {
        add_action( 'rest_api_init', array($this, 'wopb_register_route') );
    }

    public function wopb_register_route() {
        register_rest_route( 'wopb', 'posts', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('post_type', 'taxonomy', 'include', 'exclude', 'order', 'orderby', 'count', 'size', 'tag', 'cat', 'meta_key', 'status', 'wpnonce'),
                'callback' => array($this, 'wopb_route_post_data'),
            )
        );
        register_rest_route( 'wopb', 'category', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('queryCat', 'queryNumber', 'queryType', 'wpnonce'),
                'callback' => array($this, 'wopb_route_category_data'),
            )
        );
        register_rest_route( 'wopb', 'taxonomy', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('taxonomy', 'wpnonce'),
                'callback' => array($this, 'wopb_route_taxonomy_data'),
            )
        );
        register_rest_route( 'wopb', 'imagesize', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('taxonomy', 'wpnonce'),
                'callback' => array($this, 'wopb_route_imagesize_data'),
            )
        );
        register_rest_route( 'wopb', 'tax_info', array(
                'methods' => \WP_REST_Server::READABLE,
                'args' => array('taxonomy', 'wpnonce'),
                'callback' => array($this, 'wopb_route_taxonomy_info_data'),
            )
        );
    }

    public function wopb_route_category_data($prams){
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')){
            return rest_ensure_response([]);
        }

        $data = wopb_function()->get_category_data(json_decode($prams['queryCat']), $prams['queryNumber'], $prams['queryType']);
        return rest_ensure_response( $data );
    }

    public function wopb_route_imagesize_data() {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local){
            return ;
        }
        
        return wopb_function()->get_image_size();
    }

    public function wopb_route_post_data($prams,$local=false) {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce') && $local){
            return ;
        }

        // Query Builder
        $args = array(
            'post_type'     => 'product',
            'orderby'       => 'date',
            'post_status'   => 'publish',
            'order'         => 'DESC',
        );

        if(isset($prams['count'])){ $args['posts_per_page'] = esc_attr($prams['count']); }
        // if(isset($prams['post_type'])){ $args['post_type'] = esc_attr( $prams['post_type'] ); }

        if(isset($prams['taxonomy'])){ // taxonomy  slug__val__slug__val
            $tax_arr = array('relation' => 'OR');
            $tax = explode('__', esc_attr($prams['taxonomy']));
            if(!empty($tax)){
                for($i=0; $i < count($tax) ; $i++){
                    if($i%2 == 0){
                        $tax_arr[] = array('taxonomy'=>$tax[$i], 'field' => 'slug', 'terms' => array($tax[$i+1]));
                    }
                }
            }
            if(!empty($tax_arr)){
                $args['tax_query'] = $tax_arr;
            }
        }

        if(isset($prams['include'])){ $args['post__in'] = explode('__', esc_attr($prams['include'])); }
        if(isset($prams['exclude'])){ $args['post__not_in'] = explode('__', esc_attr($prams['exclude'])); }
        if(isset($prams['orderby'])){ $args['orderby'] = esc_attr($prams['orderby']); }
        if(isset($prams['order'])){ $args['order'] = $prams['order']; }
        if(isset($prams['offset'])){ $args['offset'] = esc_attr($prams['offset']); }
        if(isset($prams['paged'])){ $args['paged'] = esc_attr($prams['paged']); }
        if(isset($prams['meta_key'])){ $args['meta_key'] = esc_attr($prams['meta_key']); }


        if ( isset($prams['queryOrderBy']) ) {
            switch ($prams['queryOrderBy']) {
                case 'new_old':
                    $args['order'] = 'DESC';
                    unset($args['orderby']);
                    break;

                case 'title':
                    $args['orderby'] = 'title';
                    $args['order'] = 'ASC';
                    break;

                case 'title_reversed':
                    $args['orderby'] = 'title';
                    $args['order'] = 'DESC';
                    break;

                case 'price_low':
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                    break;

                case 'price_high':
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;            

                case 'popular':
                    $args['meta_key'] = 'total_sales';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;

                case 'popular_view':
                    $args['meta_key'] = '__post_views_count';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;

                case 'date':
                    unset($args['orderby']);
                    $query_args['order'] = 'DESC';
                    break;
                
                default:
                    break;
            }
        }

        if ( isset($prams['status']) ) {
            switch ($prams['status']) {
                case 'featured':
                    if( isset($args['post__in']) ) {
                        $args['post__in'] = array_merge( $args['post__in'], wc_get_featured_product_ids() );
                    } else {
                        $args['post__in'] = wc_get_featured_product_ids();
                    }
                    break;

                case 'onsale':
                    unset($args['meta_key']);
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    $args['meta_query'] = array(
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
        
        $data = [];
        $loop = new \WP_Query($args);

        if($loop->have_posts()){
            while($loop->have_posts()) {
                $loop->the_post(); 
                $var                = array();
                $post_id            = get_the_ID();
                $product            = wc_get_product($post_id);
                $user_id            = get_the_author_meta('ID');
                $var['title']       = get_the_title();
                $var['permalink']   = get_permalink();
                $var['excerpt']     = strip_tags(get_the_content());
                $var['excerpt_full']= strip_tags(get_the_excerpt());
                $var['time']        = get_the_date();
                $var['price_sale']  = $product->get_sale_price();
                $var['price_regular']= $product->get_regular_price();
                $var['discount']= ($var['price_sale'] && $var['price_regular']) ? round( ( $var['price_regular'] - $var['price_sale'] ) / $var['price_regular'] * 100 ).'%' : '';
                $var['price_html']  = $product->get_price_html();
                $var['stock']       = $product->get_stock_status();
                $var['rating_count']= $product->get_rating_count();
                $var['rating_average']= $product->get_average_rating();
                
                // image
                if( has_post_thumbnail() ){
                    $thumb_id = get_post_thumbnail_id($post_id);
                    $image_sizes = wopb_function()->get_image_size();
                    $image_src = array();
                    foreach ($image_sizes as $key => $value) {
                        $image_src[$key] = wp_get_attachment_image_src($thumb_id, $key, false)[0];
                    }
                    $var['image'] = $image_src;
                }

                // tag
                $tag = get_the_terms($post_id, (isset($prams['tag'])?esc_attr($prams['tag']):'product_tag'));
                if(!empty($tag)){
                    $v = array();
                    foreach ($tag as $val) {
                        $v[] = array('slug' => $val->slug, 'name' => $val->name, 'url' => get_term_link($val->term_id));
                    }
                    $var['tag'] = $v;
                }

                // cat
                $cat = get_the_terms($post_id, (isset($prams['cat'])?esc_attr($prams['cat']):'product_cat'));
                if(!empty($cat)){
                    $v = array();
                    foreach ($cat as $val) {
                        $v[] = array('slug' => $val->slug, 'name' => $val->name, 'url' => get_term_link($val->term_id));
                    }
                    $var['category'] = $v;
                }
                $data[] = $var;
            }
            wp_reset_postdata();
        }

    return rest_ensure_response( $data );
    }



    public function wopb_route_taxonomy_data($prams) {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')){
            return rest_ensure_response([]);
        }
        return rest_ensure_response(wopb_function()->taxonomy($prams['taxonomy']));
    }

    public function wopb_route_taxonomy_info_data($prams) {
        if (!wp_verify_nonce($_REQUEST['wpnonce'], 'wopb-nonce')){
            return rest_ensure_response([]);
        }

        $data = array();
        $terms = get_terms( $prams, array(
            'hide_empty' => true,
        ));
        if( !empty($terms) ){
            foreach ($terms as $val) {
                $data['name'] = $val->name;
                $data['count'] = $val->count;
                $data['url'] = get_term_link($val->term_id);
                $data['color'] = get_term_meta($val->term_id, '_background', true);
            }
        }

        return rest_ensure_response($data);
    }

}