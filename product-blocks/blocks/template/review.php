<?php
defined('ABSPATH') || exit;

$review_data .= '<div class="wopb-star-rating">';
    $review_data .= '<span style="width: '.(($rating_average / 5 ) * 100).'%">';
        $review_data .= '<strong itemprop="ratingValue" class="wopb-rating">'.$rating_average.'</strong>';
        $review_data .= __('out of 5', 'product-blocks');
    $review_data .= '</span>';
$review_data .= '</div>';