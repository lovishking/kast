<?php

/**
 * kast safe autofill edit forms
 */
/**
 * 1. DYNAMIC AUTOFILL LOGIC (Priority 99 ताकि कोई और इसे न रोक सके)
 */
add_filter( 'forminator_field_get_value', 'kast_robust_autofill', 99, 4 );
function kast_robust_autofill( $value, $field, $data, $form_id ) {
    // अगर यूजर खुद कुछ टाइप कर रहा है, तो हम हस्तक्षेप नहीं करेंगे
    if ( ! empty( $value ) ) return $value;

    $user_id = get_current_user_id();
    if ( ! $user_id ) return $value;

    $field_id = isset( $field['element_id'] ) ? $field['element_id'] : '';

    // Artist (3656) and Agency (4313) Mapping
    $mapping = [
        'phone-1'    => 'mobile_number',
        'phone-2'    => 'whatsapp_number',
        'text-1'     => 'contact_person_name',
        'text-2'     => 'current_city',
        'text-3'     => 'height',
        'number-1'   => 'weight',
        'number-4'   => 'bust',
        'number-5'   => 'chest',
        'number-6'   => 'waist',
        'number-7'   => 'hips',
        'number-8'   => 'shoe_size',
        'select-1'   => ($form_id == 4313) ? 'agency_category' : 'gender',
        'select-2'   => 'eye_color',
        'date-1'     => 'birth_date',
        'textarea-1' => 'description',
        'address-1-street_address' => 'address'
    ];

    if ( isset( $mapping[$field_id] ) ) {
        $stored_val = get_user_meta( $user_id, $mapping[$field_id], true );
        if ( ! empty( $stored_val ) ) {
            return $stored_val;
        }
    }
    return $value;
}

/**
 * 2. PORTFOLIO LOGIC (Merge - New Photos on Top)
 * यह सिर्फ आर्टिस्ट फॉर्म (3656) के लिए है
 */
add_action( 'forminator_custom_form_after_save_entry', 'kast_portfolio_append_logic', 10, 2 );
function kast_portfolio_append_logic( $form_id, $entry_id ) {
    // सिर्फ आर्टिस्ट का एडिट फॉर्म
    if ( (int)$form_id !== 3656 ) return;

    $user_id = get_current_user_id();
    $portfolio_field_id = 'upload-1'; // पक्का करें कि आर्टिस्ट पोर्टफोलियो की Upload ID 'upload-1' ही है
    $portfolio_meta_key = 'artist_portfolio'; // आपका UM/Database मेटा की

    // फॉर्म से नई अपलोड की गई फोटोज निकालें
    $submitted_data = Forminator_CForm_Front_Action::$prepared_data;
    $new_photos_data = isset($submitted_data[$portfolio_field_id]) ? $submitted_data[$portfolio_field_id] : '';

    if ( ! empty( $new_photos_data ) ) {
        // पुरानी फोटोज डेटाबेस से निकालें
        $old_photos_raw = get_user_meta( $user_id, $portfolio_meta_key, true );
        
        // पुरानी और नई फोटोज को एरे (Array) में बदलें
        $old_photos_arr = ! empty($old_photos_raw) ? (is_array($old_photos_raw) ? $old_photos_raw : explode(',', $old_photos_raw)) : [];
        $new_photos_arr = is_array($new_photos_data) ? $new_photos_data : explode(',', $new_photos_data);

        // नई फोटोज को पहले (TOP) रखें और पुरानी को बाद में
        $merged_photos = array_merge( $new_photos_arr, $old_photos_arr );
        
        // डुप्लिकेट और खाली वैल्यू हटाएं
        $final_portfolio = array_unique( array_filter( $merged_photos ) );

        // वापस डेटाबेस में अपडेट करें (Comma-separated string के रूप में)
        update_user_meta( $user_id, $portfolio_meta_key, implode(',', $final_portfolio) );
    }
}

/**
 * 3. PROFILE WRAPPER LOGIC (इसमें कोई बदलाव नहीं किया गया है ताकि आपका डिज़ाइन न बिगड़े)
 */
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
