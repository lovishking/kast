<?php

/**
 * KAST - Find Talent Engine V2
 */
/**
 * KAST - Find Talent Shortcode v2
 * शॉर्टकोड: [kast_find_talent_v2]
 */
add_shortcode('kast_find_talent_v2', function() {
    
    // 1. डेटा फिल्टरिंग लॉजिक (GET Parameters से)
    $s_cat    = isset($_GET['f_cat']) ? sanitize_text_field($_GET['f_cat']) : '';
    $s_gender = isset($_GET['f_gender']) ? sanitize_text_field($_GET['f_gender']) : '';
    $s_city   = isset($_GET['f_city']) ? sanitize_text_field($_GET['f_city']) : '';
    $paged    = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $args = [
        'role__in' => array('subscriber', 'um_artist', 'artist'), // टारगेट रोल्स
        'number'   => 20, // हर पेज पर यूजर्स की संख्या
        'paged'    => $paged,
    ];

    $meta_query = ['relation' => 'AND'];

    // केटेगरी फिल्टर
    if (!empty($s_cat)) {
        $meta_query[] = [
            'key'     => 'talent_category',
            'value'   => $s_cat,
            'compare' => 'LIKE'
        ];
    }

    // जेंडर फिल्टर
    if (!empty($s_gender)) {
        $meta_query[] = [
            'key'     => 'gender',
            'value'   => $s_gender,
            'compare' => '='
        ];
    }

    // शहर फिल्टर
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
    $users      = $user_query->get_results();
    $total_users = $user_query->get_total();

    ob_start(); 
    ?>

    <!-- 2. इंटरनल CSS (डार्क लग्जरी थीम) -->
    <style>
        .kast-wrapper { width: 100%; max-width: 1200px; margin: 0 auto; font-family: 'Segoe UI', Arial, sans-serif; }
        
        /* सर्च बार डिजाइन */
        .kast-search-bar { 
            background: #111; border: 1px solid #333; padding: 20px; border-radius: 8px; 
            display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 40px; align-items: center; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.7); 
        }
        .kast-field-group { flex: 1; min-width: 200px; }
        .kast-input, .kast-select { 
            width: 100%; background: #000; border: 1px solid #444; color: #fff; 
            padding: 12px 15px; border-radius: 4px; font-size: 14px; outline: none; transition: 0.3s; 
        }
        .kast-input:focus, .kast-select:focus { border-color: #D4AF37; }
        .kast-search-btn { 
            background: #D4AF37; color: #000; font-weight: bold; border: none; 
            padding: 12px 30px; border-radius: 4px; cursor: pointer; text-transform: uppercase; 
            transition: 0.3s; height: 45px; 
        }
        .kast-search-btn:hover { background: #fff; transform: translateY(-2px); }
        .kast-clear-btn { color: #888; text-decoration: none; font-size: 14px; margin-left: 10px; }

        /* ग्रिड लेआउट */
        .kast-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }

        /* कार्ड डिजाइन (Netflix Style) */
        .kast-card { 
            position: relative; height: 380px; border-radius: 8px; overflow: hidden; 
            cursor: pointer; background: #000; border: 1px solid #222; transition: 0.3s; 
        }
        .kast-card-img { width: 100%; height: 100%; background-size: cover; background-position: center; transition: 0.5s ease; }
        .kast-card:hover { border-color: #D4AF37; transform: scale(1.02); z-index: 2; }
        .kast-card:hover .kast-card-img { transform: scale(1.1); filter: brightness(0.8); }

        /* ओवरले */
        .kast-card-overlay { 
            position: absolute; bottom: 0; left: 0; right: 0; 
            background: linear-gradient(to top, rgba(0,0,0,1), transparent); 
            padding: 20px; transition: 0.3s; 
        }
        .kast-card-name { color: #D4AF37; font-size: 18px; font-weight: 700; margin: 0; text-transform: uppercase; }
        .kast-card-meta { color: #ccc; font-size: 13px; margin-top: 5px; }
        .kast-card-tag { 
            display: inline-block; background: #333; color: #fff; font-size: 10px; 
            padding: 2px 8px; border-radius: 3px; margin-top: 10px; border: 1px solid #D4AF37; 
        }

        /* पग्गिनेशन (Pagination) */
        .kast-pagination { margin-top: 40px; text-align: center; }
        .kast-pagination a, .kast-pagination span { 
            display: inline-block; padding: 10px 15px; margin: 0 5px; background: #111; 
            color: #fff; border: 1px solid #333; text-decoration: none; border-radius: 4px; 
        }
        .kast-pagination .current { background: #D4AF37; color: #000; border-color: #D4AF37; }
    </style>

    <div class="kast-wrapper">
        <!-- 3. सर्च फॉर्म -->
        <form method="GET" class="kast-search-bar">
            <div class="kast-field-group">
                <select name="f_cat" class="kast-select">
                    <option value="">All Categories</option>
                    <option value="Model" <?php selected($s_cat, 'Model'); ?>>Model</option>
                    <option value="Actor" <?php selected($s_cat, 'Actor'); ?>>Actor</option>
                    <option value="Influencer" <?php selected($s_cat, 'Influencer'); ?>>Influencer</option>
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
                <input type="text" name="f_city" class="kast-input" placeholder="City" value="<?php echo esc_attr($s_city); ?>">
            </div>

            <div>
                <button type="submit" class="kast-search-btn">Search</button>
            </div>

            <?php if(!empty($s_cat) || !empty($s_gender) || !empty($s_city)): ?>
                <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="kast-clear-btn">✖ Reset</a>
            <?php endif; ?>
        </form>

        <!-- 4. रिजल्ट ग्रिड -->
        <div class="kast-grid">
            <?php if (!empty($users)) : ?>
                <?php foreach ($users as $user) : 
                    $uid = $user->ID;
                    
                    // फोटो लॉजिक
                    $raw_photo = get_user_meta($uid, 'profile_photo', true);
                    $photo_url = '';
                    if (is_numeric($raw_photo)) {
                        $img = wp_get_attachment_image_src($raw_photo, 'large');
                        $photo_url = $img ? $img[0] : '';
                    } else {
                        $photo_url = $raw_photo;
                    }

                    if (empty($photo_url)) {
                        $photo_url = 'https://via.placeholder.com/400x600/111/333?text=KAST';
                    }

                    // डेटा फेचिंग
                    $name = $user->display_name;
                    $city = get_user_meta($uid, 'city', true);
                    $cats = get_user_meta($uid, 'talent_category', true);
                    if(is_array($cats)) $cats = implode(', ', $cats);

                    // प्रोफाइल लिंक
                    $link = function_exists('um_user_profile_url') ? um_user_profile_url($uid) : home_url('/user/'.$user->user_login);
                ?>

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
                    <h3>No talents found.</h3>
                </div>
            <?php endif; ?>
        </div>

        <!-- 5. पग्गिनेशन -->
        <div class="kast-pagination">
            <?php
            echo paginate_links(array(
                'total'   => ceil($total_users / 20),
                'current' => $paged,
                'format'  => '?paged=%#%',
                'type'    => 'plain',
            ));
            ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
});
