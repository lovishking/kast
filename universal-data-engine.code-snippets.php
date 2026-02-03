<?php

/**
 * UNIVERSAL DATA ENGINE
 */
// ================================
// KAST - UNIVERSAL DATA ENGINE
// ================================

add_shortcode('artist_data', function($atts) {

    $atts = shortcode_atts(['key' => ''], $atts);
    $key = sanitize_text_field($atts['key']);

    $target_id = kast_get_target_user_id();
    if (!$target_id) return '-';

    $viewer_id = get_current_user_id();

    // Privacy protected keys
    $private_keys = ['mobile_number','whatsapp','whatsapp_number','user_email'];

    if (in_array($key, $private_keys)) {
        if ($viewer_id != $target_id && !current_user_can('administrator')) {
            return '<span style="color:#D4AF37; font-size:12px;">🔒 Request on Chat</span>';
        }
    }

    $user = get_userdata($target_id);

    // Core user fields
    if ($key === 'user_email') return $user ? esc_html($user->user_email) : '-';
    if ($key === 'user_login') return $user ? esc_html($user->user_login) : '-';

    // Meta fields
    $val = get_user_meta($target_id, $key, true);

    if (is_array($val)) return esc_html(implode(', ', $val));

    return !empty($val) ? esc_html($val) : '-';
});
