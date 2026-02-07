<?php

/**
 * KAST Artist Directory
 */
function kast_talent_directory_func() {
    // 1. Get all Subscribers
    $args = array(
        'role'    => 'subscriber',
        'orderby' => 'registered',
        'order'   => 'DESC',
        'number'  => -1
    );
    
    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();
    
    ob_start();
    
    if (!empty($users)) {
        echo '<div class="kast-talent-grid">';
        
        foreach ($users as $user) {
            $uid = $user->ID;
            
            // =========================================================
            // IMPORTANT: Yahan apni sahi META KEYS dalni hain
            // (Ye Forminator > User Registration > User Meta se check karein)
            // =========================================================
            
            // 1. Profile Photo (Agar custom upload hai)
            // Forminator me check karein aapne photo kahan map ki hai (e.g., 'profile_photo', 'user_avatar')
            // Agar nahi pata, to hum pehle 'profile_photo' try karenge, fir default avatar.
            $photo_url = get_user_meta($uid, 'profile_photo', true); 
            
            // Fallback: Agar photo nahi hai to default gray placeholder
            if(empty($photo_url)) {
                $photo_url = get_avatar_url($uid, array('size' => 400));
            }

            // 2. Details
            $name = $user->display_name;
            
            // Note: In keys ko apne form ke hisab se badal lein agar alag hain
            $city = get_user_meta($uid, 'city', true); 
            $category = get_user_meta($uid, 'artist_category', true); 
            $gender = get_user_meta($uid, 'gender', true); 

            // Fallback Text
            if(empty($city)) $city = 'India';
            if(empty($category)) $category = 'Talent';
            if(empty($gender)) $gender = 'any';

            // Profile Link
            $profile_url = home_url('/profile/' . $user->user_login);

            // =========================================================
            // HTML OUTPUT (Card)
            // =========================================================
            echo '<div class="kast-talent-card" 
                       data-city="'.strtolower(trim($city)).'" 
                       data-gender="'.strtolower(trim($gender)).'" 
                       data-category="'.strtolower(trim($category)).'">
                       
                    <div class="kast-card-img">
                        <img src="'.$photo_url.'" alt="'.$name.'">
                        <span class="kast-badge">'.$category.'</span>
                    </div>
                    
                    <div class="kast-card-info">
                        <h3>'.$name.'</h3>
                        <p class="kast-location"><i class="fa fa-map-marker"></i> '.$city.'</p>
                        <a href="'.$profile_url.'" class="kast-view-btn">View Profile</a>
                    </div>
                  </div>';
        }
        echo '</div>';
    } else {
        echo '<p style="color:#fff; text-align:center;">No talent found.</p>';
    }
    
    return ob_get_clean();
}
add_shortcode('kast_find_talent', 'kast_talent_directory_func');
