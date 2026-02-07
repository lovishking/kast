<?php

/**
 * Agency Dashboard
 */
/**
 * KAST FINAL AGENCY DASHBOARD – MASTER SYSTEM
 * PURE CUSTOM | NO WPUF | NO BETTER MESSAGES
 * Depends on:
 *  - audition post type
 *  - _kast_applicants meta
 *  - KAST Chat Plugin
 *
 * Shortcode: [kast_agency_dashboard]
 */

/* ---------- SHORTCODE UI ---------- */
add_shortcode('kast_agency_dashboard','kast_render_agency_dashboard');
function kast_render_agency_dashboard(){
    if(!is_user_logged_in()) return '<p>Please login.</p>';
    ob_start(); ?>

<div id="kast-agency-dashboard" class="kast-dashboard-grid">
  <div class="kast-col kast-auditions"><h3>Auditions</h3><ul id="kast-audition-list"></ul></div>
  <div class="kast-col kast-applied"><h3>Applied</h3><ul id="kast-applied-list"></ul></div>
  <div class="kast-col kast-shortlisted"><h3>Shortlisted</h3><ul id="kast-shortlisted-list"></ul></div>
  <div class="kast-col kast-selected"><h3>Selected</h3><ul id="kast-selected-list"></ul></div>
</div>

<div id="kast-artist-modal" style="display:none"><div class="kast-modal-inner"></div></div>

<script>
jQuery(function($){

function loadAuditions(){
  $.post(kastAjax.ajaxurl,{action:'kast_load_auditions'},function(r){$('#kast-audition-list').html(r);});
}
loadAuditions();

$(document).on('click','.kast-audition',function(){
  $('.kast-audition').removeClass('active');
  $(this).addClass('active');
  let aid=$(this).data('id');
  $.post(kastAjax.ajaxurl,{action:'kast_load_applicants',aid:aid},function(r){
    $('#kast-applied-list').html(r.applied);
    $('#kast-shortlisted-list').html(r.shortlisted);
    $('#kast-selected-list').html(r.selected);
  },'json');
});

$(document).on('click','.kast-artist',function(){
  let uid=$(this).data('id');
  let aid=$('.kast-audition.active').data('id');
  $.post(kastAjax.ajaxurl,{action:'kast_artist_profile',uid:uid,aid:aid},function(r){
    $('.kast-modal-inner').html(r);
    $('#kast-artist-modal').fadeIn();
  });
});

$(document).on('click','#kast-artist-modal',function(e){ if(e.target.id==='kast-artist-modal') $(this).fadeOut(); });

$(document).on('click','.kast-status-btn',function(){
  let uid=$(this).data('uid'), aid=$(this).data('aid'), status=$(this).data('status');
  $.post(kastAjax.ajaxurl,{action:'kast_update_status',uid:uid,aid:aid,status:status},function(){
    $('.kast-audition.active').click();
  });
});

});
</script>
<?php return ob_get_clean(); }

/* ---------- AJAX CORE ---------- */
add_action('wp_enqueue_scripts',function(){
  wp_enqueue_script('jquery');
  wp_add_inline_script('jquery','var kastAjax={ajaxurl:"'.admin_url('admin-ajax.php').'"};','before');
});

add_action('wp_ajax_kast_load_auditions','kast_load_auditions');
function kast_load_auditions(){
  $uid=get_current_user_id();
  $q=new WP_Query(['post_type'=>'audition','author'=>$uid,'posts_per_page'=>-1]);
  while($q->have_posts()):$q->the_post();
    echo '<li class="kast-audition" data-id="'.get_the_ID().'">'.get_the_title().'</li>';
  endwhile; wp_reset_postdata(); die;
}

add_action('wp_ajax_kast_load_applicants','kast_load_applicants');
function kast_load_applicants(){
  $aid=intval($_POST['aid']);
  $applied=get_post_meta($aid,'_kast_applicants',true) ?: [];
  $short=get_post_meta($aid,'_kast_shortlisted',true) ?: [];
  $selected=get_post_meta($aid,'_kast_selected',true) ?: [];

  wp_send_json([
    'applied'=>kast_render_users($applied),
    'shortlisted'=>kast_render_users($short),
    'selected'=>kast_render_users($selected)
  ]);
}

function kast_render_users($arr){
  if(!is_array($arr)) return '';
  $out='';
  foreach($arr as $uid){
    $u=get_userdata($uid);
    $out.='<li class="kast-artist" data-id="'.$uid.'">'.$u->display_name.' | '.get_user_meta($uid,'age',true).' | '.get_user_meta($uid,'current_city',true).'</li>';
  }
  return $out;
}

add_action('wp_ajax_kast_artist_profile','kast_artist_profile');
function kast_artist_profile(){
  $uid=intval($_POST['uid']); $aid=intval($_POST['aid']);
  echo '<h3>'.get_userdata($uid)->display_name.'</h3>';
  echo '<p>City: '.get_user_meta($uid,'current_city',true).'</p>';
  echo '<button class="kast-status-btn" data-uid="'.$uid.'" data-aid="'.$aid.'" data-status="shortlisted">Shortlist</button>';
  echo '<button class="kast-status-btn" data-uid="'.$uid.'" data-aid="'.$aid.'" data-status="selected">Select</button>';
  echo '<button onclick="KAST_Chat.open('.get_current_user_id().','.$uid.')">Message</button>';
  die;
}

add_action('wp_ajax_kast_update_status','kast_update_status');
function kast_update_status(){
  $aid=intval($_POST['aid']); $uid=intval($_POST['uid']); $st=sanitize_text_field($_POST['status']);
  $app=get_post_meta($aid,'_kast_applicants',true) ?: [];
  $short=get_post_meta($aid,'_kast_shortlisted',true) ?: [];
  $sel=get_post_meta($aid,'_kast_selected',true) ?: [];

  $app=array_diff($app,[$uid]);
  $short=array_diff($short,[$uid]);
  $sel=array_diff($sel,[$uid]);

  if($st=='shortlisted') $short[]=$uid;
  if($st=='selected') $sel[]=$uid;

  update_post_meta($aid,'_kast_applicants',$app);
  update_post_meta($aid,'_kast_shortlisted',$short);
  update_post_meta($aid,'_kast_selected',$sel);
  die;
}
