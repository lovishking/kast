<?php

/**
 * user_meta key="agency_name"
 */
// KAST Shortcode for User Meta
function kast_user_meta_func($atts) {
    $atts = shortcode_atts(array('key' => ''), $atts);
    
    // Get current user ID
    $user_id = get_current_user_id();
    if (!$user_id) return ''; // If not logged in, show nothing

    // Get value
    $val = get_user_meta($user_id, $atts['key'], true);
    
    // If value is empty, show a placeholder (Optional)
    if(empty($val)) return '-';

    return $val;
}
add_shortcode('user_meta', 'kast_user_meta_func');

// Profile Picture Shortcode
function kast_profile_pic_func() {
    $user_id = get_current_user_id();
    return get_avatar($user_id, 160);
}
add_shortcode('user_profile_picture', 'kast_profile_pic_func');
