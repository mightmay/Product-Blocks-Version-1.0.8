<?php
defined('ABSPATH') || exit;

$wraper_before .= '<div class="wopb-filter-wrap" data-taxtype='.$attr['filterType'].' data-blockid="'.$attr['blockId'].'" data-blockname="product-blocks_'.$block_name.'" data-postid="'.$page_post_id.'">';
    $wraper_before .= wopb_function()->filter($attr['filterText'], $attr['filterType'], $attr['filterCat'], $attr['filterTag']);
$wraper_before .= '</div>';