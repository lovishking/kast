<?php

/**
 * Intent of roles
 */
add_action('init', function () {
    if (isset($_GET['role']) && is_user_logged_in()) {
        update_user_meta(get_current_user_id(), 'kast_join_role', sanitize_text_field($_GET['role']));
    }
});
