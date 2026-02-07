<?php

/**
 * Master Edit Code
 */
add_shortcode('kast_smart_buttons', function () {

    $logged_in_user  = get_current_user_id();
    $profile_user_id = 0;

    /* ==============================
       PROFILE USER DETECTION
    =============================== */

    // URL pattern: /user/username/
    if (preg_match('#/user/([^/]+)/?#', $_SERVER['REQUEST_URI'], $m)) {
        $slug = sanitize_title($m[1]);
        $user = get_user_by('slug', $slug);
        if ($user) {
            $profile_user_id = (int) $user->ID;
        }
    }

    // If profile not detected → do nothing
    if (!$profile_user_id) {
        return '';
    }

    ob_start();

    /* ==============================
       CASE 1: OWNER / ADMIN
    =============================== */
    if (
        is_user_logged_in() &&
        ($logged_in_user === $profile_user_id || current_user_can('administrator'))
    ) {
        ?>

        <!-- EDIT PROFILE -->
        <a href="<?php echo esc_url(home_url('/edit-profile/')); ?>"
           class="kast-edit-btn"
           style="
               display:block;
               width:100%;
               background:#D4AF37;
               color:#000;
               padding:12px;
               font-weight:800;
               text-align:center;
               text-transform:uppercase;
               text-decoration:none;
               border-radius:4px;
               margin-top:10px;
           ">
            ✏️ Edit Profile
        </a>

        <!-- MY INBOX -->
        <a href="<?php echo esc_url(home_url('/inbox/')); ?>"
           class="kast-inbox-btn"
           style="
               display:block;
               width:100%;
               background:#D4AF37;
               color:#000;
               padding:12px;
               font-weight:800;
               text-align:center;
               text-transform:uppercase;
               text-decoration:none;
               border-radius:4px;
               margin-top:10px;
           ">
            💬 My Inbox
        </a>

        <?php
    }

    /* ==============================
       CASE 2: NOT LOGGED IN
    =============================== */
    elseif (!is_user_logged_in()) {
        ?>
        <a href="<?php echo esc_url(home_url('/login/')); ?>"
           style="
               display:block;
               width:100%;
               background:#111;
               color:#D4AF37;
               padding:12px;
               font-weight:800;
               text-align:center;
               text-transform:uppercase;
               text-decoration:none;
               border-radius:4px;
               border:1px solid #333;
               margin-top:10px;
           ">
           🔐 Login to Message
        </a>
        <?php
    }

    /* ==============================
       CASE 3: OTHER USER → CHAT
    =============================== */
    else {
        echo '<div class="kast-chat-btn-wrapper" style="margin-top:15px;">';
        echo do_shortcode(
            '[kast_chat_button user="' . intval($profile_user_id) . '"]'
        );
        echo '</div>';
    }

    return ob_get_clean();
});
