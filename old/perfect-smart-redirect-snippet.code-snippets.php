<?php

/**
 * PERFECT SMART REDIRECT SNIPPET
 */
/* =====================================================
   KAST SMART REGISTRATION SUCCESS REDIRECT
   Purpose: Role-based redirect after registration
===================================================== */

add_action('template_redirect', function() {

    if (!is_user_logged_in()) return;

    // सिर्फ success page पर ही trigger हो
    if (!is_page('registration-success')) return;

    $user = wp_get_current_user();
    if (!$user || empty($user->roles)) return;

    $roles = array_map('strtolower', (array) $user->roles);

    if (in_array('agency', $roles)) {
        wp_safe_redirect(home_url('/my-agency/'));
        exit;
    }

    if (in_array('artist', $roles)) {
        wp_safe_redirect(home_url('/user/'));
        exit;
    }

});
