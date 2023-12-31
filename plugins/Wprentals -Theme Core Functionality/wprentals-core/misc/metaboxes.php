<?php

if( !function_exists('wpestate_dropdowns_theme_admin') ):
            function wpestate_dropdowns_theme_admin_option($post_id,$array_values,$option_name,$pre=''){
                $dropdown_return    =   '';
                $option_value       =   esc_html ( get_post_meta($post_id, $option_name, true) );
                foreach($array_values as $value){
                    $dropdown_return.='<option value="'.$value.'"';
                      if ( $option_value == $value ){
                        $dropdown_return.='selected="selected"';
                    }
                    $dropdown_return.='>'.$pre.$value.'</option>';
                }

                return $dropdown_return;

            }
endif;
add_action('add_meta_boxes', 'estate_sidebar_meta');
add_action('save_post', 'estate_save_postdata', 1, 2);
add_action( 'edit_comment', 'extend_comment_edit_metafields' );

if( !function_exists('estate_sidebar_meta') ):
    function estate_sidebar_meta() {
        global $post;
        add_meta_box('wpestate-sidebar-post',       esc_html__( 'Sidebar Settings',  'wprentals-core'), 'estate_sidebar_box', 'post');
        add_meta_box('wpestate-sidebar-page',       esc_html__( 'Sidebar Settings',  'wprentals-core'), 'estate_sidebar_box', 'page');
        add_meta_box('wpestate-sidebar-property',   esc_html__( 'Sidebar Settings',  'wprentals-core'), 'estate_sidebar_box', 'estate_property');
        add_meta_box('wpestate-sidebar-agent',      esc_html__( 'Sidebar Settings',  'wprentals-core'), 'estate_sidebar_box', 'estate_agent');
        add_meta_box('wpestate-settings-post',      esc_html__( 'Post Settings',     'wprentals-core'), 'estate_post_options_box', 'post', 'normal', 'default' );
        add_meta_box('wpestate-settings-page',      esc_html__( 'Page Settings',     'wprentals-core'), 'estate_page_options_box', 'page', 'normal', 'default' );
        if(isset($post->ID)){
            if(  basename(get_page_template($post->ID))== 'property_list.php' || basename(get_page_template($post->ID))== 'property_list_half.php' ){
                add_meta_box('wpestate-pro_list_adv',       esc_html__( 'Property List Advanced Options','wprentals-core'), 'estate_prop_advanced_function', 'page', 'normal', 'low');
            }
        }
        add_meta_box('wpestate-header',             esc_html__( 'Appearance Options','wprentals-core'), 'estate_header_function', 'page', 'normal', 'low');
        add_meta_box('wpestate-header',             esc_html__( 'Appearance Options','wprentals-core'), 'estate_header_function', 'post', 'normal', 'low');
        add_meta_box('wpestate-header',             esc_html__( 'Appearance Options','wprentals-core'), 'estate_header_function', 'estate_agent', 'normal', 'low');
        //add_meta_box('wpestate-header',             esc_html__( 'Header Options','wprentals-core'), 'estate_header_function', 'estate_property', 'normal', 'low');
        add_meta_box('wpestate-header', esc_html__('Stars','wprentals-core'), 'estate_comment_starts', 'comment', 'normal');
    }
endif; // end   estate_sidebar_meta





///////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Header Option
///////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !function_exists('estate_header_function') ):
    function estate_header_function(){
        global $post;
        $header_array   =   array(
                                'global',
                                'none',
                                'image',
                                'theme slider',
                                'revolution slider',
                                'google map',
                                'video header'
                                );

        $header_type    =   get_post_meta ( $post->ID, 'header_type', true);
        $header_select  =   '';

        foreach($header_array as $key=>$value){
           $header_select.='<option value="'.$key.'" ';
           if($key==$header_type){
               $header_select.=' selected="selected" ';
           }
           $header_select.='>'.$value.'</option>';
        }


        $cache_array        = array('global','no','yes');
        $transparent_symbol    = '';
        $transparent_status    = esc_html ( get_post_meta($post->ID, 'transparent_status', true) );

        foreach($cache_array as $value){
                $transparent_symbol.='<option value="'.$value.'"';
                if ($transparent_status==$value){
                        $transparent_symbol.=' selected="selected" ';
                }
                $transparent_symbol.='>'.$value.'</option>';
        }


        print'
            <h3 class="pblankh">'.__('Use transparent header','wprentals-core').'</h3>
            <select name="transparent_status">
                '.$transparent_symbol.'
            </select>';

        print '
            <h3 class = "pblankh">'.__('Select header type','wprentals-core').'</h3>
            <select id = "page_header_type" name = "header_type">
            '.$header_select.'
            </select>';



    estate_page_map_box($post);
    estate_page_slider_box($post);
    estate_page_video_box($post);
    estate_page_theme_slider($post);
    }
endif;

