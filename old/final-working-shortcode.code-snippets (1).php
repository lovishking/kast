<?php

/**
 * FINAL WORKING SHORTCODE
 */
add_shortcode('kast_complete_registration', function () { 
    if (!is_user_logged_in()) { 
        return '<p style="color:red;text-align:center;">Please login to continue.</p>'; 
    } 

    $user_id = get_current_user_id(); 
    $subscriptions = function_exists('pms_get_member_subscriptions') ? pms_get_member_subscriptions($user_id) : []; 

    $has_agency = false; 
    $has_artist = false; 

    if (!empty($subscriptions)) { 
        foreach ($subscriptions as $sub) { 
            if (!empty($sub->subscription_plan_id)) { 
                // AGENCY PLANS (Silver: 3851, Premium: 3852)
                if (in_array($sub->subscription_plan_id, [3851, 3852])) { $has_agency = true; } 
                // ARTIST PLANS (Silver: 3849, Elite: 3850)
                if (in_array($sub->subscription_plan_id, [3849, 3850])) { $has_artist = true; } 
            } 
        } 
    } 

    ob_start(); 
    if ($has_agency) { 
        echo '<h2 style="color:#D4AF37;text-align:center;">Complete Agency Profile</h2>'; 
        echo do_shortcode('[forminator_form id="2992"]'); 
    } elseif ($has_artist) { 
        echo '<h2 style="color:#D4AF37;text-align:center;">Complete Artist Profile</h2>'; 
        echo do_shortcode('[forminator_form id="2915"]'); 
    } else { 
        echo '<p style="color:red;text-align:center;">No valid subscription found. Please contact support.</p>'; 
    } 
    return ob_get_clean(); 
});
