<?php

/**
 * Developer Image Fix
 */
add_action( 'forminator_form_after_handle_submit', 'kast_save_profile_updates', 10, 2 );

function kast_save_profile_updates( $form_id, $response ) {
    $user_id = get_current_user_id();
    if ( !$user_id ) return;

    // 1. Get Form Data
    $entry_model = Forminator_Form_Entry_Model::get_latest_entry_by_form_id( $form_id );
    if ( !$entry_model ) return;
    $meta_data = $entry_model->meta_data;
    
    // Counter to track upload fields
    $upload_count = 0;

    foreach ( $meta_data as $field_id => $data ) {
        
        // 1. IMAGE HANDLING
        if ( strpos($field_id, 'upload') === 0 ) {
            $upload_count++;
            $value = $data['value'];
            $found_url = '';

            // Extract Raw Data
            if ( isset($value['file']['file_url']) ) { $found_url = $value['file']['file_url']; }
            elseif ( isset($value['file_url']) ) { $found_url = $value['file_url']; }
            elseif ( is_array($value) && isset($value[0]) ) { $found_url = $value; }
            elseif ( is_string($value) ) { $found_url = $value; }

            // Normalize to Array of Strings
            $all_urls = [];
            if ( is_array($found_url) ) {
                foreach($found_url as $u) { if(is_string($u) && !empty($u)) $all_urls[] = $u; }
            } elseif ( is_string($found_url) && !empty($found_url) ) {
                $all_urls[] = $found_url;
            }

            if ( !empty($all_urls) ) {
                // LOGIC: First Upload Field = Profile Photo
                if ( $upload_count === 1 ) {
                    // Save ONLY the first image found
                    update_user_meta( $user_id, 'profile_photo', $all_urls[0] );
                }
                // LOGIC: Subsequent Fields = Gallery
                else {
                    update_user_meta( $user_id, 'portfolio_gallery', implode(',', $all_urls) );
                }
            }
        }

        // 2. TEXT DATA MAPPING (Same as before)
        $map = [
            'text-1'=>'mobile_number', 'text-2'=>'current_city', 
            'text-3'=>'weight', 'text-4'=>'height', 
            'text-5'=>'chest', 'text-6'=>'bust', 
            'text-7'=>'waist', 'text-8'=>'hips', 
            'text-9'=>'eye_color', 'text-10'=>'shoe_size', 
            'date-1'=>'birth_date', 'select-1'=>'gender', 'textarea-1'=>'description'
        ];
        if (isset($map[$field_id]) && !empty($data['value'])) {
             update_user_meta($user_id, $map[$field_id], sanitize_text_field($data['value']));
        }
    }
}