///////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Property Listing advanced options
///////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('estate_prop_advanced_function') ):
function estate_prop_advanced_function(){
    global $post;

    if( basename(get_page_template($post->ID))!= 'property_list.php' && basename(get_page_template($post->ID))!= 'property_list_half.php' ){
        esc_html_e('Only for "Properties List" page template ! ','wprentals-core');
        return;
    }

    $args = array(
        'hide_empty'    => false
    );

    $actions_select     =   '';
    $categ_select       =   '';
    $taxonomy           =   'property_action_category';
    $tax_terms          =   get_terms($taxonomy,$args);

    $current_adv_filter_search_action = get_post_meta ( $post->ID, 'adv_filter_search_action', true);
    if($current_adv_filter_search_action==''){
        $current_adv_filter_search_action=array();
    }


    $all_selected='';
    if(!empty($current_adv_filter_search_action) && $current_adv_filter_search_action[0]=='all'){
      $all_selected=' selected="selected" ';
    }

    $actions_select.='<option value="all" '.$all_selected.'>'.esc_html__( 'all','wprentals-core').'</option>';
    if( !empty( $tax_terms ) ){
        foreach ($tax_terms as $tax_term) {
            $actions_select.='<option value="'.$tax_term->name.'" ';
            if( in_array  ( $tax_term->name,$current_adv_filter_search_action) ){
              $actions_select.=' selected="selected" ';
            }
            $actions_select.=' />'.$tax_term->name.'</option>';
        }
    }



    //////////////////////////////////////////////////////////////////////////////////////////
    $taxonomy           =   'property_category';
    $tax_terms          =   get_terms($taxonomy,$args);

    $current_adv_filter_search_category = get_post_meta ( $post->ID, 'adv_filter_search_category', true);
    if($current_adv_filter_search_category==''){
        $current_adv_filter_search_category=array();
    }

    $all_selected='';
    if( !empty($current_adv_filter_search_category) && $current_adv_filter_search_category[0]=='all'){
      $all_selected=' selected="selected" ';
    }

    $categ_select.='<option value="all" '.$all_selected.'>'.esc_html__( 'all','wprentals-core').'</option>';
    if( !empty( $tax_terms ) ){
        foreach ($tax_terms as $tax_term) {
            $categ_select.='<option value="'.$tax_term->name.'" ';
            if( in_array  ( $tax_term->name, $current_adv_filter_search_category) ){
              $categ_select.=' selected="selected" ';
            }
            $categ_select.=' />'.$tax_term->name.'</option>';
        }
    }


 //////////////////////////////////////////////////////////////////////////////////////////

    $select_city='';
    $taxonomy = 'property_city';
    $tax_terms_city = get_terms($taxonomy,$args);
    $current_adv_filter_city = get_post_meta ( $post->ID, 'current_adv_filter_city', true);

    if($current_adv_filter_city==''){
        $current_adv_filter_city=array();
    }

    $all_selected='';
    if( !empty($current_adv_filter_city) && $current_adv_filter_city[0]=='all'){
      $all_selected=' selected="selected" ';
    }

    $select_city.='<option value="all" '.$all_selected.' >'.esc_html__( 'all','wprentals-core').'</option>';
    foreach ($tax_terms_city as $tax_term) {

        $select_city.= '<option value="' . $tax_term->name . '" ';
        if( in_array  ( $tax_term->name, $current_adv_filter_city) ){
              $select_city.=' selected="selected" ';
        }
        $select_city.= '>' . $tax_term->name . '</option>';
    }


 //////////////////////////////////////////////////////////////////////////////////////////

    $select_area='';
    $taxonomy = 'property_area';
    $tax_terms_area = get_terms($taxonomy,$args);
    $current_adv_filter_area = get_post_meta ( $post->ID, 'current_adv_filter_area', true);
    if($current_adv_filter_area==''){
        $current_adv_filter_area=array();
    }

    $all_selected='';
    if(!empty($current_adv_filter_area) && $current_adv_filter_area[0]=='all'){
      $all_selected=' selected="selected" ';
    }

    $select_area.='<option value="all" '.$all_selected.'>'.esc_html__( 'all','wprentals-core').'</option>';
    foreach ($tax_terms_area as $tax_term) {
        $term_meta=  get_option( "taxonomy_$tax_term->term_id");
        $select_area.= '<option value="' . $tax_term->name . '" ';
        if( in_array  ( $tax_term->name, $current_adv_filter_area) ){
              $select_area.=' selected="selected" ';
        }
        $select_area.= '>' . $tax_term->name . '</option>';
    }

//////////////////////////////////



    $show_filter_area_select='';
    $cache_array=array('yes','no');
    $show_filter_area  =   get_post_meta($post->ID, 'show_filter_area', true);

    foreach($cache_array as $value){
         $show_filter_area_select.='<option value="'.$value.'"';
         if ( $show_filter_area == $value ){
                 $show_filter_area_select.=' selected="selected" ';
         }
         $show_filter_area_select.='>'.$value.'</option>';
    }






    $show_featured_only_select='';
    $show_featured_only  =   get_post_meta($post->ID, 'show_featured_only', true);
    foreach($cache_array as $value){

         $show_featured_only_select.='<option value="'.$value.'" ';
         if ( $show_featured_only == $value ){
                 $show_featured_only_select.=' selected="selected" ';
         }
         $show_featured_only_select.='>'.$value.'</option>';
    }

    $listing_filter = get_post_meta($post->ID, 'listing_filter',true );

    $listing_filter_array=array(
                            "1"=>"Price High to Low",
                            "2"=>"Price Low to High",
                            "0"=>"default"
                            );


 print '
     *press CTRL for multiple selection
     <table>
     <tr>
    <td width="33%" valign="top" align="left">
        <p class="meta-options">
            <label   for="filter_search_action[]">Pick actions</label> </br>
            <select  name="adv_filter_search_action[]"  multiple="multiple" style="width:250px;" >
            '.$actions_select.'
             </select>
        </p>
    </td>

    <td width="33%" valign="top" align="left">
        <p class="meta-options">
           <label for="adv_filter_search_category[]">Pick category</label> </br>
           <select  name="adv_filter_search_category[]"  multiple="multiple" style="width:250px;" >
           '.$categ_select.'
           </select>
        </p>
    </td>

    </tr>


    <tr>
        <td width="33%" valign="top" align="left">
            <p class="meta-options">
                <label for="current_adv_filter_city[]">Pick City</label> </br>
                <select  name="current_adv_filter_city[]"  multiple="multiple" style="width:250px;" >
                '.$select_city.'
                </select>
            </p>
        </td>
        <td width="33%" valign="top" align="left">
            <p class="meta-options">
               <label for="current_adv_filter_area[]">Pick Area</label> </br>
                <select  name="current_adv_filter_area[]"  multiple="multiple" style="width:250px;" >
                '.$select_area.'
                </select>
            </p>
        </td>

    </tr>

    <tr>
        <td width="33%" valign="top" align="left">
            <p class="meta-options">
               <label for="listing_filter_div">Default sort ?</label><br />
               <select id="listing_filter_div" name="listing_filter"  style="width:250px;">';
               foreach($listing_filter_array as $key=>$value){
                  print '<option  value="'.$key.'" ';
                      if($key==$listing_filter){
                          print ' selected="selected" ';
                      }
                  print '>'.$value.'</option>';
               }
               print '
               </select>
            </p>
        </td>
        <td width="33%" valign="top" align="left">
            <p class="meta-options">
               <label for="show_featured_only">Show featured only </label><br />
                <select id="show_featured_only"  name="show_featured_only" style="width:250px;" >
                ' .$show_featured_only_select . '
                </select>
            </p>
        </td>

    </tr>

    <tr>
        <td width="33%" valign="top" align="left">
            <p class="meta-options">
                <label for="show_filter_area">Show filter area</label><br />
              <select id="show_filter_area"  name="show_filter_area" style="width:250px;" >
              ' .$show_filter_area_select . '
              </select>
            </p>
        </td>
        <td width="33%" valign="top" align="left">
            <p class="meta-options">


            </p>
        </td>

    </tr>



     </table>





<style media="screen" type="text/css">

.adv_prop_container{
float:left;
width:22%;
margin-right:10px;
}

</style>
';
}

