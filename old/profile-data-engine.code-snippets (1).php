<?php

/**
 * Profile Data Engine
 */
// HELPER: Get Target User ID from URL or Login
function kast_get_target_user_id() {
    $id = 0;
    // 1. Try URL Slug (/user/username/)
    $current_url = $_SERVER['REQUEST_URI'];
    if (preg_match('/\/user\/([^\/\?]+)/', $current_url, $matches)) {
        $user = get_user_by('slug', $matches[1]);
        if ($user) $id = $user->ID;
    }
    // 2. Fallback to Current User
    if (!$id) $id = get_current_user_id();
    return $id;
}

// 1. SMART PHOTO FIX
add_shortcode('artist_photo', function() {
    $target_id = kast_get_target_user_id();
    if (!$target_id) return ''; // No user found

    $photo = get_user_meta($target_id, 'profile_photo', true); 
    if (empty($photo)) { $photo = get_user_meta($target_id, 'headshot', true); }

    $img_url = '';
    if (is_numeric($photo)) {
        $img_data = wp_get_attachment_image_src($photo, 'full');
        $img_url = $img_data ? $img_data[0] : '';
    } else {
        $img_url = $photo;
    }
    if (empty($img_url)) {
        $img_url = 'https://via.placeholder.com/600x800/000000/D4AF37?text=No+Image';
    }
    return '<img src="' . esc_url($img_url) . '" oncontextmenu="return false;" style="width:100%; height:auto; border:3px solid #D4AF37; border-radius:4px; display:block; object-fit:cover; pointer-events: none;">';
});

// 2. DATA FETCHER
add_shortcode('artist_data', function($atts) {
    $atts = shortcode_atts(['key' => ''], $atts);
    $key = $atts['key'];
    $id = kast_get_target_user_id();
    
    if (!$id) return '-';

    // Privacy Logic
    $viewer = get_current_user_id();
    $private_keys = ['mobile_number', 'user_email', 'whatsapp', 'whatsapp_number'];
    if(in_array($key, $private_keys)) {
        if($id != $viewer && !current_user_can('administrator')) {
            return '<span style="color:#D4AF37; font-size:12px; border:1px solid #333; padding:2px 5px;">🔒 Request on Chat</span>';
        }
    }

    $user_info = get_userdata($id);
    if ($key == 'user_email') return $user_info ? $user_info->user_email : '';
    if ($key == 'user_login') return $user_info ? $user_info->user_login : '';
    
    $val = get_user_meta($id, $key, true);
    if (is_array($val)) return implode(', ', $val);
    return !empty($val) ? esc_html($val) : '-';
});

// 3. PORTFOLIO
add_shortcode('artist_portfolio', function() {
    $id = kast_get_target_user_id();
    if (!$id) return '';

    $gallery = get_user_meta($id, 'portfolio_gallery', true);
    if(empty($gallery)) $gallery = get_user_meta($id, 'my_gallery', true);
    
    if (empty($gallery)) return '<p style="color:#666; font-size:13px; font-style:italic;">No portfolio images uploaded.</p>';

    if (is_string($gallery)) $gallery = explode(',', $gallery);

    ob_start();
    ?>
    <div class="kast-gallery-grid">
        <?php 
        if (is_array($gallery)) {
            foreach ($gallery as $img_url) {
                if(!empty($img_url)) {
                    echo '<div class="kast-gal-item" data-src="'.esc_url($img_url).'">';
                    echo '<img src="' . esc_url($img_url) . '" class="kast-gal-img" oncontextmenu="return false;">';
                    echo '</div>';
                }
            }
        }
        ?>
    </div>
    <!-- Popup HTML same as before (CSS handled in previous snippet) -->
    <div id="kastPhotoModal" class="kast-modal"><span class="kast-close">&times;</span><img class="kast-modal-content" id="kastModalImg" oncontextmenu="return false;"></div>
    <?php
    return ob_get_clean();
});
