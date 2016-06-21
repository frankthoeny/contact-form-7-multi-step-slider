<?php
/*
Plugin Name: Contact Form 7 Multi Step Slider
Plugin URI: http://frankthoeny.wordpress.com/contact-form-7-multi-step-slider/
Description: Create multi-step slider forms with the Contact Form 7 plugin. This plugin requires Contact Form 7 and Contact Form 7 Multi Step Module.
Author: Frank Thoeny
Author URI: http://wordpress.frankthoeny.com 
Version: 1.0
*/
/*  Copyright 2016

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * First make sure all dependent plugins are loaded... Contact Form 7
 */
function init_cf7_plugin()
{
    global $pagenow;
    
    if ( $pagenow != 'plugins.php' || function_exists('wpcf7_add_shortcode'))
    { return; }
    add_action('admin_notices', 'cf7_fieldserror');
    
    function cf7_fieldserror() {
        $out = '<div class="error" id="messages">';
        $out .= '<p>';
        if(file_exists(WP_PLUGIN_DIR.'/contact-form-7/wp-contact-form-7.php')) {
            $out .= 'The Contact Form 7 is installed, but <strong>you must activate Contact Form 7</strong> below for the Contact Form 7 Multi-Step Form to work.';
        } else {
            $out .= 'The Contact Form 7 plugin must be installed for the Contact Form 7 Multi-Step to work. <a href="'.admin_url('plugin-install.php?tab=plugin-information&plugin=contact-form-7&from=plugins&TB_iframe=true&width=600&height=550').'" class="thickbox" title="Contact Form 7">Install Now.</a>';
        }
        $out .= '</p>';
        $out .= '</div>';
        echo $out;
    }
}
add_action('plugins_loaded','init_cf7_plugin');

/**
 * First make sure all dependent plugins are loaded... Contact Form 7 Multi Step Module
 */
function init_cf7msm_plugin()
{
    global $pagenow;
    
    if ( $pagenow != 'plugins.php' || function_exists('cf7msm_init_sessions'))
    { return; }
    add_action('admin_notices', 'cf7msms_fieldserror');
    
    function cf7msms_fieldserror() {
        $out = '<div class="error" id="messages">';
         $out .= '<p>';
        if(file_exists(WP_PLUGIN_DIR.'/contact-form-7-multi-step-module/contact-form-7-multi-step-module.php')) {
            $out .= 'The Contact Form 7 Multi Step Module is installed, but <strong>you must activate Contact Form 7 Multi Step Module</strong> below for the Contact Form 7 Multi-Step Slider to work.';
        } else {
            $out .= 'The Contact Form 7 Multi Step Module plugin must be installed for the Contact Form 7 Multi Step Slider to work. <a href="'.admin_url('plugin-install.php?tab=plugin-information&plugin=contact-form-7&from=plugins&TB_iframe=true&width=600&height=550').'" class="thickbox" title="Contact Form 7">Install Now.</a>';
        }
        $out .= '</p>';

        $out .= '</div>';
        echo $out;
    }
}
add_action('plugins_loaded','init_cf7msm_plugin');



/**
 * Return the url with the plugin url prepended.
 */
function cf7msms_url( $path ) {
    return plugins_url( $path, __FILE__ );
}

/**
 * Add scripts to be able to go back to a previous step.
 */
function cf7msms_scripts() {	
    
    wp_enqueue_script('cf7msms_scripts',
        cf7msms_url('/resources/cf7ms-slider.js'),
        array('jquery')
    );
    
    wp_enqueue_script('cf7msms_custom_scripts',
        cf7msms_url('/script.js'),
        array('jquery','cf7msms_scripts','cf7msm')
    );
    
    wp_enqueue_style('cf7msms_styles',
        cf7msms_url('/resources/cf7ms-slider.css'),
        array()       
    );
    
    // this is useful for settings and options.
    $options = get_option( 'cf7msms_settings' );
    $slidesContainerId = $options['cf7msms_text_field_0'];    
    wp_localize_script( 'slider-custom-script', 'php_vars', array(
        'slidesContainerId'  => $slidesContainerId
    ) );
}
add_action('wp_enqueue_scripts', 'cf7msms_scripts');

/** 
 * cf7msm_step_2 has shown problematic with this contact form 7 multi step slider.
 * The function is removed until a better solution is worked up.
 * The function cf7msm_clear_success_message is instantiated regardless of
 * a cf7msm_step_2. Needs to be created in a future version, but will have
 * to do for now.
 */
remove_action('wpcf7_contact_form', 'cf7msm_step_2');
function cf7msms_no_step_2_page() {
    add_filter('wpcf7_ajax_json_echo', 'cf7msm_clear_success_message', 10, 2);
}
add_action('wpcf7_contact_form', 'cf7msms_no_step_2_page');


/**
 * Set the additional settings before sending mail. Animate the slider...
 */