endif;
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Listing options
///////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !function_exists('estate_listing_options') ):
    function estate_listing_options(){


        global $post;
        if ( 'property_list.php'== basename( get_page_template() )){

            $listing_action  =   get_post_meta($post->ID, 'listing_action', true);
            $listing_categ   =   get_post_meta($post->ID, 'listing_categ', true);
            $listing_city    =   get_post_meta($post->ID, 'listing_city', true);
            $listing_area    =   get_post_meta($post->ID, 'listing_area', true);

            $args = array(
            'hide_empty'    => false
            );

            $taxonomy = 'property_action_category';
            $tax_terms = get_terms($taxonomy,$args);

            $taxonomy_categ = 'property_category';
            $tax_terms_categ = get_terms($taxonomy_categ,$args);

            $actions_select     =   '';
            $categ_select       =   '';


            ///////////////////////// actions
            if( !empty( $tax_terms ) ){
                foreach ($tax_terms as $tax_term) {
                  $actions_select.='<option value="'.$tax_term->name.'" ';
                  if ($tax_term->name == $listing_action ){
                       $actions_select.=' selected="selected" ';
                  }
                  $actions_select.=' >'.$tax_term->name.'</option>';
                }
            }


            /////////////////////////categ

            if( !empty( $tax_terms_categ ) ){
                foreach ($tax_terms_categ as $categ) {
                  $categ_select.='<option value="'.$categ->name.'" ';
                   if ($categ->name == $listing_categ ){
                       $categ_select.=' selected="selected" ';
                  }
                  $categ_select.='>'.$categ->name.'</option>';
                }
            }


            ///////////////////////// city
            $select_city='';
            $taxonomy = 'property_city';
            $tax_terms = get_terms($taxonomy,$args);
            foreach ($tax_terms as $tax_term) {
               $select_city.= '<option value="' . $tax_term->name . '" ';
               if ( $tax_term->name  == $listing_city ){
                       $select_city.=' selected="selected" ';
                  }
               $select_city.='>' . $tax_term->name . '</option>';
            }

            if ($select_city==''){
                  $select_city.= '<option value="">No Cities</option>';
            }



            /////////////////////////area
            $select_area='';
            $taxonomy = 'property_area';
            $tax_terms = get_terms($taxonomy,$args);

            foreach ($tax_terms as $tax_term) {
                $term_meta=  get_option( "taxonomy_$tax_term->term_id");
                $select_area.= '<option value="' . $tax_term->name . '" data-parentcity="' . $term_meta['cityparent'] . '" ';

                 if ( $tax_term->name  == $listing_area ){
                       $select_area.=' selected="selected" ';
                  }

                $select_area.= '>' . $tax_term->name . '</option>';

             }





          print '
          <p class="meta-options">
            <label for="listing_action">'.esc_html__( 'Action category','wprentals-core').'</label><br />
              <select  name="listing_action" >
                         <option value="all">'.wpestate_category_labels_dropdowns('second').'</option>
                         '.$actions_select.'
                    </select>
          </p>


        <p class="meta-options">
        <label for="listing_categ">'.esc_html__( 'Pick Category','wprentals-core').'</label><br />
         <select name="listing_categ"  >
                        <option value="all">'.wpestate_category_labels_dropdowns('main').'</option>
                        '. $categ_select.'
                    </select>
        </p>

        <p class="meta-options">
          <label for="listing_city">'.esc_html__( 'Pick City','wprentals-core').'</label><br />
          <select  name="listing_city"  >
                <option value="all">'.esc_html__( 'All Cities','wprentals-core').'</option>
                '. $select_city.'
           </select>
        </p>

        <p class="meta-options">
            <label for="listing_area">'.esc_html__( 'Pick Area','wprentals-core').'</label><br />
            <select  name="listing_area">
                <option data-parentcity="*" value="all">'.esc_html__( 'All Areas','wprentals-core').'</option>
                '.$select_area.'
            </select>
        </p>
         ';


        }else{
            print esc_html_e('These Options are available for "Property list" page template only!','wprentals-core');
        }

    }
endif; // end   estate_listing_options





////////////////////////////////////////////////////////////////////////////////////////////////
// Manage Revolution Slider
////////////////////////////////////////////////////////////////////////////////////////////////

if( !function_exists('estate_page_slider_box') ):
    function estate_page_slider_box($post) {
        global $post;
        $rev_slider           = get_post_meta($post->ID, 'rev_slider', true);
        print '
        <div class="header_admin_options revolution_slider">
            <p class="meta-options pblank">
                <h3 class="pblankh">'.esc_html__( 'Options for Revolution Slider (if Header Type "revolution slider" is selected)','wprentals-core').'</h3>
            </p>
            <p class="meta-options">
                <label for="page_custom_lat">'.esc_html__( 'Revolution Slider Name','wprentals-core').'</label><br />
                <input type="text" id="rev_slider" name="rev_slider" size="40" value="'.$rev_slider.'">
            </p>
            </div>
        ';
    }
endif; // end   estate_page_slider_box


