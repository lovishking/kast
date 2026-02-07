<?php

/**
 * kast_agency_smart_buttons
 */
add_shortcode('kast_agency_smart_buttons', function () {

    $logged_in_user   = get_current_user_id();
    $profile_user_id = 0;

    /* ==============================
       PROFILE USER DETECTION
    =============================== */
    if (preg_match('#/user/([^/]+)/?#', $_SERVER['REQUEST_URI'], $m)) {
        $slug = sanitize_title($m[1]);
        $user = get_user_by('slug', $slug);
        if ($user) {
            $profile_user_id = (int) $user->ID;
        }
    }

    if (!$profile_user_id) {
        return '';
    }

    /* ==============================
       AGENCY PLAN DETECTION
    =============================== */
    $plan = get_user_meta($profile_user_id, 'subscription_plan', true);
    $is_premium = (
        stripos($plan, 'Gold') !== false ||
        stripos($plan, 'Premium') !== false
    );

    ob_start();

    /* ==============================
       CASE 1: OWNER / ADMIN
    =============================== */
    if (
        is_user_logged_in() &&
        ($logged_in_user === $profile_user_id || current_user_can('administrator'))
    ) {
        ?>

        <!-- MY INBOX -->
        <a href="<?php echo esc_url(home_url('/inbox/')); ?>"
           style="display:block;width:100%;background:#D4AF37;color:#000;padding:15px;font-weight:800;text-align:center;text-transform:uppercase;text-decoration:none;border-radius:8px;margin-bottom:10px;">
            💬 My Inbox
        </a>

        <?php if (!$is_premium) : ?>

            <!-- UPGRADE TO PREMIUM -->
            <a href="<?php echo esc_url(home_url('/pricing/')); ?>"
               style="display:block;width:100%;background:#FFD700;color:#000;padding:15px;font-weight:900;text-align:center;text-transform:uppercase;text-decoration:none;border-radius:8px;border:2px solid #000;box-shadow:0 4px 15px rgba(255,215,0,0.4);">
                🚀 Upgrade to Premium (₹449)
            </a>

        <?php else : ?>

            <!-- PREMIUM BADGE -->
            <div style="display:block;width:100%;background:rgba(212,175,55,0.1);color:#D4AF37;padding:12px;font-weight:800;text-align:center;text-transform:uppercase;border:1px solid #D4AF37;border-radius:8px;">
                ⭐ Premium Gold Member
            </div>

        <?php endif; ?>

        <?php
    }

    /* ==============================
       CASE 2: NOT LOGGED IN
    =============================== */
    elseif (!is_user_logged_in()) {
        ?>

        <a href="<?php echo esc_url(home_url('/login/')); ?>"
           style="display:block;width:100%;background:#111;color:#D4AF37;padding:15px;font-weight:800;text-align:center;text-transform:uppercase;text-decoration:none;border-radius:8px;border:1px solid #D4AF37;">
            🔐 Login to Message
        </a>

        <?php
    }

    /* ==============================
       CASE 3: OTHER USER → CHAT
    =============================== */
    else {

        echo '<div class="kast-chat-btn-wrapper">';
        echo do_shortcode('[kast_chat_button user="' . intval($profile_user_id) . '"]');
        echo '</div>';

    }

    return ob_get_clean();
});
