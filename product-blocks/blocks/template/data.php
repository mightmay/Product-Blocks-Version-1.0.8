<?php
defined('ABSPATH') || exit;

$post_id        = get_the_ID();
$title          = get_the_title( $post_id );
$titlelink      = get_permalink( $post_id );
$post_thumb_id  = get_post_thumbnail_id( $post_id );

$product        = wc_get_product($post_id);
$_sales         = $product->get_sale_price();
$_regular       = $product->get_regular_price();
$_discount      = ($_sales && $_regular) ? round( ( $_regular - $_sales ) / $_regular * 100 ).'%' : '';
$rating_count   = $product->get_rating_count();
$rating_average = $product->get_average_rating();