<?php

/**
 * KAST - Universal Profile Photo
 */
add_shortcode('kast_profile_photo', function() {
    // 1. Pata karo kiska profile khula hai
    $profile_id = 0;
    if (function_exists('um_profile_id')) {
        $profile_id = um_profile_id(); // Agar UM profile page hai
    }
    
    // Agar profile ID nahi mili, to current logged in user lo
    if (!$profile_id) {
        $profile_id = get_current_user_id();
    }

    if (!$profile_id) return ''; // Agar koi user hi nahi hai

    // 2. Photo dhoondo (Forminator wali key)
    $photo_data = get_user_meta($profile_id, 'profile_photo', true);
    $image_url = '';

    // Logic: Forminator kabhi ID deta hai, kabhi URL, kabhi Array
    if (!empty($photo_data)) {
        if (is_numeric($photo_data)) {
            // Agar ID hai (Example: 245)
            $image_url = wp_get_attachment_url($photo_data);
        } elseif (is_array($photo_data)) {
            // Agar Array hai
            $image_url = !empty($photo_data['file_url']) ? $photo_data['file_url'] : $photo_data[0];
        } else {
            // Agar seedha URL hai
            $image_url = $photo_data;
        }
    }

    // 3. Agar Forminator photo nahi mili, to default Avatar try karo
    if (empty($image_url)) {
        $image_url = get_avatar_url($profile_id, ['size' => 400]);
    }
    
    // 4. Fallback (Agar kuch bhi na mile)
    if (empty($image_url)) {
        $image_url = 'https://via.placeholder.com/400x400/000000/D4AF37?text=No+Photo';
    }

    // 5. HTML Output (Design ke sath)
    return '<div style="width: 100%; aspect-ratio: 1/1; overflow: hidden; border: 2px solid #D4AF37; border-radius: 5px;">
                <img src="' . esc_url($image_url) . '" style="width: 100%; height: 100%; object-fit: cover;" alt="Profile Photo">
            </div>';
});
