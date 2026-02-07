<?php

/**
 * Data Injector
 */
add_action('wp_footer', function () {

    if ( ! is_user_logged_in() ) return;

    if ( ! is_page('edit-profile') ) return;

    $uid = get_current_user_id();

    $data = [
        'text-1'  => get_user_meta($uid, 'mobile_number', true),
        'text-2'  => get_user_meta($uid, 'current_city', true),
        'text-3'  => get_user_meta($uid, 'weight', true),
        'text-4'  => get_user_meta($uid, 'height', true),
        'text-5'  => get_user_meta($uid, 'chest', true),
        'text-6'  => get_user_meta($uid, 'bust', true),
        'text-7'  => get_user_meta($uid, 'waist', true),
        'text-8'  => get_user_meta($uid, 'hips', true),
    ];
    ?>
    <script>
        window.KAST_PROFILE_DATA = <?php echo json_encode($data); ?>;
    </script>
    <?php
});
