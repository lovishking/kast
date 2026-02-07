<?php

/**
 * KAST - Auto Experience
 */
add_shortcode('artist_auto_experience', function() {
    $user_id = get_current_user_id();
    
    // 1. Registration Date nikalo
    $user_info = get_userdata($user_id);
    $reg_date = new DateTime($user_info->user_registered);
    $now = new DateTime();
    
    // 2. Time since registration (Interval)
    $interval = $reg_date->diff($now);
    $months_since_reg = ($interval->y * 12) + $interval->m;

    // 3. Initial Experience jo user ne form mein bhara tha
    $init_years = (int) get_user_meta($user_id, 'exp_years', true);
    $init_months = (int) get_user_meta($user_id, 'exp_months', true);
    
    // 4. Total Calculation
    $total_months = ($init_years * 12) + $init_months + $months_since_reg;
    
    // 5. Convert back to Years & Months
    $final_years = floor($total_months / 12);
    $final_months = $total_months % 12;

    // Output
    if ($final_years > 0) {
        return $final_years . ' Yrs ' . $final_months . ' Months';
    } else {
        return $final_months . ' Months';
    }
});
