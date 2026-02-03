<?php

/**
 * Features
 */
/* 
 * Snippet Name: KAST - Capture & Save Plan from Razorpay Redirect
 */

// 1. URL se Plan pakadna (Cookie set karna)
add_action('init', 'kast_capture_plan_from_url');

function kast_capture_plan_from_url() {
    // Check karein ki kya link mein 'plan' likha hai?
    if (isset($_GET['plan'])) {
        $plan_name = sanitize_text_field($_GET['plan']);

        // A. Agar user pehle se Logged-in hai (Upgrade case)
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'kast_current_plan', $plan_name);
        } 
        // B. Agar user Naya hai (Signup case) -> Browser Cookie mein save karein (1 ghante ke liye)
        else {
            setcookie('kast_temp_plan', $plan_name, time() + 3600, '/');
        }
    }
}

// 2. Registration complete hone par Plan save karna
add_action('user_register', 'kast_save_plan_on_new_registration');

function kast_save_plan_on_new_registration($user_id) {
    // Check karein ki kya koi Cookie saved hai?
    if (isset($_COOKIE['kast_temp_plan'])) {
        $plan_name = sanitize_text_field($_COOKIE['kast_temp_plan']);
        
        // Naye user ke meta data mein Plan save karein
        update_user_meta($user_id, 'kast_current_plan', $plan_name);
        
        // Kaam ho gaya, ab cookie delete kar dein
        setcookie('kast_temp_plan', '', time() - 3600, '/');
    }
}
