<?php

/**
 * KAST - Find Talent Engine V2
 */
add_shortcode('kast_find_talent_v2', function() {
    // ==========================================
    // 1. SEARCH LOGIC (DATA FILTERING)
    // ==========================================
    
    $s_cat    = isset($_GET['f_cat']) ? sanitize_text_field($_GET['f_cat']) : '';
    $s_gender = isset($_GET['f_gender']) ? sanitize_text_field($_GET['f_gender']) : '';
    $s_city   = isset($_GET['f_city']) ? sanitize_text_field($_GET['f_city']) : '';

    $args = [
       'role__in' => array('subscriber', 'um_artist', 'artist'), // Target Roles
        'number'   => 12, // Users per page
        'paged'    => get_query_var('paged') ? get_query_var('paged') : 1,
    ];

    $meta_query = ['relation' => 'AND'];

    // Category Filter
    if (!empty($s_cat)) {
        $meta_query[] = [
            'key'     => 'talent_category', // Humari fixed key
            'value'   => $s_cat,
            'compare' => 'LIKE' // LIKE zaroori hai arrays ke liye
        ];
    }

    // Gender Filter
    if (!empty($s_gender)) {
        $meta_query[] = [
            'key'     => 'gender',
            'value'   => $s_gender,
            'compare' => '='
        ];
    }

    // City Filter
    if (!empty($s_city)) {
        $meta_query[] = [
            'key'     => 'city',
            'value'   => $s_city,
            'compare' => 'LIKE' // Partial match (e.g. Mumbai match karega Navi Mumbai se)
        ];
    }

    if (count($meta_query) > 1) {
        $args['meta_query'] = $meta_query;
    }

    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();

    ob_start();
    ?>

    <!-- ==========================================
       2. INTERNAL CSS (DARK LUXURY THEME)
    ========================================== -->
    <style>
        /* Container */
        .kast-wrapper { width: 100%; max-width: 1200px; margin: 0 auto; font-family: 'Arial', sans-serif; }

        /* Search Bar Design */
        .kast-search-bar {
            background: #111;
            border: 1px solid #333;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 40px;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
            /* Field width issue */
            .kast-field-group {
            flex: 0 0 22%;
            min-width: 220px;
        }
            /* City field thoda wide */
            .kast-field-group:nth-child(3) {
            flex: 0 0 28%;
        }
            /* Button ko squeeze hone se bachane ke liye */
            .kast-search-bar > div:last-child {
            flex: 0 0 auto;
        }
        
        /* Inputs & Selects */
        .kast-input, .kast-select {
            width: 100%;
            background: #000;
            border: 1px solid #444;
            color: #fff;
            padding: 12px 15px;
            border-radius: 4px;
            font-size: 12px;
            outline: none;
            transition: 0.3s;
        }
        .kast-input:focus, .kast-select:focus { border-color: #D4AF37; }
        
        /* Search Button */
        .kast-search-btn {
            background: #D4AF37;
            color: #000;
            font-weight: bold;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            text-transform: uppercase;
            transition: 0.3s;
            height: 42px; /* Matches input height */
        }
        .kast-search-btn:hover { background: #fff; }

        /* Clear Button */
        .kast-clear-btn {
            color: #888; text-decoration: none; font-size: 14px; margin-left: 10px;
        }
        .kast-clear-btn:hover { color: #fff; }

        /* Grid Layout */
        .kast-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        /* Card Design (Netflix Style) */
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
        
        /* Image Background */
        .kast-card-img {
            width: 100%; height: 100%;
            background-size: cover;
            background-position: top center;
            transition: transform 0.5s ease;
        }
        .kast-card:hover .kast-card-img { transform: scale(1.1); }

        /* Overlay Gradient */
        .kast-card-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.95), transparent);
            padding: 20px;
            transform: translateY(10px);
            transition: 0.3s;
        }
        .kast-card:hover .kast-card-overlay { transform: translateY(0); }

        /* Typography */
        .kast-card-name { color: #D4AF37; font-size: 18px; font-weight: 700; margin: 0; text-transform: uppercase; }
        .kast-card-meta { color: #ccc; font-size: 13px; margin-top: 5px; }
        .kast-card-tag { 
            display: inline-block; background: #333; color: #fff; 
            font-size: 10px; padding: 2px 6px; border-radius: 3px; 
            margin-top: 8px; border: 1px solid #D4AF37;
        }
    </style>

    <div class="kast-wrapper">
        
        <!-- ==========================================
           3. SEARCH FORM (3 Bars + Button)
        ========================================== -->
        <form method="GET" class="kast-search-bar">
            
            <!-- 1. Category -->
            <div class="kast-field-group">
                <select name="f_cat" class="kast-select">
                    <option value="">All Categories</option>
                    <!-- Make sure values match Forminator EXACTLY -->
                    <option value="Model" <?php selected($s_cat, 'Model'); ?>>Model</option>
                    <option value="Actor" <?php selected($s_cat, 'Actor'); ?>>Actor</option>
                    <option value="Influencer" <?php selected($s_cat, 'Influencer'); ?>>Influencer</option>
                </select>
            </div>

            <!-- 2. Gender -->
            <div class="kast-field-group">
                <select name="f_gender" class="kast-select">
                    <option value="">Any Gender</option>
                    <option value="Male" <?php selected($s_gender, 'Male'); ?>>Male</option>
                    <option value="Female" <?php selected($s_gender, 'Female'); ?>>Female</option>
                    <option value="Other" <?php selected($s_gender, 'Other'); ?>>Other</option>
                </select>
            </div>

            <!-- 3. City -->
            <div class="kast-field-group">
                <input type="text" name="f_city" class="kast-input" placeholder="City (e.g. Mumbai)" value="<?php echo esc_attr($s_city); ?>">
            </div>

            <!-- Submit -->
            <div>
                <button type="submit" class="kast-search-btn">Search</button>
            </div>

            <?php if(!empty($s_cat) || !empty($s_gender) || !empty($s_city)): ?>
                <a href="<?php echo get_permalink(); ?>" class="kast-clear-btn">✖ Reset</a>
            <?php endif; ?>
        </form>

        <!-- ==========================================
           4. RESULTS GRID
        ========================================== -->
        <div class="kast-grid">
            <?php if (!empty($users)) : ?>
                <?php foreach ($users as $user) : 
                    $uid = $user->ID;
                    
                    // --- SMART PHOTO LOGIC ---
                    $raw_photo = get_user_meta($uid, 'profile_photo', true);
                    $photo_url = '';

                    if (is_numeric($raw_photo)) {
                        $img = wp_get_attachment_image_src($raw_photo, 'large');
                        $photo_url = $img ? $img[0] : '';
                    } elseif (!empty($raw_photo)) {
                        $photo_url = $raw_photo;
                    }
                    
                    // Fallback Image
                    if (empty($photo_url)) {
                        $photo_url = 'https://via.placeholder.com/400x600/111/333?text=No+Image'; 
                    }

                    // --- DATA FETCHING ---
                    $name = $user->display_name;
                    $city = get_user_meta($uid, 'city', true);
                    $cats = get_user_meta($uid, 'talent_category', true);
                    if(is_array($cats)) $cats = implode(', ', $cats);
                    
                    // Profile Link
                    $link = function_exists('um_user_profile_url') ? um_user_profile_url($uid) : home_url('/user/'.$user->user_login);
                ?>

                <!-- CARD HTML -->
                <div class="kast-card" onclick="location.href='<?php echo esc_url($link); ?>'">
                    <div class="kast-card-img" style="background-image: url('<?php echo esc_url($photo_url); ?>');"></div>
                    <div class="kast-card-overlay">
                        <h3 class="kast-card-name"><?php echo esc_html($name); ?></h3>
                        <div class="kast-card-meta">📍 <?php echo $city ? esc_html($city) : 'India'; ?></div>
                        <?php if($cats): ?>
                            <div class="kast-card-tag"><?php echo mb_strimwidth($cats, 0, 25, '...'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php endforeach; ?>
            <?php else : ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #fff;">
                    <h3>No talents found matching your criteria.</h3>
                    <p style="color:#888;">Try removing filters to see more results.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
    <?php
    return ob_get_clean();
});
