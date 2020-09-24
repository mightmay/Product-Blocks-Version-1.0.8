<?php
defined('ABSPATH') || exit;

if ($attr['headingShow']) {
    $wraper_before .= '<div class="wopb-heading-wrap wopb-heading-'.$attr["headingStyle"].' wopb-heading-'.$attr["headingAlign"].'">';
        if ($attr['headingURL']) {
            $wraper_before .= '<'.$attr['headingTag'].' class="wopb-heading-inner"><a href="'.$attr["headingURL"].'"><span>'.$attr["headingText"].'</span></a></'.$attr['headingTag'].'>';
        } else {
            $wraper_before .= '<'.$attr['headingTag'].' class="wopb-heading-inner"><span>'.$attr["headingText"].'</span></'.$attr['headingTag'].'>';
        }
        if ($attr['headingStyle'] == 'style11' && $attr['headingURL'] && $attr['headingBtnText']) {
            $wraper_before .= '<a class="wopb-heading-btn" href="'.$attr['headingURL'].'">'.$attr["headingBtnText"].wopb_function()->svg_icon('rightArrowLg').'</a>';
        }
        if ($attr['subHeadingShow']) {
            $wraper_before .= '<div class="wopb-sub-heading"><div class="wopb-sub-heading-inner">'.$attr['subHeadingText'].'</div></div>';
        }
    $wraper_before .= '</div>';
}