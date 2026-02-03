<?php

/**
 * KAST - Home New Talents
 */
function kast_home_6_final_fix() {

    // 1. QUERY: Latest 12 Artists (taaki photo filter ke baad bhi 6 mil jaaye)
    $args = array(
        'role__in' => array('um_artist', 'artist'),
        'number'   => 12,
        'orderby'  => 'registered',
        'order'    => 'DESC'
    );

    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();

    if (empty($users)) return '';

    ob_start();
    ?>

    <style>
        .k-home-wrap { 
            width: 100%;
            background: #000; 
            padding: 20px 0; 
            font-family: 'Arial', sans-serif; 
        }

        .k-home-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr); 
            gap: 15px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .k-home-card {
            position: relative;
            height: 350px;
            border: 1px solid #222;
            border-radius: 6px;
            overflow: hidden;
            transition: 0.3s;
            background: #0a0a0a;
            cursor: pointer;
        }

        .k-home-card:hover {
            border-color: #D4AF37;
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.2);
        }

        .k-home-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;
            transition: 0.5s;
            display: block;
        }

        .k-home-card:hover .k-home-img { transform: scale(1.05); }

        .k-home-info {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, #000 20%, rgba(0,0,0,0.9) 100%);
            padding: 15px 5px;
            text-align: center;
        }

        .k-home-name {
            color: #D4AF37; 
            font-size: 14px; 
            font-weight: 700;
            text-transform: uppercase; 
            margin-bottom: 3px;
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis;
        }
        
        .k-home-cat {
            color: #fff; 
            font-size: 10px; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .k-badge {
            position: absolute; 
            top: 10px; 
            right: 10px;
            background: #D4AF37; 
            color: #000;
            font-size: 9px; 
            font-weight: 800;
            padding: 3px 6px; 
            border-radius: 2px;
            text-transform: uppercase; 
            z-index: 2;
        }

        @media (max-width: 1024px) { 
            .k-home-grid { grid-template-columns: repeat(3, 1fr); } 
        }

        @media (max-width: 768px) { 
            .k-home-grid { grid-template-columns: repeat(2, 1fr); } 
        }
    </style>

    <div class="k-home-wrap">
        <div class="k-home-grid">

            <?php
            $shown = 0;

            foreach ($users as $user) {

                if ($shown >= 6) break;

                $uid = $user->ID;

                // -------- PHOTO FILTER --------

                $photo_url = '';

                $p_raw = get_user_meta($uid, 'upload-1', true);
                if (empty($p_raw)) {
                    $p_raw = get_user_meta($uid, 'profile_photo', true);
                }

                if (is_array($p_raw)) {
                    $p_raw = isset($p_raw['file_url']) ? $p_raw['file_url'] : reset($p_raw);
                }

                if (is_numeric($p_raw)) {
                    $img = wp_get_attachment_image_src($p_raw, 'large');
                    $photo_url = $img ? $img[0] : '';
                } elseif (!empty($p_raw) && is_string($p_raw)) {
                    $photo_url = $p_raw;
                }

                // 👉 बिना फोटो वाले artist skip
                if (empty($photo_url)) continue;

                $shown++;

                // -------- NAME --------

                $fname = get_user_meta($uid, 'first_name', true);
                $lname = get_user_meta($uid, 'last_name', true);
                $name  = (!empty($fname)) ? $fname . ' ' . $lname : $user->display_name;

                // -------- CATEGORY (Actor / Model / Influencer) --------

                $role_raw = get_user_meta($uid, 'i_am_interested_in', true);
                $role_display = '';

                if (!empty($role_raw)) {
                    if (is_array($role_raw)) {
                        $role_display = implode(', ', $role_raw);
                    } else {
                        $unser = maybe_unserialize($role_raw);
                        $role_display = is_array($unser) ? implode(', ', $unser) : $role_raw;
                    }
                }

                if (empty($role_display)) {
                    $role_display = 'Artist';
                }

                // -------- PROFILE LINK --------

                $user_info = get_userdata($uid);
                $link = home_url('/user/' . $user_info->user_login . '/');
                ?>

                <div class="k-home-card" onclick="window.location='<?php echo esc_url($link); ?>';">
                    <span class="k-badge">New</span>

                    <img src="<?php echo esc_url($photo_url); ?>" class="k-home-img">

                    <div class="k-home-info">
                        <div class="k-home-name"><?php echo esc_html($name); ?></div>
                        <div class="k-home-cat"><?php echo esc_html($role_display); ?></div>
                    </div>
                </div>

            <?php } ?>

        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('kast_home_newest', 'kast_home_6_final_fix');
