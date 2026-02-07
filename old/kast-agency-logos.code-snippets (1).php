<?php

/**
 * KAST - Agency Logos
 */
function kast_agency_logo_display() {
    
    // 1. QUERY: Fetch Agencies Only
    // 'um_agency' aur 'agency' dono check kar rahe hain (aapke role name ke hisab se)
    $args = array(
        'role__in' => array('um_agency', 'agency'), 
        'number'   => 12,      // Max 12 logos dikhayenge
        'orderby'  => 'rand'   // Har baar random order mein dikhenge
    );
    
    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();

    if (empty($users)) return '';

    ob_start();
    ?>

    <!-- STYLE -->
    <style>
        .k-logo-wrap {
            width: 100%;
            padding: 40px 0;
            /* Transparent Background */
            background: transparent; 
            text-align: center;
        }

        /* GRID: Flexible Layout */
        .k-logo-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* Center align logos */
            align-items: center;
            gap: 40px; /* Space between logos */
            max-width: 1200px;
            margin: 0 auto;
        }

        /* LOGO ITEM */
        .k-logo-item {
            width: 150px;       /* Fixed width for uniformity */
            height: 100px;      /* Fixed height */
            display: flex;
            justify-content: center;
            align-items: center;
            transition: 0.3s;
            opacity: 0.7;       /* Thoda dim rakhenge */
            filter: grayscale(100%); /* Black & White Effect (Premium look) */
        }

        /* HOVER EFFECT */
        .k-logo-item:hover {
            opacity: 1;         /* Full bright */
            filter: grayscale(0%); /* Original Color */
            transform: scale(1.1); /* Slight Zoom */
        }

        /* IMAGE STYLE */
        .k-logo-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* Logo katega nahi, pura dikhega */
            display: block;
        }
    </style>

    <div class="k-logo-wrap">
        <!-- Optional Heading (Agar chahiye to uncomment karein) -->
        <!-- <h3 style="color:#D4AF37; text-transform:uppercase; margin-bottom:30px; font-size:16px; letter-spacing:2px;">Our Partner Agencies</h3> -->
        
        <div class="k-logo-grid">
            <?php foreach ($users as $user) {
                $uid = $user->ID;
                
                // --- PHOTO LOGIC (Same robust logic) ---
                $dummy = 'https://via.placeholder.com/150x100/000000/333333?text=AGENCY'; 
                $photo_url = $dummy;

                // 1. Get raw data (upload-1 usually holds the logo)
                $p_raw = get_user_meta($uid, 'upload-1', true);
                if(empty($p_raw)) { $p_raw = get_user_meta($uid, 'profile_photo', true); }

                // 2. Handle Array
                if (is_array($p_raw)) {
                    $p_raw = isset($p_raw['file_url']) ? $p_raw['file_url'] : reset($p_raw);
                }

                // 3. Validation
                if (is_numeric($p_raw)) {
                    $img = wp_get_attachment_image_src($p_raw, 'medium');
                    $photo_url = $img ? $img[0] : $dummy;
                } elseif (!empty($p_raw) && is_string($p_raw)) {
                    $photo_url = $p_raw;
                }

                // Link to Agency Profile
                $link = get_author_posts_url($uid);
                ?>
                
                <a href="<?php echo esc_url($link); ?>" class="k-logo-item" title="<?php echo esc_attr($user->display_name); ?>">
                    <img src="<?php echo esc_url($photo_url); ?>" class="k-logo-img" alt="Agency Logo">
                </a>

            <?php } ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('kast_agency_logos', 'kast_agency_logo_display');
