<?php

/**
 * KAST - Final Logic & Chat Rules
 */
// 1. DATA FETCHER
add_shortcode('artist_data', function($atts) {
    $atts = shortcode_atts(['key' => ''], $atts);
    $key = $atts['key'];
    $id = (function_exists('um_profile_id') && um_profile_id()) ? um_profile_id() : get_current_user_id();
    
    if ($key == 'user_email') { $u = get_userdata($id); return $u ? $u->user_email : ''; }
    if ($key == 'user_login') { $u = get_userdata($id); return $u ? $u->user_login : ''; }

    $val = get_user_meta($id, $key, true);
    if (is_array($val)) return implode(', ', $val);
    return !empty($val) ? esc_html($val) : '-';
});

// 2. PHOTO FETCHER
add_shortcode('artist_photo', function() {
    $id = (function_exists('um_profile_id') && um_profile_id()) ? um_profile_id() : get_current_user_id();
    $photo = get_user_meta($id, 'profile_photo', true);
    $url = '';
    
    if (is_numeric($photo)) {
        $img = wp_get_attachment_image_src($photo, 'large');
        $url = $img ? $img[0] : '';
    } elseif (!empty($photo)) {
        $url = $photo;
    }
    if (empty($url)) $url = 'https://via.placeholder.com/400x400/000000/D4AF37?text=No+Photo';
    return '<img src="' . esc_url($url) . '" alt="Profile" style="width:100%; border:3px solid #D4AF37; border-radius:4px;">';
});

// 3. SMART BUTTONS (CRITICAL ERROR FIXED)
add_shortcode('kast_smart_buttons', function() {
    $target_id = (function_exists('um_profile_id') && um_profile_id()) ? um_profile_id() : get_current_user_id();
    $viewer_id = get_current_user_id();
    
    ob_start();

    // === SCENARIO 1: OWNER (Edit) ===
    if ($viewer_id > 0 && ($target_id == $viewer_id || current_user_can('administrator'))) {
        ?>
        <input type="checkbox" id="toggle-edit" style="display:none;">
        <label for="toggle-edit" class="kast-edit-btn">✏️ Edit Profile / Upload Portfolio</label>
        
        <div id="kast-edit-container">
            <h3 style="color:#D4AF37; margin-top:0;">Update Details</h3>
            <?php echo do_shortcode('[forminator_form id="2964"]'); ?>
        </div>
        <style>
            #kast-edit-container { display: none; background: #0a0a0a; border: 1px solid #D4AF37; padding: 20px; margin-bottom: 20px; }
            #toggle-edit:checked ~ #kast-edit-container { display: block; animation: slideDown 0.5s ease; }
            .kast-edit-btn { display:block; width:100%; background:#D4AF37; color:#000; padding:12px; font-weight:bold; text-align:center; cursor:pointer; margin-bottom:20px; text-transform:uppercase;}
            @keyframes slideDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
        </style>
        <?php
    } 
    
    // === SCENARIO 2: GUEST (Not Logged In) ===
    // FIX: Agar viewer ID 0 hai, to Code Crash mat karo, Login button dikhao
    elseif ($viewer_id == 0) {
        ?>
        <div style="border: 1px solid #D4AF37; padding: 20px; text-align: center; border-radius: 4px; margin-bottom: 20px; background: #000;">
            <h4 style="color: #D4AF37; margin: 0 0 15px 0; text-transform: uppercase; font-size: 14px;">Login to Connect</h4>
            <a href="<?php echo home_url('/login/'); ?>" style="background: #D4AF37; color: #000; padding: 10px 25px; text-decoration: none; font-weight: bold; text-transform: uppercase; display: inline-block;">
                Login Now
            </a>
        </div>
        <?php
    }

    // === SCENARIO 3: LOGGED IN USER (Chat Logic) ===
    else {
        $can_chat = false;
        $upgrade_link = home_url('/pricing/');
        $chat_link = home_url('/chat/?new_conversation_with=' . $target_id);
        
        $plans = function_exists('pms_get_member_active_subscription_ids') ? pms_get_member_active_subscription_ids($viewer_id) : [];
        
        $viewer_data = get_userdata($viewer_id);
        $viewer_roles = $viewer_data->roles;
        $is_viewer_artist = in_array('um_artist', $viewer_roles) || in_array('subscriber', $viewer_roles);
        $is_viewer_agency = in_array('um_agency', $viewer_roles);

        $target_data = get_userdata($target_id);
        $target_roles = $target_data ? $target_data->roles : []; // Safety check
        $is_target_artist = in_array('um_artist', $target_roles) || in_array('subscriber', $target_roles);
        $is_target_agency = in_array('um_agency', $target_roles);

        // Logic
        if ($is_viewer_artist) {
            if ($is_target_agency) { $can_chat = true; }
            elseif ($is_target_artist) { if (in_array(668, $plans)) { $can_chat = true; } }
        }
        elseif ($is_viewer_agency) {
            if (in_array(1949, $plans) || in_array(1952, $plans)) { $can_chat = true; }
        }

        $final_link = $can_chat ? $chat_link : $upgrade_link;
        $btn_text = $can_chat ? 'Chat Now' : 'Upgrade to Chat';
        $status_text = $can_chat ? 'Access Unlocked' : 'Access Restricted';
        ?>
        
        <div style="border: 1px solid #D4AF37; padding: 20px; text-align: center; border-radius: 4px; margin-bottom: 20px; background: #000;">
            <h4 style="color: #D4AF37; margin: 0 0 15px 0; text-transform: uppercase; font-size: 14px;">
                <?php echo $status_text; ?>
            </h4>
            <a href="<?php echo $final_link; ?>" style="background: #D4AF37; color: #000; padding: 10px 25px; text-decoration: none; font-weight: bold; text-transform: uppercase; display: inline-block;">
                <?php echo $btn_text; ?>
            </a>
        </div>
        <?php
    }

    return ob_get_clean();
});
