<?php

/**
 * KAST - Final Logic & Chat Rules
 */
// 1. DATA FETCHER (Corrected to detect Profile ID)
add_shortcode('artist_data', function($atts) { 
    $atts = shortcode_atts(['key' => ''], $atts); 
    $id = (function_exists('um_profile_id') && um_profile_id()) ? um_profile_id() : get_current_user_id(); 
    if ($atts['key'] == 'user_email') { $u = get_userdata($id); return $u ? $u->user_email : ''; } 
    $val = get_user_meta($id, $atts['key'], true); 
    if (is_array($val)) return implode(', ', $val); 
    return !empty($val) ? esc_html($val) : '-'; 
});

// 2. PHOTO FETCHER
add_shortcode('artist_photo', function() { 
    $id = (function_exists('um_profile_id') && um_profile_id()) ? um_profile_id() : get_current_user_id(); 
    $photo = get_user_meta($id, 'profile_photo', true); 
    $url = is_numeric($photo) ? wp_get_attachment_url($photo) : $photo; 
    if (empty($url)) $url = 'https://via.placeholder.com/400x400/000000/D4AF37?text=No+Photo'; 
    return '<img src="' . esc_url($url) . '" alt="Profile" style="width:100%; height:100%; object-fit:cover;">'; 
});

// 3. SMART BUTTONS (Redesigned for Agency with 4 Buttons)
add_shortcode('kast_smart_buttons', function() { 
    $target_id = (function_exists('um_profile_id') && um_profile_id()) ? um_profile_id() : get_current_user_id(); 
    $viewer_id = get_current_user_id(); 
    $user_data = get_userdata($target_id);
    $is_agency = ($user_data && in_array('um_agency', $user_data->roles));
    
    ob_start(); 
    ?>
    <div class="kast-button-grid-system">
        <?php if ($viewer_id > 0 && ($target_id == $viewer_id || current_user_can('administrator'))) : 
            // OWNER VIEW: Edit (2993 for Agency, 3656 for Artist), Post, Dashboard
            $edit_form_id = $is_agency ? '2993' : '3656';
        ?>
            <button id="kast-toggle-edit-btn" class="k-btn k-btn-gold"><i class="fa fa-edit"></i> EDIT PROFILE</button>
            <a href="/post-an-audition/" class="k-btn"><i class="fa fa-plus"></i> POST AUDITION</a>
            <a href="/inbox/" class="k-btn"><i class="fa fa-envelope"></i> MY MESSAGES</a>
            <a href="/dashboard/" class="k-btn"><i class="fa fa-th"></i> DASHBOARD</a>

            <div id="kast-edit-container" style="display:none; width:100%; margin-top:20px; background:#0a0a0a; border:1px solid #D4AF37; padding:20px;">
                <?php echo do_shortcode('[forminator_form id="'.$edit_form_id.'"]'); ?>
            </div>
            
            <script>
            document.getElementById('kast-toggle-edit-btn').addEventListener('click', function(){
                var container = document.getElementById('kast-edit-container');
                container.style.display = (container.style.display === 'none') ? 'block' : 'none';
            });
            </script>

        <?php elseif ($viewer_id == 0) : ?>
            <a href="/login/" class="k-btn k-btn-gold" style="width:100%">LOGIN TO CONNECT</a>
        <?php else : ?>
            <!-- GUEST VIEW: Just Message Button -->
            <div class="message-btn-wrapper" style="width:100%">
                [kast_chat_button user="<?= $target_id ?>"]
            </div>
        <?php endif; ?>
    </div>

    <style>
    .kast-button-grid-system { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; width: 100%; margin-top: 15px; }
    .k-btn { background: #111; color: #fff; border: 1px solid #333; padding: 12px 5px; text-align: center; border-radius: 4px; font-size: 12px; font-weight: bold; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; cursor: pointer; text-transform: uppercase;}
    .k-btn:hover { background: #D4AF37; color: #000; border-color: #D4AF37; }
    .k-btn-gold { background: #D4AF37; color: #000; border-color: #D4AF37; }
    @media (max-width: 600px) { .kast-button-grid-system { grid-template-columns: 1fr; } }
    </style>
    <?php
    return ob_get_clean(); 
});
