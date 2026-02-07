<?php

/**
 * JavaScript Autofill Engine (FINAL FIX)
 */
add_action('wp_footer', function () {
?>
<script>
jQuery(document).ready(function($){

    function kastAutofill(){

        if(typeof window.KAST_PROFILE_DATA === 'undefined') return;

        $.each(window.KAST_PROFILE_DATA, function(field, val){

            if(!val) return;

            let input = $('[name="'+field+'"]');

            if(input.length){
                input.val(val).trigger('change').trigger('keyup');
            }

        });

    }

    kastAutofill();
    setTimeout(kastAutofill, 1000);
    setTimeout(kastAutofill, 3000);
    setTimeout(kastAutofill, 5000);

});
</script>
<?php
});