////////////////////////////////////////////////////////////////////////////////////////////////
// Manage Google Maps
////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('estate_page_map_box') ):
function estate_page_map_box($post) {
    global $post;
    $page_lat           = get_post_meta($post->ID, 'page_custom_lat', true);
    $page_long          = get_post_meta($post->ID, 'page_custom_long', true);
    $page_custom_image  = get_post_meta($post->ID, 'page_custom_image', true);
    $page_custom_zoom   = get_post_meta($post->ID, 'page_custom_zoom', true);
    $min_height         = intval( esc_html(get_post_meta($post->ID, 'min_height', true)) );
    $max_height         = intval( esc_html(get_post_meta($post->ID, 'max_height', true)) );
    $cache_array        = array('yes','no');
    $keep_min_symbol    = '';
    $keep_min_status    = esc_html ( get_post_meta($post->ID, 'keep_min', true) );

    foreach($cache_array as $value){
            $keep_min_symbol.='<option value="'.$value.'"';
            if ($keep_min_status==$value){
                    $keep_min_symbol.=' selected="selected" ';
            }
            $keep_min_symbol.='>'.$value.'</option>';
    }

    if ($page_custom_zoom==''){
        $page_custom_zoom=15;
    }
    print ' <div class="header_admin_options google_map">
        <p class="meta-options pblank">
        <h3 class="pblankh">'.esc_html__( 'Options for Google Maps (if Header Type "google map" is selected)','wprentals-core').'</h3>
        </p>';

    if( get_post_type($post->ID)!="estate_property" ){
        print '

        <p class="meta-options pblank">
        '.esc_html__( '  Leave these blank in order to get the general map settings.','wprentals-core').'
        </p>
        <p class="meta-options third-meta-options">
        <label for="page_custom_lat">'.esc_html__( 'Map - Center point  Latitudine: ','wprentals-core').'</label><br />
        <input type="text" id="page_custom_lat" name="page_custom_lat" size="36" value="'.$page_lat.'">
        </p>

        <p class="meta-options third-meta-options">
        <label for="page_custom_long">'.esc_html__( 'Map - Center point  Longitudine: ','wprentals-core').'</label><br />
        <input type="text" id="page_custom_long" name="page_custom_long" size="36" value="'.$page_long.'">
        </p>



        <p class="meta-options third-meta-options">
        <label for="page_custom_zoom">'.esc_html__( 'Zoom Level for map (1-20)','wprentals-core').'</label><br />
        <select name="page_custom_zoom" id="page_custom_zoom">';

        for ($i=1;$i<21;$i++){
            print '<option value="'.$i.'"';
            if($page_custom_zoom==$i){
                print ' selected="selected" ';
            }
            print '>'.$i.'</option>';
        }
        print'
        </select>
    </p>';
    }

    print'
    <p class="meta-options third-meta-options">
     <label for="min_height">'.esc_html__( 'Height of the map when closed','wprentals-core').'</label><br />
      <input id="min_height" type="text" size="36" name="min_height" value="'.$min_height.'" />
    </p>

    <p class="meta-options third-meta-options">
       <label for="max_height">'.esc_html__( 'Height of map when open','wprentals-core').'</label><br />
       <input id="max_height" type="text" size="36" name="max_height" value="'.$max_height.'" />
    </p>

    <p class="meta-options third-meta-options">
       <label for="keep_min">'.esc_html__( 'Force map at the "closed" size ? ','wprentals-core').'</label><br />
       <select id="keep_min" name="keep_min">
       <option value=""></option>
          '.$keep_min_symbol.'
       </select>
    </p>


    <p class="meta-options">
        <label for="bypass_fit_bounds">'.esc_html__( 'ByPass fit bounds (auto zoom and pan of the map around visible markers) ','wprentals-core').'</label><br />
        <input type="hidden" value="0" name="bypass_fit_bounds" />
        <input type="checkbox" value="1" name="bypass_fit_bounds" id="bypass_fit_bounds"  ';
        if( get_post_meta($post->ID,'bypass_fit_bounds',true)==1){
            print ' checked="checked" ';
        }
    print '/>
     </p></div>';




    $cache_array        =   array('yes','no');
    $cache_array_rev    =   array('no','yes');
    $cache_array_fix    =   array('cover','contain');
    $img_full_screen                    = wpestate_dropdowns_theme_admin_option($post->ID,$cache_array_rev,'page_header_image_full_screen');
    $img_full_back_type                 = wpestate_dropdowns_theme_admin_option($post->ID,$cache_array_fix,'page_header_image_back_type');
    $page_header_title_over_image       = stripslashes ( esc_html ( get_post_meta($post->ID, 'page_header_title_over_image', true) ) );
    $page_header_subtitle_over_image    = stripslashes ( esc_html ( get_post_meta($post->ID, 'page_header_subtitle_over_image', true) ) );
    $page_header_image_height           = esc_html ( get_post_meta($post->ID, 'page_header_image_height', true) );
    $page_header_overlay_val            = esc_html ( get_post_meta($post->ID, 'page_header_overlay_val', true) );
    $page_header_overlay_color          = esc_html ( get_post_meta($post->ID, 'page_header_overlay_color', true) );

print'<div class="header_admin_options image_header">
    <p class="meta-options pblank">
    <h3 class="pblankh">'.esc_html__( 'Options for Static Image  (if Header Type "image" is selected)','wprentals-core').'</h3>
    </p>

   <p class="meta-options">
        <label for="page_custom_image">'.esc_html__( 'Header Image','wprentals-core').'</label><br />
        <input id="page_custom_image" type="text" size="36" name="page_custom_image" value="'.$page_custom_image.'" />
	<input id="page_custom_image_button" type="button"   size="40" class="upload_button button" value="'.esc_html__( 'Upload Image','wprentals-core').'" />
    </p>

    <p class="meta-options third-meta-options">
        <label for="page_header_image_full_screen">'.__('Full Screen?','wprentals-core').'</label><br />
        <select id="page_header_image_full_screen" name="page_header_image_full_screen">
            '.$img_full_screen.'
        </select>
    </p>

    <p class="meta-options third-meta-options">
        <label for="page_header_image_back_type">'.__('Full Screen Background Type?','wprentals-core').'</label><br />
        <select id="page_header_image_back_type" name="page_header_image_back_type">
            '.$img_full_back_type.'
        </select>
    </p>

    <p class="meta-options third-meta-options">
        <label for="page_header_title_over_image">'.__('Title Over Image','wprentals-core').'</label><br />
        <input id="page_header_title_over_image" type="text" size="36" name="page_header_title_over_image" value="'.$page_header_title_over_image.'" />
    </p>

    <p class="meta-options third-meta-options">
        <label for="page_header_subtitle_over_image">'.__('SubTitle Over Image','wprentals-core').'</label><br />
        <input id="page_header_subtitle_over_image" type="text" size="36" name="page_header_subtitle_over_image" value="'.$page_header_subtitle_over_image.'" />
    </p>

    <p class="meta-options third-meta-options">
        <label for="page_header_image_height">'.__('Image Height(Ex:700, Default:580px)','wprentals-core').'</label><br />
        <input id="page_header_image_height" type="text" size="36" name="page_header_image_height" value="'.$page_header_image_height.'" />
    </p>

        <div class="meta-options third-meta-options">
            <label for="page_header_overlay_color">'.__('Overlay Color','wprentals-core').'</label><br />
            <div id="page_header_overlay_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$page_header_overlay_color.';"  ></div></div>  <input type="text" name="page_header_overlay_color" maxlength="7" class="inptxt " value="'.$page_header_overlay_color.'"/>
        </div>

        <p class="meta-options third-meta-options">
            <label for="page_header_overlay_val">'.__('Overlay Opacity(betwen 0 and 1 , Ex:0.5, default 0.6)','wprentals-core').'</label><br />
            <input id="page_header_overlay_val" type="text" size="36" name="page_header_overlay_val" value="'.$page_header_overlay_val.'" />
        </p>


        <p class="meta-options pblank">
        </p></div>';
}
endif; // end   estate_page_map_box



