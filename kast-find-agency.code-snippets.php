<?php

/**
 * Kast Find Agency
 */
add_shortcode('kast_find_agencies', function() {

    // ==========================================
    // 1. SEARCH LOGIC (DATA FILTERING)
    // ==========================================

    $s_cat    = isset($_GET['f_cat']) ? sanitize_text_field($_GET['f_cat']) : '';
    $s_gender = isset($_GET['f_gender']) ? sanitize_text_field($_GET['f_gender']) : '';
    $s_city   = isset($_GET['f_city']) ? sanitize_text_field($_GET['f_city']) : '';

    $args = [
        'role__in' => array('subscriber', 'um_agency', 'agency'),
        'number'   => 12,
        'paged'    => get_query_var('paged') ? get_query_var('paged') : 1,
    ];

    $meta_query = ['relation' => 'AND'];

    if (!empty($s_cat)) {
        $meta_query[] = [
            'key'     => 'agency_category',
            'value'   => $s_cat,
            'compare' => 'LIKE'
        ];
    }

    if (!empty($s_gender)) {
        $meta_query[] = [
            'key'     => 'gender',
            'value'   => $s_gender,
            'compare' => '='
        ];
    }

    if (!empty($s_city)) {
        $meta_query[] = [
            'key'     => 'city',
            'value'   => $s_city,
            'compare' => 'LIKE'
        ];
    }

    if (count($meta_query) > 1) {
        $args['meta_query'] = $meta_query;
    }

    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();

    ob_start();
    ?>

    <style>
        .kast-wrapper { width: 100%; max-width: 1200px; margin: 0 auto; font-family: 'Arial', sans-serif; }
        .kast-search-bar {
            background: #111; border: 1px solid #333; padding: 20px; border-radius: 8px;
            display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 40px; align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        .kast-field-group { flex: 0 0 22%; min-width: 220px; }
        .kast-field-group:nth-child(3) { flex: 0 0 28%; }
        .kast-search-bar > div:last-child { flex: 0 0 auto; }

        .kast-input, .kast-select {
            width: 100%; background: #000; border: 1px solid #444; color: #fff;
            padding: 12px 15px; border-radius: 4px; font-size: 12px;
            outline: none; transition: 0.3s;
        }

        .kast-input:focus, .kast-select:focus { border-color: #D4AF37; }

        .kast-search-btn {
            background: #D4AF37; color: #000; font-weight: bold; border: none;
            padding: 12px 30px; border-radius: 4px; cursor: pointer;
            text-transform: uppercase; transition: 0.3s; height: 42px;
        }

        .kast-search-btn:hover { background: #fff; }

        .kast-clear-btn { color: #888; text-decoration: none; font-size: 14px; margin-left: 10px; }
        .kast-clear-btn:hover { color: #fff; }

        .kast-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        .kast-card {
            position: relative;
            height: 380px;
            border-radius: 6px;
            overflow: hidden;
            cursor: pointer;
            background: #000;
            border: 1px solid #222;
            transition: transform 0.3s ease;
        }

        .kast-card-img {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: top center;
            transition: transform 0.5s ease;
        }

        .kast-card:hover .kast-card-img { transform: scale(1.1); }

        .kast-card-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.95), transparent);
            padding: 20px;
            transform: translateY(10px);
            transition: 0.3s;
        }

        .kast-card:hover .kast-card-overlay { transform: translateY(0); }

        .kast-card-name {
            color: #D4AF37;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
        }

        .kast-card-meta { color: #ccc; font-size: 13px; margin-top: 5px; }

        .kast-card-tag {
            display: inline-block;
            background: #333;
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-top: 8px;
            border: 1px solid #D4AF37;
            text-transform: uppercase;
        }
    </style>

    <div class="kast-wrapper">

        <form method="GET" class="kast-search-bar">

            <div class="kast-field-group">
                <select name="f_cat" class="kast-select">
                    <option value="">All Categories</option>
                    <option value="Model Agency" <?php selected($s_cat, 'Model Agency'); ?>>Model Agency</option>
                    <option value="Casting Agency" <?php selected($s_cat, 'Casting Agency'); ?>>Casting Agency</option>
                    <option value="Production House" <?php selected($s_cat, 'Production House'); ?>>Production House</option>
                </select>
            </div>

            <div class="kast-field-group">
                <select name="f_gender" class="kast-select">
                    <option value="">Any Gender</option>
                    <option value="Male" <?php selected($s_gender, 'Male'); ?>>Male</option>
                    <option value="Female" <?php selected($s_gender, 'Female'); ?>>Female</option>
                </select>
            </div>

            <div class="kast-field-group">
                <input type="text" name="f_city" class="kast-input"
                       placeholder="City (e.g. Mumbai)"
                       value="<?php echo esc_attr($s_city); ?>">
            </div>

            <div>
                <button type="submit" class="kast-search-btn">Search</button>
            </div>

            <?php if(!empty($s_cat) || !empty($s_gender) || !empty($s_city)): ?>
                <a href="<?php echo get_permalink(); ?>" class="kast-clear-btn">✖ Reset</a>
            <?php endif; ?>

        </form>

        <div class="kast-grid">

            <?php if (!empty($users)) : ?>
                <?php foreach ($users as $user) :

                    $uid = $user->ID;

                    // Photo Logic
                    $raw_photo = get_user_meta($uid, 'profile_photo', true);
                    $photo_url = is_numeric($raw_photo)
                        ? (wp_get_attachment_image_src($raw_photo, 'large')
                            ? wp_get_attachment_image_src($raw_photo, 'large')[0]
                            : '')
                        : $raw_photo;

                    if (empty($photo_url)) {
                        $photo_url = 'https://via.placeholder.com/400x600/111/333?text=Agency';
                    }

                    // --- AGENCY DISPLAY DATA ---
                    $agency_name = get_user_meta($uid, 'agency_name', true);
                    if(empty($agency_name)) $agency_name = $user->display_name;

                    $agency_city = get_user_meta($uid, 'city', true);

                    $agency_cat = get_user_meta($uid, 'agency_category', true);
                    if(is_array($agency_cat)) $agency_cat = implode(', ', $agency_cat);

                    $link = function_exists('um_user_profile_url')
                        ? um_user_profile_url($uid)
                        : home_url('/user/'.$user->user_login);
                ?>

                <div class="kast-card" onclick="location.href='<?php echo esc_url($link); ?>'">

                    <div class="kast-card-img"
                         style="background-image: url('<?php echo esc_url($photo_url); ?>');">
                    </div>

                    <div class="kast-card-overlay">
                        <h3 class="kast-card-name"><?php echo esc_html($agency_name); ?></h3>
                        <div class="kast-card-meta">
                            📍 <?php echo $agency_city ? esc_html($agency_city) : 'India'; ?>
                        </div>

                        <?php if($agency_cat): ?>
                            <div class="kast-card-tag"><?php echo esc_html($agency_cat); ?></div>
                        <?php endif; ?>
                    </div>

                </div>

                <?php endforeach; ?>

            <?php else : ?>

                <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #fff;">
                    <h3>No agencies found.</h3>
                </div>

            <?php endif; ?>

        </div>
    </div>

    <?php
    return ob_get_clean();
});
