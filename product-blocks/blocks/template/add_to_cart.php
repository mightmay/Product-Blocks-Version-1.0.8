<?php
defined('ABSPATH') || exit;

$cart_data .= '<div class="wopb-product-btn">';
    $cart_data .= wopb_function()->get_add_to_cart($product, $attr['cartText']);
$cart_data .= '</div>';