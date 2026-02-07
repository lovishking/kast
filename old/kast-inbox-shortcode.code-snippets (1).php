<?php

/**
 * KAST INBOX SHORTCODE
 */
/* =====================================================
   KAST CHAT INBOX – FINAL 100% WORKING (ICONS + POSTCARD)
   Shortcode: [kast_chat_inbox]
===================================================== */

/** 
 * 1. PHOTO HELPER (Right Side Profile Card ke liye)
 */
function kast_get_user_photo_final($uid) {
    // Backup placeholder agar photo na mile
    $dummy = 'https://via.placeholder.com/600x800/111111/D4AF37?text=No+Photo';
    
    // Check various common meta keys used by plugins/Forminator
    $keys = ['profile_photo', 'upload-1', 'headshot', 'forminator_upload_1'];

    foreach ($keys as $key) {
        $raw = get_user_meta($uid, $key, true);
        if (!empty($raw)) {
            if (is_array($raw)) { $raw = isset($raw['file_url']) ? $raw['file_url'] : reset($raw); }
            if (is_numeric($raw)) {
                $img = wp_get_attachment_image_src($raw, 'large');
                return $img ? $img[0] : $dummy;
            }
            if (is_string($raw) && filter_var($raw, FILTER_VALIDATE_URL)) { return $raw; }
        }
    }
    // Final fallback to WP Avatar if no meta found
    return get_avatar_url($uid, ['size' => 500]) ?: $dummy;
}

/**
 * 2. AJAX PROFILE FETCHER
 */
add_action('wp_ajax_kast_load_full_profile', function(){
    $uid = intval($_POST['uid']);
    if(!$uid) wp_die();

    $u = get_userdata($uid);
    $photo = kast_get_user_photo_final($uid);
    $bio = get_user_meta($uid, 'description', true) ?: "No professional bio available for this profile.";
    
    // Role Logic
    $role = (function_exists('kast_user_role')) ? kast_user_role($uid) : 'artist';
    $role_label = ($role === 'agency') ? 'AGENCY' : 'ARTIST';

    echo '<div class="kast-profile-container">';
        
        // --- TOP: Photo (Left) | Info (Right) ---
        echo '<div class="kast-profile-top">';
            
            // Photo Box
            echo '<div class="kast-p-photo-col">';
                echo '<div class="kast-postcard-frame">';
                    echo '<img src="' . esc_url($photo) . '" alt="Profile">';
                echo '</div>';
            echo '</div>';

            // Info Box
            echo '<div class="kast-p-info-col">';
                echo '<div class="kast-p-header">';
                    echo '<h2>' . esc_html(strtoupper($u->display_name)) . '</h2>';
                    echo '<span class="kast-role-gold">' . $role_label . '</span>';
                echo '</div>';
                
                echo '<div class="kast-p-about">';
                    echo '<h4>ABOUT</h4>';
                    echo '<p>' . nl2br(esc_html($bio)) . '</p>';
                echo '</div>';
            echo '</div>';

        echo '</div>';

        // --- BOTTOM: Message Button ---
        echo '<div class="kast-profile-footer">';
            echo '<div class="kast-btn-wrapper">' . do_shortcode('[kast_chat_button user="' . $uid . '"]') . '</div>';
        echo '</div>';

    echo '</div>';
    wp_die();
});


/**
 * 3. MAIN INBOX SHORTCODE
 */