if( !function_exists('estate_page_video_box') ):
function estate_page_video_box($post) {
    global $post;
    //page_custom_video


    $cache_array                        =   array('yes','no');
    $cache_array_reverse                =   array('no','yes');
    $cache_array_fix                    =   array('screen','auto');
    $page_custom_video                  =   get_post_meta($post->ID, 'page_custom_video', true);
    $page_custom_video_webbm            =   get_post_meta($post->ID, 'page_custom_video_webbm', true);
    $page_custom_video_ogv              =   get_post_meta($post->ID, 'page_custom_video_ogv', true);
    $page_custom_video_cover_image      =   get_post_meta($post->ID, 'page_custom_video_cover_image', true);
    $img_full_screen                    =   wpestate_dropdowns_theme_admin_option($post->ID,$cache_array_reverse,'page_header_video_full_screen');
    $page_header_title_over_video       =   stripslashes ( esc_html ( get_post_meta($post->ID, 'page_header_title_over_video', true) ) );
    $page_header_subtitle_over_video    =   stripslashes ( esc_html ( get_post_meta($post->ID, 'page_header_subtitle_over_video', true) ) );
    $page_header_video_height           =   esc_html ( get_post_meta($post->ID, 'page_header_video_height', true) );
    $page_header_overlay_color_video    =   esc_html ( get_post_meta($post->ID, 'page_header_overlay_color_video', true) );
    $page_header_overlay_val_video      =   esc_html ( get_post_meta($post->ID, 'page_header_overlay_val_video', true) );

    print '
    <div class="header_admin_options video_header">
        <p class="meta-options pblank">
            <h3 class="pblankh">'.__('Options for Video Header','wprentals-core').'</h3>
        </p>



        <p class="meta-options ">
            <label for="page_custom_image">'.__('Video MP4 version','wprentals-core').'</label><br />
            <input id="page_custom_video" type="text" size="36" name="page_custom_video" value="'.$page_custom_video.'" />
            <input id="page_custom_video_button" type="button"   size="40" class="upload_button button" value="'.__('Upload Video','wprentals-core').'" />
        </p>

        <p class="meta-options ">
            <label for="page_custom_image">'.__('Video WEBM version','wprentals-core').'</label><br />
            <input id="page_custom_video_webbm" type="text" size="36" name="page_custom_video_webbm" value="'.$page_custom_video_webbm.'" />
            <input id="page_custom_video_webbm_button" type="button"   size="40" class="upload_button button" value="'.__('Upload Video','wprentals-core').'" />
        </p>

        <p class="meta-options ">
            <label for="page_custom_image">'.__('Video OGV version','wprentals-core').'</label><br />
            <input id="page_custom_video_ogv" type="text" size="36" name="page_custom_video_ogv" value="'.$page_custom_video_ogv.'" />
            <input id="page_custom_video_ogv_button" type="button"   size="40" class="upload_button button" value="'.__('Upload Video','wprentals-core').'" />
        </p>

        <p class="meta-options ">
            <label for="page_custom_video_cover_image">'.__('Cover Image','wprentals-core').'</label><br />
            <input id="page_custom_video_cover_image" type="text" size="36" name="page_custom_video_cover_image" value="'.$page_custom_video_cover_image.'" />
            <input id="page_custom_video_cover_image_button" type="button"   size="40" class="upload_button button" value="'.__('Upload Image','wprentals-core').'" />
        </p>

        <p class="meta-options third-meta-options">
            <label for="page_header_video_full_screen">'.__('Full Screen?','wprentals-core').'</label><br />
            <select id="page_header_video_full_screen" name="page_header_video_full_screen">
                '.$img_full_screen.'
            </select>
        </p>



        <p class="meta-options third-meta-options">
            <label for="page_header_title_over_video">'.__('Title Over Image','wprentals-core').'</label><br />
            <input id="page_header_title_over_video" type="text" size="36" name="page_header_title_over_video" value="'.$page_header_title_over_video.'" />
        </p>

        <p class="meta-options third-meta-options">
            <label for="page_header_subtitle_over_video">'.__('SubTitle Over Image','wprentals-core').'</label><br />
            <input id="page_header_subtitle_over_video" type="text" size="36" name="page_header_subtitle_over_video" value="'.$page_header_subtitle_over_video.'" />
        </p>

        <p class="meta-options third-meta-options">
            <label for="page_header_video_height">'.__('Video Height(Ex:700, Default:580px)','wprentals-core').'</label><br />
            <input id="page_header_video_height" type="text" size="36" name="page_header_video_height" value="'.$page_header_video_height.'" />
        </p>

        <div class="meta-options third-meta-options">
            <label for="page_header_overlay_color_video">'.__('Overlay Color','wprentals-core').'</label><br />

            <div id="page_header_overlay_color_video" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$page_header_overlay_color_video.';"  ></div></div>  <input type="text" name="page_header_overlay_color_video" maxlength="7" class="inptxt " value="'.$page_header_overlay_color_video.'"/>
        </div>

        <p class="meta-options third-meta-options">
            <label for="page_header_overlay_val_video">'.__('Overlay Opacity(betwen 0 and 1 , Ex:0.5, default 0.6)','wprentals-core').'</label><br />
            <input id="page_header_overlay_val_video" type="text" size="36" name="page_header_overlay_val_video" value="'.$page_header_overlay_val_video.'" />
        </p>


        <p class="meta-options pblank">
        </p></div>';

}
endif; // end   estate_page_slider_box



