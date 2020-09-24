<?php
defined('ABSPATH') || exit;

$wraper_after .= '<div class="wopb-loadmore">';
    $wraper_after .= '<span class="wopb-loadmore-action" data-pages="'.$pageNum.'" data-pagenum="1" data-blockid="'.$attr['blockId'].'" data-blockname="product-blocks_'.$block_name.'" data-postid="'.$page_post_id.'">Load More <span class="wopb-spin">'.wopb_function()->svg_icon('refresh').'</span></span>';
$wraper_after .= '</div>';