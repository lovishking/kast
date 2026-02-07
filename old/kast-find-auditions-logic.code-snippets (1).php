<?php

/**
 * Kast Find Auditions Logic
 */
add_shortcode('kast_find_auditions', function() {
    
    // 1. FILTER LOGIC
    $args = [
        'post_type'      => 'post',
        'category_name'  => 'auditions,acting,modeling,influencer',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => get_query_var('paged') ? get_query_var('paged') : 1,
    ];
    
    // Filters...
    if(isset($_GET['f_cat']) && $_GET['f_cat'] != '') {
        $args['meta_query'][] = ['key' => 'category', 'value' => sanitize_text_field($_GET['f_cat']), 'compare' => 'LIKE'];
    }
    if(isset($_GET['f_gender']) && $_GET['f_gender'] != '') {
        $args['meta_query'][] = ['key' => 'gender', 'value' => sanitize_text_field($_GET['f_gender']), 'compare' => '='];
    }
    if(isset($_GET['f_city']) && $_GET['f_city'] != '') {
        $args['meta_query'][] = ['key' => 'city', 'value' => sanitize_text_field($_GET['f_city']), 'compare' => 'LIKE'];
    }

    $query = new WP_Query($args);
    $current_user_id = get_current_user_id();
    $is_logged_in = is_user_logged_in();

    ob_start();
    ?>

    <!-- STYLE -->
    <style>
        .kast-wrapper { width: 100%; max-width: 1200px; margin: 0 auto; font-family: 'Segoe UI', sans-serif; }
        .kast-search-bar { background: #111; border: 1px solid #333; padding: 15px; border-radius: 8px; display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap; }
        .kast-input, .kast-select { background: #000; border: 1px solid #444; color: #fff; padding: 10px; flex: 1; min-width: 150px; border-radius: 4px; }
        .kast-btn { background: #D4AF37; color: #000; border: none; padding: 10px 25px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .kast-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }

        /* CARD DESIGN */
        .kast-card {
            background: #050505; border: 1px solid #222; 
            border-top: 4px solid #D4AF37;
            border-radius: 8px; padding: 25px; display: flex; flex-direction: column;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); transition: 0.3s;
        }
        .kast-card:hover { transform: translateY(-5px); border-color: #444; background: #0a0a0a; }

        .kc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid #222; padding-bottom: 15px; }
        .kc-title { color: #fff; font-size: 20px; margin: 0; line-height: 1.3; font-weight: 700; text-transform: capitalize; }
        .kc-agency { font-size: 13px; color: #777; margin-top: 5px; font-style: italic; }
        
        .kc-budget { 
            color: #D4AF37; border: 1px solid #D4AF37; background: transparent;
            padding: 5px 12px; border-radius: 4px; font-size: 13px; font-weight: bold; white-space: nowrap;
        }

        /* DATA LIST */
        .kc-data-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px; }
        .kc-row { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1a1a1a; padding-bottom: 8px; }
        .kc-label { color: #666; font-size: 13px; }
        .kc-val { color: #eee; font-size: 14px; font-weight: 500; text-align: right; }

        .kc-footer { margin-top: auto; }
        .kc-apply-btn {
            background: #D4AF37; color: #000; border: none; padding: 10px 25px; width: 100%;
            font-weight: bold; text-transform: uppercase; font-size: 13px; border-radius: 4px; cursor: pointer; transition: 0.3s;
        }
        .kc-apply-btn:hover { background: #fff; }
        .kc-apply-btn.applied { background: #222; color: #2ecc71; cursor: default; border: 1px solid #333; }
    </style>

    <div class="kast-wrapper">
        <!-- Search Form -->
        <form method="GET" class="kast-search-bar">
            <select name="f_cat" class="kast-select"><option value="">All Categories</option><option value="Actor">Actor</option><option value="Model">Model</option>option><option value="influencer">influencer</option></select>
            <select name="f_gender" class="kast-select"><option value="">Any Gender</option><option value="Male">Male</option><option value="Female">Female</option></select>
            <input type="text" name="f_city" class="kast-input" placeholder="City" value="<?php echo esc_attr($_GET['f_city'] ?? ''); ?>">
            <button class="kast-btn">SEARCH</button>
        </form>

        <div class="kast-grid">
            <?php if ($query->have_posts()) : ?>
                <?php while ($query->have_posts()) : $query->the_post(); 
                    $pid = get_the_ID();
                    
                    $agency = get_post_meta($pid, 'agency', true);
                    $production = get_post_meta($pid, 'production', true);
                    $project_type = get_post_meta($pid, 'project_type', true);
                    $age = get_post_meta($pid, 'age', true);
                    $shoot_date = get_post_meta($pid, 'shoot_date', true);
                    $city = get_post_meta($pid, 'city', true);
                    $multi_loc = get_post_meta($pid, 'multi_loc', true);
                    $budget = get_post_meta($pid, 'budget', true);
                    $gender = get_post_meta($pid, 'gender', true);
                    $category = get_post_meta($pid, 'category', true);
                    
                    $applicants = get_post_meta($pid, '_kast_applicants', true) ?: [];
                    $has_applied = in_array($current_user_id, $applicants);
                ?>

                <div class="kast-card">
                    <div class="kc-header">
                        <div>
                            <h3 class="kc-title"><?php the_title(); ?></h3>
                            <div class="kc-agency">
                                <?php echo $agency ? esc_html($agency) : ''; ?> 
                                <?php if($production) echo ' • '.esc_html($production); ?>
                            </div>
                        </div>
                        <div class="kc-budget"><?php echo $budget ? '₹'.esc_html($budget) : 'Paid'; ?></div>
                    </div>

                    <!-- Clean Data List (Requirements Hata Diya) -->
                    <div class="kc-data-list">
                        <div class="kc-row">
                            <span class="kc-label">Project Type</span>
                            <span class="kc-val">🎬 <?php echo $project_type ?: '-'; ?></span>
                        </div>
                        <div class="kc-row">
                            <span class="kc-label">Looking For</span>
                            <span class="kc-val">👤 <?php echo $category ?: '-'; ?> (<?php echo $gender ?: 'Any'; ?>)</span>
                        </div>
                        <div class="kc-row">
                            <span class="kc-label">Age Group</span>
                            <span class="kc-val"><?php echo $age ?: '-'; ?></span>
                        </div>
                        <div class="kc-row">
                            <span class="kc-label">Shoot Date</span>
                            <span class="kc-val">📅 <?php echo $shoot_date ?: 'TBD'; ?></span>
                        </div>
                        <div class="kc-row">
                            <span class="kc-label">Location</span>
                            <span class="kc-val">📍 <?php echo $city ?: 'Online'; ?> <?php if($multi_loc) echo '+'; ?></span>
                        </div>
                    </div>

                    <div class="kc-footer">
                        <?php if($has_applied): ?>
                            <button class="kc-apply-btn applied">✅ Applied</button>
                        <?php else: ?>
                            <button class="kc-apply-btn js-apply-btn" data-id="<?php echo $pid; ?>">APPLY NOW</button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php endwhile; wp_reset_postdata(); ?>
            <?php else : ?>
                <p style="color:#fff; text-align:center;">No auditions found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- AJAX Script -->
    <script>
    jQuery(document).ready(function($) {
        $('.js-apply-btn').click(function(e) {
            e.preventDefault();
            var btn = $(this);
            btn.text('Applying...').css('opacity', '0.7');
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: { action: 'kast_apply_audition', post_id: btn.data('id') },
                success: function(r) {
                    if(r.success) btn.addClass('applied').text('✅ Applied').prop('disabled',true).css('opacity','1');
                    else alert(r.data);
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
});