if( !function_exists('estate_page_theme_slider') ):
function estate_page_theme_slider($post) {
    return;
    global $post;
    $rev_slider           = get_post_meta($post->ID, 'rev_slider', true);
    print '
    <div class="header_admin_options theme_slider">
        <p class="meta-options pblank">
            <h3 class="pblankh">'.__('Options for Theme Slider','wprentals-core').'</h3>
        </p>
        <p class="meta-options">

        </p>
    </div>
    ';
}
endif; // end   estate_page_slider_box








////////////////////////////////////////////////////////////////////////////////////////////////
// Manage Custom Header of the page
////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('estate_page_map_box_agent') ):
    function estate_page_map_box_agent($post) {
        global $post;
        $page_lat           = get_post_meta($post->ID, 'page_custom_lat', true);
        $page_long          = get_post_meta($post->ID, 'page_custom_long', true);
        $page_custom_image  = get_post_meta($post->ID, 'page_custom_image', true);
        $page_custom_zoom  = get_post_meta($post->ID, 'page_custom_zoom', true);

        if ($page_custom_zoom==''){
            $page_custom_zoom=15;
        }

        print '

        <p class="meta-options">
            <label for="page_custom_image">'.esc_html__( 'Replace Map with this image','wprentals-core').'</label><br />
            <input id="page_custom_image" type="text" size="36" name="page_custom_image" value="'.$page_custom_image.'" />
            <input id="page_custom_image_button" type="button"   size="40" class="upload_button button" value="'.esc_html__( 'Upload Image','wprentals-core').'" />
         </p>

         <p class="meta-options">
           <label for="page_custom_zoom">'.esc_html__( 'Zoom Level for map (1-20)','wprentals-core').'</label><br />
           <select name="page_custom_zoom" id="page_custom_zoom">';

          for ($i=1;$i<21;$i++){
               print '<option value="'.$i.'"';
               if($page_custom_zoom==$i){
                   print ' selected="selected" ';
               }
               print '>'.$i.'</option>';
           }

         print'
           </select>
         <p>
        ';

    }
endif; // end   estate_page_map_box_agent



////////////////////////////////////////////////////////////////////////////////////////////////
// Manage page options
////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('estate_page_options_box') ):
    function estate_page_options_box($post) {
        global $post;

        $page_title = get_post_meta($post->ID, 'page_show_title', true);
        $selected_no = $selected_yes = '';

        if ($page_title == 'no') {
            $selected_no = 'selected="selected"';
        } else {
            $selected_yes = 'selected="selected"';
        }

        if ($page_title != '') {
            $page_title_select = '<option value="' . $page_title . '" selected="selected">' . $page_title . '</option>';
        }

        print '
        <p class="meta-options">
        <label for="page_show_title">'.esc_html__( 'Show Title: ','wprentals-core').'</label><br />
        <select id="page_show_title" name="page_show_title" style="width: 200px;">
                <option value="yes" ' . $selected_yes . '>yes</option>
                <option value="no" ' . $selected_no . '>no</option>
        </select></p>';

    }
endif; // end   estate_page_options_box


////////////////////////////////////////////////////////////////////////////////////////////////
// Manage post options
////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('estate_post_options_box') ):
    function estate_post_options_box($post) {
        wp_nonce_field(plugin_basename(__FILE__), 'estate_property_noncename');
        global $post;

        $option = '';
        $title_values = array('yes', 'no');
        $post_title = get_post_meta($post->ID, 'post_show_title', true);
        foreach ($title_values as $value) {
            $option.='<option value="' . $value . '"';
            if ($value == $post_title) {
                $option.='selected="selected"';
            }
            $option.='>' . $value . '</option>';
        }

        print   '<p class="meta-options">
                    <label for="post_show_title">'.esc_html__( 'Show Title:','wprentals-core').' </label><br />
                    <select id="post_show_title" name="post_show_title" style="width: 200px;">
                            ' . $option . '
                    </select><br />
                </p>';

        $option = '';
        $title_values = array( 'no','yes');
        $group_pictures = get_post_meta($post->ID, 'group_pictures', true);
        foreach ($title_values as $value) {
            $option.='<option value="' . $value . '"';
            if ($value == $group_pictures) {
                $option.='selected="selected"';
            }
            $option.='>' . $value . '</option>';
        }

        print'  <p class="meta-options">
                    <label for="group_pictures">'.esc_html__( 'Group pictures in slider?(*only for blog posts)','wprentals-core').' </label><br />
                    <select id="group_pictures" name="group_pictures" style="width: 200px;">
                            ' . $option . '
                    </select><br />
                </p>';

    }
endif; // end   estate_post_options_box





////////////////////////////////////////////////////////////////////////////////////////////////
// Manage Sidebars per posts/page
////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('estate_sidebar_box') ):
    function estate_sidebar_box($post) {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'wpestate_sidebar_noncename');
        global $post;
        global $wp_registered_sidebars ;
        $sidebar_name   = get_post_meta($post->ID, 'sidebar_select', true);
        $sidebar_option = get_post_meta($post->ID, 'sidebar_option', true);

        $sidebar_values = array(   0=>'right',
                                   1=>'left',
                                   2=>'none');

        $option         = '';

        foreach ($sidebar_values as $key=>$value) {
            $option.='<option value="' . $value . '"';
            if ($value == $sidebar_option) {
                $option.=' selected="selected"';
            }
            $option.='>' . $value . '</option>';
        }

        print '
        <p class="meta-options"><label for="sidebar_option">'.esc_html__( 'Where to show the sidebar: ','wprentals-core').' </label><br />
            <select id="sidebar_option" name="sidebar_option" style="width: 200px;">
            ' . $option . '
            </select>
        </p>';

        print'
        <p class="meta-options"><label for="sidebar_select">'.esc_html__( 'Select the sidebar: ','wprentals-core').'</label><br />
            <select name="sidebar_select" id="sidebar_select" style="width: 200px;">';
            foreach ($GLOBALS['wp_registered_sidebars'] as $sidebar) {
                print'<option value="' . ($sidebar['id'] ) . '"';
                if ($sidebar_name == $sidebar['id']) {
                    print' selected="selected"';
                }
                print' >' . ucwords($sidebar['name']) . '</option>';
            }
            print '
            </select>
        </p>';
    }
endif; // end   estate_sidebar_box





