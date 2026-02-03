<?php

/**
 * Fix Audition Form UI
 */
add_action('wp_footer', function() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        
        function fixAuditionForm() {
            // --- 1. Identify Fields ---
            
            // USER FIELDS (Jo User bharega)
            var realTitle   = $('input[name="text-6"]');      // Project Title
            var realDetails = $('textarea[name="textarea-1"]'); // Audition Details
            
            // HIDDEN FIELDS (Jo hum auto-fill karenge)
            var hiddenTitle   = $('input[name="postdata-1-post-title"]');
            var hiddenContent = $('textarea[name="postdata-1-post-content"]');
            var hiddenCat     = $('select[name="postdata-1-category"]');

            // --- 2. Hide Duplicates (CSS Hide) ---
            if(hiddenTitle.length)   hiddenTitle.closest('.forminator-row').hide();
            if(hiddenContent.length) hiddenContent.closest('.forminator-row').hide();
            if(hiddenCat.length)     hiddenCat.closest('.forminator-row').hide();

            // --- 3. Magic Auto-Fill ---
            
            // Copy Project Title -> Main Post Title
            realTitle.on('input change keyup blur', function() {
                hiddenTitle.val($(this).val()).trigger('change');
            });

            // Copy Audition Details -> Main Post Content
            realDetails.on('input change keyup blur', function() {
                hiddenContent.val($(this).val()).trigger('change');
            });
        }

        // Run multiple times to ensure it loads
        fixAuditionForm();
        setTimeout(fixAuditionForm, 1000);
        setTimeout(fixAuditionForm, 3000);
    });
    </script>
    
    <!-- CSS Fallback to hide ugly fields immediately -->
    <style>
        .forminator-row:has(input[name="postdata-1-post-title"]),
        .forminator-row:has(textarea[name="postdata-1-post-content"]),
        .forminator-row:has(select[name="postdata-1-category"]) {
            display: none !important;
        }
    </style>
    <?php
});
