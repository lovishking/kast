<?php

/**
 * Profile Data Engine
 */
/**
 * 1. HELPER FUNCTION: Sahi User ID nikalne ke liye
 * Isse "Dusron ki profile na dikhne" wali problem solve hogi.
 */
function kast_get_target_user_id() {
    $current_url = $_SERVER['REQUEST_URI'];
    $target_id = 0;

    // URL pattern check: /user/username/
    // Is regex ko behtar kiya gaya hai taki ye trailing slash bhi handle kare
    if (preg_match('/\/user\/([^\/\?]+)/', $current_url, $matches)) {
        $slug = sanitize_text_field(trim($matches[1], '/'));
        
        // 1. Pehle Slug se dhoondo
        $user = get_user_by('slug', $slug);
        
        // 2. Agar nahi mila, to Login ID se dhoondo (Ultimate Member compatibility)
        if (!$user) {
            $user = get_user_by('login', $slug);
        }

        if ($user) {
            $target_id = $user->ID;
        }
    }

    // FALLBACK: Agar URL mein koi user nahi hai (yani My Profile page hai), tabhi login user ki ID do
    if (!$target_id) {
        $target_id = get_current_user_id();
    }

    return $target_id;
}

/**
 * 2. SMART PHOTO SHORTCODE: [artist_photo]
 */
add_shortcode('artist_photo', function() {
    $target_id = kast_get_target_user_id();
    if (!$target_id) return '';

    // Meta keys check karein
    $photo = get_user_meta($target_id, 'profile_photo', true); 
    if (empty($photo)) { 
        $photo = get_user_meta($target_id, 'headshot', true); 
    }

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

/**
 * 3. DATA FETCHER SHORTCODE: [artist_data key="meta_key"]
 */
add_shortcode('artist_data', function($atts) {
    $atts = shortcode_atts(['key' => ''], $atts);
    $key = $atts['key'];
    $id = kast_get_target_user_id();
    
    if (!$id) return '-';

    // Privacy Logic: Mobile aur Email ko lock karne ke liye
    $viewer_id = get_current_user_id();
    $private_keys = ['mobile_number', 'user_email', 'whatsapp', 'whatsapp_number', 'phone'];
    
    if (in_array($key, $private_keys)) {
        // Agar main Admin nahi hoon aur ye meri apni profile nahi hai, to lock dikhao
        if ($id != $viewer_id && !current_user_can('administrator')) {
            return '<span style="color:#D4AF37; font-size:12px; border:1px solid #333; padding:2px 5px; border-radius:3px;">🔒 Request on Chat</span>';
        }
    }

    // Special cases for WP native data
    $user_info = get_userdata($id);
    if ($key == 'user_email') return $user_info ? $user_info->user_email : '';
    if ($key == 'user_login') return $user_info ? $user_info->user_login : '';
    if ($key == 'display_name') return $user_info ? $user_info->display_name : '';

    $val = get_user_meta($id, $key, true);
    if (is_array($val)) return implode(', ', $val);
    
    return !empty($val) ? esc_html($val) : '-';
});

/**
 * 4. PORTFOLIO GALLERY SHORTCODE: [artist_portfolio]
 */
add_shortcode('artist_portfolio', function() {
    $id = kast_get_target_user_id();
    if (!$id) return '';

    $gallery = get_user_meta($id, 'portfolio_gallery', true);
    if (empty($gallery)) {
        $gallery = get_user_meta($id, 'my_gallery', true);
    }
    
    if (empty($gallery)) {
        return '<p style="color:#666; font-size:13px; font-style:italic;">No portfolio images uploaded.</p>';
    }

    // Agar string hai to array mein badlo
    if (is_string($gallery)) {
        $gallery = explode(',', $gallery);
    }

    ob_start();
    ?>
    <style>
        .kast-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; padding: 10px 0; }
        .kast-gal-item { cursor: pointer; border: 1px solid #333; overflow: hidden; border-radius: 4px; aspect-ratio: 1/1; }
        .kast-gal-img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .kast-gal-img:hover { transform: scale(1.1); }
    </style>
    <div class="kast-gallery-grid">
        <?php 
        foreach ($gallery as $img_url) {
            $img_url = trim($img_url);
            if (!empty($img_url)) {
                echo '<div class="kast-gal-item">';
                echo '<img src="' . esc_url($img_url) . '" class="kast-gal-img" oncontextmenu="return false;">';
                echo '</div>';
            }
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
});

/**
 * 5. SMART BUTTONS: [kast_smart_buttons]
 * Isse Edit/Chat/Login buttons sahi dikhenge
 */
add_shortcode('kast_smart_buttons', function() {
    $target_id = kast_get_target_user_id();
    $viewer_id = get_current_user_id();

    ob_start();
    
    // CASE 1: Meri apni profile (Owner)
    if ($viewer_id > 0 && ($target_id == $viewer_id || current_user_can('administrator'))) {
        echo '<a href="'.home_url('/edit-profile/').'" class="kast-edit-btn" style="display:block; width:100%; background:#D4AF37; color:#000; padding:12px; font-weight:bold; text-align:center; text-decoration:none; border-radius:4px; margin-bottom:10px;">✏️ EDIT PROFILE</a>';
    } 
    // CASE 2: Guest (Not logged in)
    elseif ($viewer_id == 0) {
        echo '<div style="border: 1px solid #D4AF37; padding: 15px; text-align: center; border-radius: 4px; background: #000;">';
        echo '<h4 style="color: #D4AF37; margin: 0 0 10px 0; font-size:14px;">LOGIN TO CONNECT</h4>';
        echo '<a href="'.home_url('/login/').'" style="background: #D4AF37; color: #000; padding: 8px 20px; text-decoration: none; font-weight: bold; display: inline-block; border-radius:4px;">LOGIN NOW</a>';
        echo '</div>';
    } 
    // CASE 3: Dusra user (Chat Button)
    else {
        // Yahan Better Messages ya UM Chat ka shortcode daal sakte hain
        echo '<div style="margin-top:10px;">';
        echo do_shortcode('[better_messages_pm_button user_id="'.$target_id.'" text="Message Artist" class="kast-btn"]');
        echo '</div>';
    }

    return ob_get_clean();
});