////////////////////////////////////////////////////////////////////////////////////////////////
// Saving of custom data
////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('estate_save_postdata') ):
function estate_save_postdata($post_id) {
    global $post;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    ///////////////////////////////////// Check permissions
    if(isset($_POST['post_type'])){
        if ('page' == $_POST['post_type'] or 'post' == $_POST['post_type'] or 'estate_property' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return;
        }
        else {
            if (!current_user_can('edit_post', $post_id))
                return;
        }
    }


    $allowed_keys=array(
      'embed_virtual_tour',
        'local_booking_type',
        'instant_booking',
        'smoking_allowed',
        'party_allowed',
        'pets_allowed',
        'other_rules',
        'children_allowed',
        'cancellation_policy',
        'property_custom_details_admin_value',
        'property_custom_details_admin_label',
        'page_header_image_back_type',
        'page_header_image_full_screen',
        'page_header_overlay_val_video',
        'page_header_overlay_color_video',
        'page_header_video_height',
        'page_header_subtitle_over_video',
        'page_header_title_over_video',
        'page_header_video_full_screen',
        'page_custom_video_cover_image',
        'page_custom_video_ogv',
        'page_custom_video_webbm',
        'page_custom_video',
        'page_header_overlay_val',
        'page_header_overlay_color',
        'page_header_image_height',
        'page_header_subtitle_over_image',
        'page_header_title_over_image',
        'biling_period',
        'sidebar_option',
        'sidebar_select',
        'post_show_title',
        'group_pictures',
        'sidebar_option',
        'sidebar_select',
        'adv_filter_search_action',
        'adv_filter_search_category',
        'current_adv_filter_city',
        'current_adv_filter_area',
        'listing_filter',
        'show_featured_only',
        'show_filter_area',
        'header_type',
        'transparent_status',
        'listing_action',
        'listing_categ',
        'listing_city',
        'listing_area',
        'rev_slider',
        'page_custom_lat',
        'page_custom_long',
        'page_custom_zoom',
        'min_height',
        'max_height',
        'keep_min',
        'page_custom_image',
        'page_show_title',
        'property_address',
        'property_county',
        'property_state',
        'property_zip',
        'property_country',
        'property_status',
        'prop_featured',
        'property_price',
        'cleaning_fee',
        'cleaning_fee_per_day',
        'city_fee',
        'city_fee_per_day',
        'price_per_weekeend',
        'min_days_booking',
        'property_price_per_week',
        'property_price_per_month',
        'extra_price_per_guest',
        'max_extra_guest_no',
        'overload_guest',
        'checkin_change_over',
        'checkin_checkout_change_over',
        'price_per_guest_from_one',
        'property_size',
        'property_rooms',
        'property_bedrooms',
        'property_bathrooms',
        'guest_no',
        'embed_video_type',
        'property_affiliate',
        'virtual_tour',
        'private_notes',
        'embed_video_id',
        'property_latitude',
        'property_longitude',
        'google_camera_angle',
        'page_custom_zoom',
        'property_agent',
        'agent_email',
        'agent_phone',
        'agent_mobile',
        'agent_skype',
        'agent_facebook',
        'agent_twitter',
        'agent_linkedin',
        'agent_pinterest',
        'live_in',
        'i_speak',
        'payment_info',
        'user_agent_id',
        'booking_from_date',
        'booking_to_date',
        'booking_id',
        'booking_guests',
        'booking_listing_name',
        'booking_status',
        'billing_freq',
        'pack_listings',
        'mem_list_unl',
        'pack_featured_listings',
        'pack_price',
        'pack_visible',
        'pack_stripe_id',
        'message_from_user',
        'message_to_user',
        'message_status',
        'delete_source',
        'delete_destination',
        'bypass_fit_bounds',
        'property_price_before_label',
        'property_price_after_label',
        'property_taxes',
        'security_deposit',
        'early_bird_percent',
        'early_bird_days',
        'cleaning_fee_per_day',
        'city_fee_per_day',
        'city_fee_percent',
        'min_days_booking',
        'image_to_attach'
        );



    $custom_fields = wprentals_get_option('wpestate_custom_fields_list','');
    if( !empty($custom_fields)){
        $i=0;
        while($i< count($custom_fields) ){
            $name =   $custom_fields[$i][0];
            $slug           =     wpestate_limit45(sanitize_title( $name ));
            $slug           =     sanitize_key($slug);
            $allowed_keys[] =     $slug;
            $i++;
       }
    }



    foreach ($_POST as $key => $value) {
        if( !is_array ($value) ){
            if (in_array ($key, $allowed_keys)) {
                $postmeta = wp_filter_kses( $value );

                if( $key == 'property_price' ||$key == 'property_price_per_week' ||$key == 'property_price_per_month'  ){
                    update_post_meta($post_id, sanitize_key($key),intval ($postmeta) );
                }else if( $key == 'property_affiliate'){
                    update_post_meta($post_id,'property_affiliate',esc_url($_POST['property_affiliate']) );
                }else if( $key == 'virtual_tour'){
                    $iframe = array( 'iframe' => array(
                            'src' => array (),
                            'width' => array (),
                            'height' => array (),
                            'frameborder' => array(),
                            'style' => array(),
                            'allowFullScreen' => array() // add any other attributes you wish to allow
                        ) );

                    $virtual_tour   =   wp_kses (trim($_POST['virtual_tour']),$iframe);
                    update_post_meta($post_id, 'virtual_tour',$virtual_tour );
                }else{
                    update_post_meta($post_id, sanitize_key($key), $postmeta );
                }
            }
        }
    }






    //////////////////////////////////////////////////////////////////
    //// change listing owner
    //////////////////////////////////////////////////////////////////

    if ( isset($_POST['property_agent'])){
        remove_action('save_post', 'estate_save_postdata',1);
        $new_user_as_agent  =   intval($_POST['property_agent']);
        //$new_user_id        =   intval ( get_post_meta($new_user_as_agent, 'user_agent_id', true) );
        $new_user_id = get_user_meta( $new_user_as_agent, 'user_agent_id',true);
        if($new_user_id==0){
            $new_user_id=1;
        }

        // change author
        $curpost = array(
            'ID'            => $post->ID,
            'post_author'   => $new_user_as_agent
        );

        wp_update_post($curpost );
        update_user_meta( $new_user_as_agent, 'user_agent_id', $new_user_id );
        add_action('save_post', 'estate_save_postdata', 1, 2);
    }

    ///////////////////////////// end change author



    if(isset($_POST['adv_filter_search_action'])){
        update_post_meta($post->ID, 'adv_filter_search_action',wpestate_sanitize_array ( $_POST['adv_filter_search_action'] ) );
     }else{
        if(isset($post->ID)){
           update_post_meta($post->ID, 'adv_filter_search_action','' );
        }
     }

     if(isset($_POST['adv_filter_search_category'])){
        update_post_meta($post->ID, 'adv_filter_search_category', wpestate_sanitize_array ($_POST['adv_filter_search_category']) );
     }else{
        if(isset($post->ID)){
            update_post_meta($post->ID, 'adv_filter_search_category','' );
        }
     }

     if(isset($_POST['current_adv_filter_city'])){
        update_post_meta($post->ID, 'current_adv_filter_city',wpestate_sanitize_array($_POST['current_adv_filter_city']) );
     }else{
        if(isset($post->ID)){
            update_post_meta($post->ID, 'current_adv_filter_city','' );
        }
     }


    if(isset($_POST['current_adv_filter_area'])){
        update_post_meta($post->ID, 'current_adv_filter_area',wpestate_sanitize_array ($_POST['current_adv_filter_area']) );
    }else{
        if(isset($post->ID)){
            update_post_meta($post->ID, 'current_adv_filter_area','' );
        }
    }


    if(isset($_POST['property_custom_details_admin_value'])){

        if(is_array($_POST['property_custom_details_admin_value'])){
            $extra_details_array = array();
            foreach( $_POST['property_custom_details_admin_value'] as $key=>$value){
                $extra_details_array[sanitize_text_field( $_POST['property_custom_details_admin_label'][$key] )]=sanitize_text_field($value);
            }
             update_post_meta($post_id, 'property_custom_details', $extra_details_array);
        }


    }

    $order=0;
    $parent_post=$post->ID;
    if( isset( $_POST['image_to_attach'] ) &&  isset($post->ID) ){
        $all_media = explode(',',$_POST['image_to_attach']);


        if(is_array($all_media)){
            foreach($all_media as $value){
                $order++;
                $value=intval($value);
                if($value!=0){
                    wp_update_post( array(
                        'ID'            =>  $value,
                        'post_parent'   =>   $parent_post,
                        'menu_order'    =>  $order
                ));

                }

            }
        }
    }
    wp_reset_postdata();


}
endif; // end   estate_save_postdata

