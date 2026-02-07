<?php

/**
 * [kast_unified_profile]
 */
// 1. ARTIST LAYOUT SHORTCODE
add_shortcode('kast_artist_layout', function() {
    ob_start();
    ?>
    <div class="kast-profile-container">
        <div class="kast-left-col">
            <div class="kast-photo-wrapper">[artist_photo]</div>
            [kast_smart_buttons]
        </div>
        <div class="kast-right-col">
            <h1 class="kast-name">[artist_data key="first_name"] [artist_data key="last_name"]</h1>
            <div class="kast-category">[artist_data key="talent_category"]</div>
            <hr class="kast-line">
            <h3 class="kast-section-title">Personal Details</h3>
            <div class="kast-grid-2">
                <div class="k-info"><label>Username</label> <span>[artist_data key="user_login"]</span></div>
                <div class="k-info"><label>DOB</label> <span>[artist_data key="birth_date"]</span></div>
                <div class="k-info"><label>Gender</label> <span>[artist_data key="gender"]</span></div>
                <div class="k-info"><label>Current City</label> <span>[artist_data key="city"]</span></div>
                <div class="k-info"><label>Eye Color</label> <span>[artist_data key="eye_color"]</span></div>
                <div class="k-info"><label>Work Experience</label> <span>[artist_auto_experience]</span></div>
                <div class="k-info"><label>Height</label> <span>[artist_data key="height"]</span></div>
                <div class="k-info"><label>Weight</label> <span>[artist_data key="weight"]</span></div>
                <div class="k-info"><label>Chest</label> <span>[artist_data key="chest"]</span></div>
                <div class="k-info"><label>Bust</label> <span>[artist_data key="bust"]</span></div>
                <div class="k-info"><label>Waist</label> <span>[artist_data key="waist"]</span></div>
                <div class="k-info"><label>Hips</label> <span>[artist_data key="hips"]</span></div>
                <div class="k-info"><label>Shoe Size</label> <span>[artist_data key="shoe_size"]</span></div>
                <div class="k-info full-width"><label>Languages</label> <span>[artist_data key="languages"]</span></div>
                <div class="k-info"><label>Hobbies</label> <span>[artist_data key="hobbies"]</span></div>
            </div>
            <h3 class="kast-section-title">Contact Info</h3>
            <div class="kast-grid-2">
                <div class="k-info"><label>Mobile Number</label> <span>[artist_data key="mobile_number"]</span></div>
                <div class="k-info"><label>Whatsapp Number</label> <span>[artist_data key="whatsapp_number"]</span></div>
            </div>
        </div>
    </div>

    <style>
    .kast-profile-container { width: 100%; max-width: 1400px; margin: 20px auto; display: flex; background-color: #000; border: 1px solid #222; padding: 40px; gap: 50px; box-shadow: 0 0 50px rgba(0,0,0,0.8); }
    .kast-left-col { flex: 1; display: flex; flex-direction: column; }
    .kast-right-col { flex: 1; }
    .kast-name { font-family: serif; color: #D4AF37; font-size: 45px; margin: 0; line-height: 1.1; text-transform: uppercase; }
    .kast-category { color: #fff; font-size: 16px; letter-spacing: 3px; text-transform: uppercase; margin-top: 10px; opacity: 0.8; }
    .kast-line { border-color: #333; margin: 25px 0; }
    .kast-section-title { color: #D4AF37; font-size: 16px; text-transform: uppercase; border-bottom: 1px solid #333; padding-bottom: 8px; margin: 30px 0 20px 0; letter-spacing: 1px; }
    .kast-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .k-info.full-width { grid-column: 1 / -1; }
    .k-info label { display: block; color: #aaa; font-size: 11px; text-transform: uppercase; margin-bottom: 5px; }
    .k-info span { color: #fff; font-size: 15px; font-weight: 500; }
    .kast-photo-wrapper img { width: 100%; height: auto; border: 3px solid #D4AF37; }
    @media (max-width: 900px) { .kast-profile-container { width: 95%; flex-direction: column; padding: 20px; gap: 30px; } }
    </style>

    <div class="kast-gallery-container">
        <h3 class="kast-gal-title">PORTFOLIO GALLERY</h3>
        [artist_portfolio]
    </div>
    <div id="kastImagePopup" class="kast-img-popup"><img id="kastPopupImg" src="" alt="Preview"></div>

    <style>
    .kast-gallery-container { width: 100%; max-width: 1400px; margin: 30px auto; background: #000; border: 1px solid #222; padding: 40px; }
    .kast-gal-title { text-align: center; color: #D4AF37; border-bottom: 1px solid #333; padding-bottom: 15px; margin-bottom: 30px; font-size: 20px; text-transform: uppercase; letter-spacing: 2px; }
    .kast-gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .kast-gallery-grid img { width: 100%; height: auto; display: block; cursor: zoom-in; border-radius: 4px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .kast-gallery-grid img:hover { transform: scale(1.03); box-shadow: 0 0 25px rgba(212,175,55,0.3); }
    @media (max-width: 768px) { .kast-gallery-grid { grid-template-columns: repeat(2, 1fr); } }
    .kast-img-popup { position: fixed; inset: 0; background: rgba(0,0,0,0.95); display: none; justify-content: center; align-items: center; z-index: 999999; cursor: zoom-out; }
    .kast-img-popup img { max-width: 90%; max-height: 90%; object-fit: contain; border: 3px solid #D4AF37; border-radius: 6px; box-shadow: 0 0 40px rgba(212,175,55,0.35); cursor: default; }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const popup = document.getElementById("kastImagePopup");
        const popupImg = document.getElementById("kastPopupImg");
        document.addEventListener("click", function (e) {
            if (e.target.closest(".kast-gallery-grid img")) {
                popupImg.src = e.target.src;
                popup.style.display = "flex";
                document.body.style.overflow = "hidden";
            }
            if (e.target === popup) { closePopup(); }
        });
        document.addEventListener("keydown", function (e) { if (e.key === "Escape") { closePopup(); } });
        function closePopup() { popup.style.display = "none"; popupImg.src = ""; document.body.style.overflow = ""; }
    });
    </script>
    <?php
    return do_shortcode(ob_get_clean());
});

// 2. AGENCY LAYOUT SHORTCODE
add_shortcode('kast_agency_layout', function() {
    ob_start();
    ?>
    <div class="kast-premium-profile">
        <div id="kast-view-mode">
            <div class="kast-profile-container">
                <div class="kast-left-col">
                    <div class="kast-photo-wrapper">[user_profile_picture]</div>
                    <div class="kast-action-stack">
                        <button id="kast-toggle-edit-btn" class="action-btn-gold-long"><i class="fa fa-edit"></i> EDIT PROFILE</button>
                        <a href="/post-an-audition/" class="action-btn-gold-long"><i class="fa fa-plus-circle"></i> POST AUDITION</a>
                        <div class="chat-btn-container-premium">[kast_agency_smart_buttons]</div>
                        <a href="/dashboard/" class="action-btn-gold-long"><i class="fa fa-chart-line"></i> DASHBOARD</a>
                    </div>
                </div>
                <div class="kast-right-col">
                    <h1 class="kast-name">[user_meta key="agency_name"]</h1>
                    <div class="kast-category">[user_meta key="agency_category"]</div>
                    <div class="handle-badge-inline">@[user_meta key="user_name"]</div>
                    <hr class="kast-line">
                    <h3 class="kast-section-title">About Our Agency</h3>
                    <div class="agency-description-text">[user_meta key="description"]</div>
                    <h3 class="kast-section-title">Agency Details</h3>
                    <div class="kast-grid-2">
                        <div class="k-info"><label>Contact Person</label> <span>[user_meta key="contact_person_name"]</span></div>
                        <div class="k-info"><label>City</label> <span>[user_meta key="city"]</span></div>
                        <div class="k-info"><label>Working Since</label> <span>[user_meta key="working_since"]</span></div>
                        <div class="k-info"><label>Membership</label> <span class="gold-text">[user_meta key="subscription_plan"]</span></div>
                    </div>
                    <h3 class="kast-section-title">Contact Information</h3>
                    <div class="kast-grid-2">
                        <div class="k-info"><label>Mobile Number</label> <span>[user_meta key="mobile_number"]</span></div>
                        <div class="k-info"><label>Whatsapp Number</label> <span>[user_meta key="whatsapp_number"]</span></div>
                        <div class="k-info full-width"><label>Email Address</label> <span>[user_email]</span></div>
                        <div class="k-info full-width"><label>Official Website</label> <a href="[user_meta key='website_url']" target="_blank" style="color:#D4AF37;">Visit Site</a></div>
                    </div>
                    <h3 class="kast-section-title">Location & Social</h3>
                    <div class="k-info full-width"><label>Full Address</label> <span>[user_meta key="address"]</span></div>
                    <div class="social-links-gold">
                        <a href="[user_meta key='instagram_profile']"><i class="fab fa-instagram"></i> Instagram</a>
                        <a href="[user_meta key='facebook_profile']"><i class="fab fa-facebook"></i> Facebook</a>
                    </div>
                </div>
            </div>
        </div>
        <div id="kast-edit-mode" style="display: none;">
            <div class="edit-form-wrap">
                <div class="form-header-premium">
                    <h2>Update Agency Profile</h2>
                    <button id="kast-cancel-edit-btn" class="btn-close-edit">X Close</button>
                </div>
                [forminator_form id="4313"]
            </div>
        </div>
    </div>

    <style>
    .kast-profile-container { width: 100%; max-width: 1400px; margin: 20px auto; display: flex; background-color: #000; border: 1px solid #222; padding: 40px; gap: 50px; box-shadow: 0 0 50px rgba(0,0,0,0.8); border-radius: 15px; }
    .kast-left-col { flex: 1; display: flex; flex-direction: column; gap: 30px; }
    .kast-right-col { flex: 1; }
    .kast-photo-wrapper { background: #111; border: 3px solid #D4AF37; border-radius: 12px; overflow: hidden; line-height: 0; width: 100%; aspect-ratio: 3 / 3; display: flex; align-items: center; justify-content: center; }
    .kast-photo-wrapper img { width: 100% !important; height: 100% !important; object-fit: cover !important; border-radius: 0 !important; }
    .kast-name { font-family: serif; color: #D4AF37; font-size: 45px; margin: 0; line-height: 1.1; text-transform: uppercase; }
    .kast-category { color: #fff; font-size: 16px; letter-spacing: 3px; text-transform: uppercase; margin-top: 10px; opacity: 0.8; }
    .handle-badge-inline { color: #D4AF37; font-size: 14px; margin-top: 5px; opacity: 0.6; }
    .kast-line { border-color: #333; margin: 25px 0; }
    .kast-section-title { color: #D4AF37; font-size: 16px; text-transform: uppercase; border-bottom: 1px solid #333; padding-bottom: 8px; margin: 30px 0 20px 0; letter-spacing: 1px; }
    .kast-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .k-info.full-width { grid-column: 1 / -1; }
    .k-info label { display: block; color: #888; font-size: 11px; text-transform: uppercase; margin-bottom: 5px; }
    .k-info span { color: #fff; font-size: 15px; font-weight: 500; }
    .gold-text { color: #D4AF37 !important; font-weight: bold !important; }
    .agency-description-text { color: #bbb; line-height: 1.6; font-size: 15px; }
    .kast-action-stack { display: flex; flex-direction: column; gap: 15px; }
    .action-btn-gold-long, .chat-btn-container-premium button, .chat-btn-container-premium a { width: 100%; display: flex !important; align-items: center; justify-content: center; gap: 10px; padding: 16px !important; background: linear-gradient(to bottom, #D4AF37, #B8860B) !important; color: #000 !important; font-weight: 700 !important; font-size: 14px !important; text-transform: uppercase; border-radius: 8px !important; border: none !important; text-decoration: none !important; cursor: pointer; transition: 0.3s; }
    .action-btn-gold-long:hover { filter: brightness(1.2); transform: translateY(-2px); }
    .social-links-gold { display: flex; gap: 20px; margin-top: 10px; }
    .social-links-gold a { color: #D4AF37; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .edit-form-wrap { background: #111; padding: 40px; border-radius: 15px; border: 1px solid #333; }
    .form-header-premium { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #D4AF37; padding-bottom: 10px; }
    .btn-close-edit { background: #ff4444; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; }
    @media (max-width: 900px) { .kast-profile-container { flex-direction: column; padding: 20px; gap: 30px; } .kast-name { font-size: 32px; } .kast-grid-2 { grid-template-columns: 1fr; } }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var eb = document.getElementById("kast-toggle-edit-btn"), cb = document.getElementById("kast-cancel-edit-btn");
        var vm = document.getElementById("kast-view-mode"), em = document.getElementById("kast-edit-mode");
        if(eb) eb.onclick = function() { vm.style.display = "none"; em.style.display = "block"; window.scrollTo({top: 0, behavior: 'smooth'}); };
        if(cb) cb.onclick = function() { em.style.display = "none"; vm.style.display = "block"; };
        
        // Refresh page when Forminator form is submitted successfully
        document.addEventListener('frm_after_submit', function(e) {
            setTimeout(function() {
                location.reload();
            }, 1000);
        });
    });
    </script>
    <?php
    return do_shortcode(ob_get_clean());
});

// 3. MAIN SWITCHER (Logic remains same)
add_shortcode('kast_profile_switcher', function() {
    $profile_id = um_profile_id();
    if (!$profile_id) return '';
    $user = get_userdata($profile_id);
    if (!$user) return '';

    $user_role = $user->roles[0];
    if ($user_role === 'agency' || $user_role === 'um_agency') {
        return do_shortcode('[kast_agency_layout]');
    } else {
        return do_shortcode('[kast_artist_layout]');
    }
});
