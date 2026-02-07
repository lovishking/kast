<?php

/**
 * KAST - Photo URL Only
 */
add_shortcode('kast_photo_url', function() {
    // Current User ya Profile Owner ki ID nikalo
    $id = (function_exists('um_profile_id') && um_profile_id()) ? um_profile_id() : get_current_user_id();
    
    // Forminator wali photo dhoondo
    $photo = get_user_meta($id, 'profile_photo', true);
    
    // URL banao
    if (is_numeric($photo)) {
        $img = wp_get_attachment_image_src($photo, 'full');
        return $img ? $img[0] : '';
    } elseif (!empty($photo)) {
        return $photo;
    }
    
    // Agar photo nahi hai to blank return karo (ya placeholder)
    return 'https://via.placeholder.com/400x400/000000/D4AF37?text=No+Photo';
});