function cf7msms_fabricate_additional_settings( $wpcf7 ) {
    $on_sent_ok = $wpcf7->additional_setting( 'on_sent_ok', false );
    if ( empty( $on_sent_ok ) ) {
        $properties = $wpcf7->get_properties();
        $properties['additional_settings'] .= "\non_sent_ok: \"".       
        "var i = $(\"#cf7ms-slider .wpcf7.cf7ms-active\").index();".
        "var n = i+1;".       
        "var slideLeft = \"-\"+n*100+\"%\";".
        "if (!$(\"#cf7ms-slider .wpcf7.cf7ms-active\").hasClass(\"cf7ms-last\")) {".
        "$(\"#cf7ms-slider .cf7ms-slides-container\")".
        ".animate({ marginLeft : slideLeft })".
        ".find(\".wpcf7.cf7ms-active\")".        
        ".removeClass(\"cf7ms-active\")".
        ".next(\".wpcf7\")".
        ".addClass(\"cf7ms-active\");".
        "}else{".
        "$(\"#cf7ms-slider .cf7ms-slides-container\").animate({ marginLeft : 0 });".
        "$(\"#cf7ms-slider .wpcf7.cf7ms-active\").removeClass(\"cf7ms-active\");".
        "$(\"#cf7ms-slider .wpcf7.cf7ms-first\").addClass(\"cf7ms-active\");".
        "}\"";
        
        $wpcf7->set_properties( $properties );        
     }
}
add_action('wpcf7_before_send_mail', 'cf7msms_fabricate_additional_settings');

/**
 * Add the multi step slider shortcode.
 */
function cf7msms_shortcode($atts) {
    $options = get_option( 'cf7msms_settings' );
    $slides = $options['cf7msms_text_field_0'];
    $cf7_forms_shortcodes = explode("\n", str_replace("\r", "", $slides));
    $output = "<div id=\"cf7ms-slider\"><div class=\"cf7ms-slides-container\">";
    // add title from the form...
    foreach( $cf7_forms_shortcodes as $cf7_forms_shortcode ){
        if (preg_match("/\[contact-form-7 /", $cf7_forms_shortcode)) { 
            $output .= do_shortcode( $cf7_forms_shortcode );           
        } else {
            $output .= "<p style=\"font-size:20px;margin:0;text-align:center;\">".
                "A match was not found. Please add some Forms!<br />".
                "<a href=\"admin.php?page=wpcf7-multi-step-slider\">CF7 Multi Step Slider Settings</a>".
                "</p>";
        }
    }
    
    $output .= "</div></div>";
    return $output;
    
}
add_shortcode("contact-form-7-multi-step-slider", "cf7msms_shortcode");

/**
 * Add shortcode to widget.
 */
function cf7msms_widget_text_filter( $content ) {
	if ( ! preg_match( '/\[contact-form-7-multi-step-slider\]/', $content ) )
    { return $content; }

	$content = do_shortcode( $content );

	return $content;
}
add_filter( 'widget_text', 'cf7msms_widget_text_filter', 9 );

/**
 * Add the multi step slider submenu page.
 */
function cf7msms_menu() {    
    
    add_submenu_page( 'wpcf7',
			__( 'Multi Step Slider Settings', 'contact-form-7-multi-step-slider' ),
			__( 'Multi Step Slider', 'contact-form-7-multi-step-slider' ),
			'manage_options', 
            'wpcf7-multi-step-slider', 
            'cf7msms_options_page'
        );
    
}
add_action( 'admin_menu', 'cf7msms_menu' );

function cf7msms_settings_init() { 

	register_setting( 'pluginPage', 'cf7msms_settings' );

	add_settings_section(
		'cf7msms_pluginPage_section', 
		__( 'Multi Step Slider Settings', 'contact-form-7-multi-step-slider' ), 
		'cf7msms_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'cf7msms_text_field_0', 
		__( 'Container ID', 'contact-form-7-multi-step-slider' ), 
		'cf7msms_text_field_0_render', 
		'pluginPage', 
		'cf7msms_pluginPage_section' 
	);

}
add_action( 'admin_init', 'cf7msms_settings_init' );

function cf7msms_text_field_0_render() { 

	$options = get_option( 'cf7msms_settings' );
	?>
   
    <textarea style="width:100%;height:140px;" name='cf7msms_settings[cf7msms_text_field_0]'><?php echo $options['cf7msms_text_field_0']; ?></textarea>
	
	<?php

}

function cf7msms_settings_section_callback() { 

	echo __( 'Copy this shortcode and paste it into your post, page, or text widget content:', 'contact-form-7-multi-step-slider' );
    ?>
    
    <input type="text" id="wpcf7-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="[contact-form-7-multi-step-slider]">
    
    <?php
}

function cf7msms_options_page() { 

	?>
	
	<form action='options.php' method='post'>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}


 
	
  
 


