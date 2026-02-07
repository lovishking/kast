<?php

/**
 * Artist Photo Delete Button
 */
add_shortcode('artist_delete_btn', 'kast_delete_post_shortcode');

function kast_delete_post_shortcode() {
    // 1. Check if user is logged in
    if (!is_user_logged_in()) {
        return '';
    }

    global $post;
    $current_user_id = get_current_user_id();

    // 2. Check if the current user is the owner of the photo
    if ($post->post_author != $current_user_id) {
        return ''; // Agar owner nahi hai, to button mat dikhao
    }

    // 3. Delete Logic (Jab button click hoga)
    if (isset($_POST['kast_delete_post']) && isset($_POST['post_id'])) {
        if ($_POST['post_id'] == $post->ID) {
            wp_trash_post($post->ID); // Post ko Trash mein daal do
            
            // Delete hone ke baad kahan bhejma hai? (Current Page Reload)
            echo "<script>window.location.reload();</script>"; 
            return;
        }
    }

    // 4. Button HTML Style (Trash Icon)
    ob_start();
    ?>
    <form method="post" onsubmit="return confirm('Are you sure you want to delete this photo?');">
        <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
        <button type="submit" name="kast_delete_post" style="
            background-color: #000000; 
            color: #ff4d4d; 
            border: 1px solid #ff4d4d; 
            padding: 5px 10px; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 12px;
            margin-top: 10px;">
            <i class="fa fa-trash"></i> DELETE
        </button>
    </form>
    <?php
    return ob_get_clean();
}
