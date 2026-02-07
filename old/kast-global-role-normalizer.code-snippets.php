<?php

/**
 * KAST Global Role Normalizer
 */
function kast_get_normalized_user_role($uid = 0) {

    if (!$uid) $uid = get_current_user_id();
    if (!$uid) return false;

    $user = get_userdata($uid);
    if (!$user) return false;

    $roles = array_map('strtolower', (array) $user->roles);

    return [
        'id'        => $uid,
        'is_agency' => in_array('agency', $roles),
        'is_artist' => in_array('artist', $roles)
    ];
}