add_shortcode('kast_chat_inbox', function () {
    if (!is_user_logged_in()) return '<div style="color:#D4AF37;padding:50px;text-align:center;">Please login to view inbox.</div>';

    global $wpdb;
    $me = get_current_user_id();
    $rows = $wpdb->get_results($wpdb->prepare("
        SELECT IF(sender_id=%d, receiver_id, sender_id) AS uid, MAX(created_at) AS t
        FROM {$wpdb->prefix}kast_chat_messages
        WHERE sender_id=%d OR receiver_id=%d
        GROUP BY uid ORDER BY t DESC
    ", $me, $me, $me));

    ob_start(); ?>

    <div class="kast-inbox-shell">
        <!-- Sidebar (Left) -->
        <div class="kast-shell-left">
            <div class="k-shell-header">Inbox</div>
            <div class="k-shell-search"><input type="text" id="kSearch" placeholder="Search name..."></div>
            <div class="k-shell-list">
                <?php if(!$rows): echo '<div style="padding:20px;color:#444;">No chats found.</div>'; endif; ?>
                <?php foreach ($rows as $r):
                    $uid = intval($r->uid);
                    $u = get_userdata($uid); if(!$u) continue;
                    
                    // Role Detection for Icons
                    $role = (function_exists('kast_user_role')) ? kast_user_role($uid) : 'artist';
                    $role_label = ($role === 'agency') ? 'AGENCY' : 'ARTIST';
                    
                    // Agency = 🎬 | Artist = 🎭 (SVG Icons for better quality)
                    $icon_svg = ($role === 'agency') 
                        ? '<svg viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>'
                        : '<svg viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
                ?>
                <div class="k-user-row" data-uid="<?= $uid ?>" data-name="<?= strtolower($u->display_name) ?>">
                    <div class="k-mini-icon"><?= $icon_svg ?></div>
                    <div class="k-mini-meta">
                        <strong><?= $u->display_name ?></strong>
                        <span class="k-mini-role"><?= $role_label ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Main Content Area (Right) -->
        <div class="kast-shell-right">
            <div id="kWelcome" class="k-welcome">
                <div class="k-w-icon">💬</div>
                <h3>KAST Inbox</h3>
                <p>Select a contact to view portfolio and message.</p>
            </div>
            <div id="kProfileTarget"></div>
            <div id="kLoader" style="display:none;text-align:center;padding-top:100px;color:#D4AF37;font-style:italic;">Opening details...</div>
        </div>
    </div>

    <script>
    jQuery(function($){
        $('.k-user-row').on('click', function(){
            var uid = $(this).data('uid');
            $('.k-user-row').removeClass('active');
            $(this).addClass('active');

            $('#kWelcome, #kProfileTarget').hide();
            $('#kLoader').show();

            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'kast_load_full_profile',
                uid: uid
            }, function(res){
                $('#kLoader').hide();
                $('#kProfileTarget').html(res).fadeIn();
            });
        });

        $('#kSearch').on('input', function(){
            var v = $(this).val().toLowerCase();
            $('.k-user-row').each(function(){ $(this).toggle($(this).data('name').indexOf(v) > -1); });
        });
    });
    </script>

    <style>
    /* Inbox Shell */
    .kast-inbox-shell { display: flex; width: 100%; max-width: 1200px; margin: 40px auto; height: 680px; background: #000; border: 1px solid #222; border-radius: 12px; overflow: hidden; font-family: sans-serif; }
    
    /* LEFT SIDEBAR (Icons Only) */
    .kast-shell-left { width: 320px; border-right: 1px solid #1a1a1a; background: #080808; display: flex; flex-direction: column; }
    .k-shell-header { padding: 20px; color: #D4AF37; font-weight: 700; font-size: 20px; border-bottom: 1px solid #1a1a1a; }
    .k-shell-search { padding: 12px; border-bottom: 1px solid #1a1a1a; }
    #kSearch { width: 100%; background: #111; border: 1px solid #333; padding: 10px; color: #fff; border-radius: 6px; outline: none; font-size: 13px; }
    
    .k-shell-list { flex: 1; overflow-y: auto; }
    .k-user-row { display: flex; gap: 12px; padding: 15px; cursor: pointer; border-bottom: 1px solid #111; align-items: center; transition: 0.2s; }
    .k-user-row.active { background: #151515; border-left: 4px solid #D4AF37; }
    
    /* Sidebar Icons */
    .k-mini-icon { width: 42px; height: 42px; border-radius: 50%; background: #111; display: flex; align-items: center; justify-content: center; border: 1px solid #222; flex-shrink: 0; }
    .k-mini-icon svg { width: 22px; height: 22px; }

    .k-mini-meta strong { color: #fff; font-size: 14px; display: block; }
    .k-mini-role { color: #D4AF37; font-size: 9px; font-weight: 800; }

    /* RIGHT CONTENT */
    .kast-shell-right { flex: 1; background: #050505; display: flex; flex-direction: column; overflow-y: auto; padding: 40px; }
    .k-welcome { margin: auto; text-align: center; color: #333; }
    .k-w-icon { font-size: 50px; opacity: 0.2; }

    /* Profile Card Logic */
    .kast-profile-container { width: 100%; }
    .kast-profile-top { display: flex; gap: 35px; align-items: flex-start; margin-bottom: 40px; }

    /* Postcard Frame (For Right Side Photo) */
    .kast-p-photo-col { width: 300px; flex-shrink: 0; }
    .kast-postcard-frame { padding: 8px; background: #111; border: 2px solid #D4AF37; box-shadow: 0 10px 40px rgba(0,0,0,0.8); border-radius: 4px; }
    .kast-postcard-frame img { width: 100%; height: 450px; object-fit: cover; display: block; border: 1px solid #222; border-radius: 2px; }

    /* Name & About */
    .kast-p-info-col { flex: 1; text-align: left; }
    .kast-p-header { margin-bottom: 25px; border-bottom: 1px solid #222; padding-bottom: 15px; }
    .kast-p-header h2 { color: #fff; font-size: 38px; margin: 0 0 5px 0; font-family: serif; letter-spacing: 1px; }
    .kast-role-gold { color: #D4AF37; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; }

    .kast-p-about h4 { color: #D4AF37; font-size: 12px; margin-bottom: 10px; letter-spacing: 2px; }
    .kast-p-about p { color: #aaa; line-height: 1.6; font-size: 15px; }

    /* Bottom Button */
    .kast-profile-footer { border-top: 1px solid #1a1a1a; padding-top: 30px; }
    .kast-btn-wrapper a, .kast-btn-wrapper button {
        display: block !important; width: 100% !important; background: #D4AF37 !important; color: #000 !important;
        padding: 16px !important; font-weight: 900 !important; text-transform: uppercase !important; border-radius: 8px !important;
        border: none !important; font-size: 16px !important; transition: 0.3s;
    }
    .kast-btn-wrapper a:hover { transform: translateY(-3px); background: #fff !important; }

    /* Custom Scrollbar */
    .k-shell-list::-webkit-scrollbar { width: 4px; }
    .k-shell-list::-webkit-scrollbar-thumb { background: #222; }
    </style>

    <?php
    return ob_get_clean();
});
