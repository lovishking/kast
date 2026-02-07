<?php

/**
 * KAST - Debug User Data
 */
add_shortcode('kast_debug_data', function() {
    // Screenshot mein dikh rahe user ka username yahan likhein
    // Agar username nahi pata to 'anvesh' ya jo bhi uska login ID hai wo try karein
    // Ya fir hum seedha latest user utha lete hain:
    
    $users = get_users(['number' => 1]); // Gets the first user found
    if (empty($users)) return 'No users found.';
    
    $user = $users[0];
    $uid = $user->ID;
    
    $all_meta = get_user_meta($uid);

    echo '<div style="background: #fff; color: #000; padding: 20px; z-index: 9999; position: relative;">';
    echo '<h3>Debug Data for User: ' . $user->display_name . ' (ID: ' . $uid . ')</h3>';
    
    echo '<h4>Profile Photo Candidates:</h4>';
    // Check common keys
    echo 'upload-1: <pre>' . print_r(get_user_meta($uid, 'upload-1', true), true) . '</pre>';
    echo 'profile_photo: <pre>' . print_r(get_user_meta($uid, 'profile_photo', true), true) . '</pre>';
    echo 'forminator_upload_1: <pre>' . print_r(get_user_meta($uid, 'forminator_upload_1', true), true) . '</pre>';
    
    echo '<hr>';
    echo '<h4>All Saved Data (Raw):</h4>';
    echo '<pre style="height: 300px; overflow: scroll; background: #eee;">';
    print_r($all_meta);
    echo '</pre>';
    echo '</div>';
});