if (!function_exists('wpestate_sanitize_array')):
function wpestate_sanitize_array($original){
    $new_Array=array();
    foreach($original as $key=>$value){
        if(is_array($value)){
            $new_Array[sanitize_key($key)]=   wpestate_sanitize_array($value);
        }else{
            $new_Array[sanitize_key($key)]=  sanitize_text_field($value);
        }
    }
    return $new_Array;
}
endif;













/// edit reviews


if ( ! function_exists( 'extend_comment_edit_metafields' ) ):
	/**
	 * Save comment/review rating meta field data
	 * and trigger recalculation of the properties
	 * total star rating
	 *
	 */
	function extend_comment_edit_metafields( $comment_id ) {

		if ( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) {
			return;
		}

		$property_ID = intval( $_POST['comment_post_ID'] );
		if ( ( isset( $_POST['review_stars'] ) ) && ( $_POST['review_stars'] != '' ) ) {
			update_comment_meta( $comment_id, 'review_stars', intval( $_POST['review_stars'] ) );
			wpestate_calculate_property_rating( $property_ID );
		} else if ( isset( $_POST['star-rating-field'] ) && is_array( $_POST['star-rating-field'] ) ) {
			$star_rating = array();

			//gather all field data
			foreach ( $_POST['star-rating-field'] as $field_key => $field_value ) {
				$star_rating[ $field_key ] = sanitize_text_field( $field_value );
				$star_rating_partial[] = sprintf( '"%s": %s', $field_key, $field_value );
			}
			// calculate all total rating
			$star_rating_total     = wpestate_round_to_nearest_05( array_sum( $star_rating ) / count( $star_rating ) );
			$star_rating_partial[] = sprintf( '"%s": %s', 'rating', $star_rating_total );
			// create data for db
			$star_rating_str = '{' . implode( ',', $star_rating_partial ) . '}';
			update_comment_meta( $comment_id, 'review_stars', $star_rating_str );
			// recalculate review stars totals for the property
			wpestate_calculate_property_rating( $property_ID );
		}

	}
endif;


if ( ! function_exists( 'estate_comment_starts' ) ):
	/**
	 * Render star rating meta fields
	 * depending on old or new rating system
	 */
	function estate_comment_starts( $comment ) {

		$stars         = get_comment_meta( $comment->comment_ID, 'review_stars', TRUE );
		$rating_fields = wpestate_get_review_fields();
		$max_stars     = wpestate_get_max_stars();

		if ( is_string( $stars )) {
			$tmp_rating = json_decode( $stars, TRUE );
			$fields     = '';
			$fields     .= '<table>' . PHP_EOL;
			foreach ( $rating_fields['fields'] as $field_key => $field_value ) {
				$fields .= '<tr>' . PHP_EOL;
				$fields .= '<th align="left">' . esc_html( $field_value ) . '</th>' . PHP_EOL;
				$fields .= '<td><input name="star-rating-field[' . esc_attr( $field_key ) . ']" type="number" value="' . $tmp_rating[ $field_key ] . '" max="' . intval( $max_stars ) . '" min="1" step=".5"></td>' . PHP_EOL;
				$fields .= '</tr>' . PHP_EOL;
			}
			$fields .= '</table>' . PHP_EOL;
		} else if ( is_numeric( $stars ) ) {
			$i             = 1;
			$starts_select = '';
			while ( $i <= $max_stars ) {
				$starts_select .= '<option value="' . $i . '"';
				if ( $stars == $i ) {
					$starts_select .= ' selected="selected" ';
				}
				$starts_select .= '>' . $i . '</option>';
				$i ++;
			}
			$fields .= sprintf( '<select name="review_stars">%s</select>', $starts_select );
		}
		wp_nonce_field( 'extend_comment_update', 'extend_comment_update', FALSE );
		print '
        <table>
        <tr>
            <td width="33%" valign="top" align="left">
                ' . esc_html__( 'Stars', 'wprentals-core' ) . '
            </td>
            <td width="33%" valign="top" align="left">

              ' . $fields . '

            </td>
        </tr>

         </table>';
	}

endif;
