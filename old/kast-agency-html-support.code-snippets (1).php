<?php

/**
 * KAST - Agency HTML Support
 */
/* 1. DATA DISPLAY (Jo aapke HTML mein [user_meta] hai) */
add_shortcode('user_meta', function($atts) {
    $atts = shortcode_atts(['key' => ''], $atts);
    $key = sanitize_text_field($atts['key']);
    
    // Pata karo profile kiski hai (URL se)
    $target_id = 0;
    $current_url = $_SERVER['REQUEST_URI'];
    if (preg_match('/\/user\/([^\/\?]+)/', $current_url, $matches)) {
        $user = get_user_by('slug', $matches[1]);
        if ($user) $target_id = $user->ID;
    }
    if ($target_id === 0) $target_id = get_current_user_id(); // Fallback to Owner

    $val = get_user_meta($target_id, $key, true);
    return !empty($val) ? esc_html($val) : '-';
});

/* 2. USERNAME & EMAIL DISPLAY */
add_shortcode('user_name', function() {
    $target_id = 0;
    $current_url = $_SERVER['REQUEST_URI'];
    if (preg_match('/\/user\/([^\/\?]+)/', $current_url, $matches)) {
        $user = get_user_by('slug', $matches[1]);
        if ($user) $target_id = $user->ID;
    }
    if ($target_id === 0) $target_id = get_current_user_id();

    $u = get_userdata($target_id);
    return $u ? $u->user_login : '';
});

add_shortcode('user_email', function() {
    $target_id = 0;
    $current_url = $_SERVER['REQUEST_URI'];
    if (preg_match('/\/user\/([^\/\?]+)/', $current_url, $matches)) {
        $user = get_user_by('slug', $matches[1]);
        if ($user) $target_id = $user->ID;
    }
    if ($target_id === 0) $target_id = get_current_user_id();

    $u = get_userdata($target_id);
    return $u ? $u->user_email : '';
});

/* 3. PROFILE PICTURE (Agency Logo) */
add_shortcode('user_profile_picture', function() {
    $target_id = 0;
    $current_url = $_SERVER['REQUEST_URI'];
    if (preg_match('/\/user\/([^\/\?]+)/', $current_url, $matches)) {
        $user = get_user_by('slug', $matches[1]);
        if ($user) $target_id = $user->ID;
    }
    if ($target_id === 0) $target_id = get_current_user_id();

    $img_url = get_user_meta($target_id, 'profile_photo', true);
    if(empty($img_url)) $img_url = get_avatar_url($target_id, ['size' => 500]);

    return '<img src="'.esc_url($img_url).'" alt="Agency Logo" style="width:100%; height:100%; object-fit:cover; border-radius:50%; border: 3px solid #D4AF37;">';
});

/* 4. AGENCY CHAT BUTTON (Fix for your HTML) */
add_shortcode('kast_agency_chat_btn', function() {
    $target_id = 0;
    $current_url = $_SERVER['REQUEST_URI'];
    if (preg_match('/\/user\/([^\/\?]+)/', $current_url, $matches)) {
        $user = get_user_by('slug', $matches[1]);
        if ($user) $target_id = $user->ID;
    }
    if ($target_id === 0) $target_id = get_current_user_id();
    
    // Agar khud ki profile hai to Chat button mat dikhao
    if ($target_id == get_current_user_id()) return '';

    return do_shortcode('[better_messages_pm_button user_id="' . $target_id . '" text="Message" class="kast-btn kast-btn-chat"]');
});
