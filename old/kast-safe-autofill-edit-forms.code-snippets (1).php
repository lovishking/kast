<?php

/**
 * kast safe autofill edit forms
 */
/**
 * KAST – Safe Autofill Injector for Forminator Edit Forms
 * Artist Edit Form ID: 3656
 * Agency Edit Form ID: 2993
 */

add_filter( 'forminator_render_field', 'kast_safe_autofill_edit_forms', 20, 3 );

function kast_safe_autofill_edit_forms( $html, $field, $form_id ) {

    // Sirf Edit Forms par hi chale
    if ( ! in_array( (int)$form_id, [3656, 2993] ) ) {
        return $html;
    }

    $user_id = get_current_user_id();
    if ( ! $user_id ) return $html;

    // FIELD → USER META MAP
    $map = [
        'text-1'     => 'mobile_number',
        'text-2'     => 'current_city',
        'text-3'     => 'weight',
        'text-4'     => 'height',
        'text-5'     => 'chest',
        'text-6'     => 'bust',
        'text-7'     => 'waist',
        'text-8'     => 'hips',
        'text-9'     => 'eye_color',
        'text-10'    => 'shoe_size',
        'date-1'     => 'birth_date',
        'select-1'   => 'gender',
        'textarea-1'=> 'description',
    ];

    $field_id = $field['id'];

    if ( ! isset( $map[ $field_id ] ) ) {
        return $html;
    }

    $value = get_user_meta( $user_id, $map[ $field_id ], true );
    if ( empty( $value ) ) return $html;

    // ---------- INPUT ----------
    if ( in_array( $field['type'], ['text','number','phone','email','date'] ) ) {

        $html = preg_replace(
            '/value="[^"]*"/',
            'value="' . esc_attr( $value ) . '"',
            $html
        );
    }

    // ---------- TEXTAREA ----------
    elseif ( $field['type'] === 'textarea' ) {

        $html = preg_replace(
            '/>(.*?)<\/textarea>/s',
            '>' . esc_textarea( $value ) . '</textarea>',
            $html
        );
    }

    // ---------- SELECT ----------
    elseif ( $field['type'] === 'select' ) {

        $html = preg_replace_callback(
            '/<option([^>]*)value="([^"]*)"([^>]*)>/',
            function( $matches ) use ( $value ) {

                if ( trim($matches[2]) == trim($value) ) {
                    return '<option'.$matches[1].'value="'.$matches[2].'" selected="selected"'.$matches[3].'>';
                }

                return $matches[0];
            },
            $html
        );
    }

    return $html;
}
