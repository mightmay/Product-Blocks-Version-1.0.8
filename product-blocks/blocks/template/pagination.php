<?php
defined('ABSPATH') || exit;

$wraper_after .= '<div class="wopb-pagination-wrap'.($attr["paginationAjax"] ? " wopb-pagination-ajax-action" : "").'" data-paged="1" data-blockid="'.$attr['blockId'].'" data-postid="'.$page_post_id.'" data-pages="'.$pageNum.'" data-blockname="product-blocks_'.$block_name.'">';
    $wraper_after .= wopb_function()->pagination($pageNum, $attr['paginationNav']);
$wraper_after .= '</div>';