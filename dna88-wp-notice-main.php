<?php
/**
 * Plugin Name: Notice
 * Description: Adds a notice or message notification bar to the front-end of your website.
 * Plugin URI: https://wordpress.org/plugins/notice/
 * Version: 1.0.9
 * Author: dna88
 * Author URI: https://dna88.com/
 * Text Domain: dna88-wp-notice

**/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! defined( 'dna88_wp_notifications_plugin_dir_url' ) ) {
    define('dna88_wp_notifications_plugin_dir_url', plugin_dir_url(__FILE__));
}

if ( ! defined( 'dna88_wp_notifications_assets_url' ) ) {
    define('dna88_wp_notifications_assets_url', dna88_wp_notifications_plugin_dir_url . "assets");
}

if ( ! defined( 'dna88_wp_notifications_IMG_URL' ) ) {
    define('dna88_wp_notifications_IMG_URL', dna88_wp_notifications_assets_url . "/images");
}

if ( ! defined( 'dna88_wp_notifications_file_dir' ) ) {
    define('dna88_wp_notifications_file_dir', dirname(__FILE__));
}


/*****************************************************
 * Post Type settings area
 *****************************************************/
class Dna88_wp_notification_admin_area_Controller {
    
    function __construct(){
        global $pagenow;

        add_action( 'admin_menu', array($this,'dna88_wp_notification_admin_menu') );
        add_action( 'admin_init', array($this, 'dna88_wp_notification_register_plugin_settings') );
        add_action( 'activated_plugin', array( $this, 'dna88_wp_notification_activation_redirect') );

        if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === 'dna88-notice-settings' ) {
          add_action( 'admin_enqueue_scripts', array( $this, 'dna88_wp_notification_admin_enqueue_scripts' ) );
        }


        $dna88_wp_notifications_enable = get_option('dna88_wp_notifications_enable');


        if ( ( $dna88_wp_notifications_enable == 1 ) && !is_admin() && ( $pagenow !== 'wp-login.php' ) ) {

            if( (get_option('dna88_wp_notifications_front_display_only') == 1) && (!is_home() || !is_front_page()) ){
                return;
            }


            $dna88_wp_notifications_banner_position = get_option('dna88_wp_notifications_banner_position');

            if($dna88_wp_notifications_banner_position == 'dna88_bottom'){

                add_action( 'wp_footer', array( $this, 'dna88_wp_notification_banner' ) );
            }else{

                add_action( 'wp_head', array( $this, 'dna88_wp_notification_banner' ) );
            }

            add_action( 'wp_enqueue_scripts', array( $this, 'dna88_wp_notification_frontend_enqueue_scripts' ) );
        }

    }

    public function dna88_wp_notification_admin_menu(){

        add_menu_page(
            __('Notice','dna88-wp-notice'), 
            __('Notice','dna88-wp-notice'), 
            'manage_options',
            'dna88-notice-settings', 
            array($this, 'dna88_wp_notification_settings_page'),
            'dashicons-bell'
        );

        add_submenu_page(
            'dna88-notice-settings',
            __('Advanced Notices'),
            __('Advanced Notices'),
            'manage_options',
            'dna_notice',
            array($this, 'qc_notice_settings_options_screen')
        );
        
    }

    public function qc_notice_settings_options_screen(){

    ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>
            <h2><?php _e( 'Notice' ); ?> <span style="color: indianred;font-style: italic;background: #c3c4c7;padding: 0px 5px;font-size: 18px;line-height: 18px;border-radius: 4px;font-weight: bold;"><?php _e( 'Pro Feature' ); ?> </span></h2>
            <div class="" style="width: 100%;opacity:0.6">
                <a href="<?php echo esc_url('https://www.dna88.com/product/notice-pro/'); ?>" target="_blank">
                    <img src="<?php echo esc_url(dna88_wp_notifications_assets_url.'/images/dna-advances.png'); ?>" style="width: 100%;">
                </a>
            </div>
        </div>

    <?php 
            
    }

    public function dna88_wp_notification_admin_enqueue_scripts() {
        
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script( 'dna88-wp-custom-admin', dna88_wp_notifications_assets_url . "/js/custom-admin.js", array( 'wp-color-picker' ), '0.0.2', true );

    }


    public function dna88_wp_notification_frontend_enqueue_scripts() {
        wp_enqueue_style( 'dna88-wp-frontend-css',  dna88_wp_notifications_assets_url . "/css/frontend-css.css" );

                $custom_css = '';

                $dna88_wp_notifications_bg_color = get_option( 'dna88_wp_notifications_bg_color' ) ? get_option( 'dna88_wp_notifications_bg_color' ) : '';

                if( isset( $dna88_wp_notifications_bg_color ) && !empty( $dna88_wp_notifications_bg_color ) ) {

                    $custom_css .= ".dna88_wp_notify_banner,
                                    .dna88_wp_notify_banner.dna88_default,
                                    .dna88_wp_notify_banner.dna88_gradiant {
                                        background: ".$dna88_wp_notifications_bg_color."!important;
                                    }  ";
                }



                $dna88_wp_notifications_font_color = get_option( 'dna88_wp_notifications_font_color' ) ? get_option( 'dna88_wp_notifications_font_color' ) : '';

                if( isset( $dna88_wp_notifications_font_color ) && !empty( $dna88_wp_notifications_font_color ) ) {

                    $custom_css .= " .dna88_wp_notify_banner p ,
                                    .dna88_wp_notify_banner.dna88_default p ,
                                    .dna88_wp_notify_banner.dna88_gradiant p{
                                        color: ".$dna88_wp_notifications_font_color.";
                                    }";

                }

                $dna88_wp_notifications_text_align = get_option( 'dna88_wp_notifications_text_align' ) ? get_option( 'dna88_wp_notifications_text_align' ) : 'center';

                if( isset( $dna88_wp_notifications_text_align ) && !empty( $dna88_wp_notifications_text_align ) ) {

                    $custom_css .= ".dna88_wp_notify_banner p{
                            text-align: ".$dna88_wp_notifications_text_align."!important;
                        }  ";
                }


                $dna88_wp_notifications_banner_position = get_option('dna88_wp_notifications_banner_position');


                if( !empty( get_option( 'dna88_wp_notifications_banner_sticky' ) ) && ( get_option('dna88_wp_notifications_banner_sticky') == 'yes' ) ) { 

                    $custom_css .= ".dna88_wp_notify_banner { position:fixed !important; }";
                    $custom_css .= ".dna88_wp_notify_banner.dna88_gradiant { position:fixed !important; }";

                }
                if(  ( get_option('dna88_wp_notifications_banner_sticky') == 'no' ) && ( get_option('dna88_wp_notifications_banner_position') == 'dna88_top' ) ) { 

                    $custom_css .= ".dna88_wp_notify_banner { position:relative !important; }";
                    $custom_css .= ".dna88_wp_notify_banner.dna88_gradiant { position:relative !important; }";

                }


                $dna88_top_position = get_option( 'dna88_wp_notifications_scroll_top_position' ) ? get_option( 'dna88_wp_notifications_scroll_top_position' ) : '0';

                if( !empty( get_option( 'dna88_wp_notifications_scroll_top_position' ) ) ) { 


                    if($dna88_wp_notifications_banner_position == 'dna88_top'){
                        $custom_css .= ".dna88_wp_notify_banner { top: ".$dna88_top_position."px  !important; }";
                        $custom_css .= ".dna88_wp_notify_banner.dna88_gradiant { top: ".$dna88_top_position."px !important; }";
                    }else{
                        $custom_css .= ".dna88_wp_notify_banner { bottom: ".$dna88_top_position."px  !important; }";
                        $custom_css .= ".dna88_wp_notify_banner.dna88_gradiant { bottom: ".$dna88_top_position."px !important; }";
                    }

                }

                if ( empty( get_option( 'dna88_wp_notifications_close_icon' ) ) || ( get_option( 'dna88_wp_notifications_close_icon' ) != 'hide') ) { 

                    $custom_css .= ".dna88_wp_notify_close_button{
                        display:block;
                    }";

                }



                $dna88_btn_bg_color = get_option( 'dna88_wp_notifications_btn_bg_color' ) ? get_option( 'dna88_wp_notifications_btn_bg_color' ) : '';

                if ( isset($dna88_btn_bg_color) && !empty( $dna88_btn_bg_color ) ) { 

                    $custom_css .= " .dna88_wp_notify_banner .dna88_call_action_btn {
                            background: ".$dna88_btn_bg_color.";
                        }";

                }


                $dna88_btn_font_color = get_option( 'dna88_wp_notifications_btn_font_color' ) ? get_option( 'dna88_wp_notifications_btn_font_color' ) : '';

                if ( isset($dna88_btn_font_color) && !empty( $dna88_btn_font_color ) ) { 

                    $custom_css .= " .dna88_wp_notify_banner .dna88_call_action_btn {
                            color: ".$dna88_btn_font_color.";
                        }";

                }

                if( get_option('dna88_wp_notifications_mobile') != 1 ) { 

                   $custom_css .= " @media all and (max-width: 500px){
                                .dna88_wp_notify_banner{
                                    display: none;
                                }
                            }";

                }

        wp_add_inline_style( 'dna88-wp-frontend-css', $custom_css );

        $global_css   = str_replace('\\', '', get_option('dna88_wp_notifications_custom_global_css'));

        wp_add_inline_style( 'dna88-wp-frontend-css', $global_css );


        // dna88_wp_notifications_set_cookie
        $set_cookie = get_option( 'dna88_wp_notifications_set_cookie' ) ? get_option( 'dna88_wp_notifications_set_cookie' ) : '';
        wp_enqueue_script( 'dna88-wp-cookie-js', dna88_wp_notifications_assets_url . "/js/jquery_cookie.js", array( 'jquery' ), '0.0.2', true );

        if(!empty($set_cookie)){

            wp_add_inline_script( 'dna88-wp-cookie-js', "
                jQuery(document).ready(function($){

                    if( Cookies.get('dna88_hide_banner_cookie') != undefined ) {
                        $('.dna88_wp_notify_banner').hide();
                    }

                    $('#dna88_wp_notify_close_button_link').click(function(){
                        Cookies.set('dna88_hide_banner_cookie', 1, { expires: 1, path: '/' }); 
                        //expire the cookie after 24 hours.

                        $('.dna88_wp_notify_banner').hide();
                    });
                });
            " );

        }else{

            wp_add_inline_script( 'dna88-wp-cookie-js', "
                jQuery(document).ready(function($){

                    $('#dna88_wp_notify_close_button_link').click(function(){

                        $('.dna88_wp_notify_banner').hide();
                    });
                });
            " );

        }




    }

    
    public function dna88_wp_notification_activation_redirect( $plugin ) {
        if( $plugin == plugin_basename( __FILE__ ) ) {
            exit(wp_redirect( admin_url( 'admin.php?page=dna88-notice-settings') ) );
        }
    }
    
    public function dna88_wp_notification_register_plugin_settings(){    

        $args = array(
            'type' => 'string', 
            'sanitize_callback' => 'sanitize_text_field',
            'default' => NULL,
        );    
        $stripslashes = array(
            'type' => 'string', 
            'sanitize_callback' => 'stripslashes',
            'default' => NULL,
        );      
        
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_enable', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_action_button', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_mobile', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_logged_in_users', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_banner_position', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_banner_sticky', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_bg_color', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_font_color', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_text_align', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_front_display_only', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_close_icon', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_set_cookie', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_message', $stripslashes );

        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_banner_style', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_close_icon_position', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_scroll_position', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_scroll_top_position', $args );

        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_call_action_btn', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_call_action_text', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_call_action_link', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_btn_bg_color', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_btn_font_color', $args );
        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_button_text_align', $args );

        register_setting( 'dna88-notice-settingss-settings-group', 'dna88_wp_notifications_custom_global_css', $args );

    
    }
    
    
    public function dna88_wp_notification_settings_page(){
        
    ?>
    <style type="text/css">
        .form-table-notify{
            margin-top: 15px;
            background: #fff;
            border-radius: 2px;
            box-shadow: 0 0 0 1px rgb(0 0 0 / 7%), 0 1px 1px rgb(0 0 0 / 4%);
        }
        .form-table-notify tr{
            border-bottom: 1px solid #eee;
            display: block;
        }
        .form-table-notify th{
            padding: 20px 10px 20px 20px;
        }
        .form-table-notify td{
            padding: 20px 10px 20px 20px;
            width: 80%;
        }
        .form-table-notify .dna88_lan_text{
            width: 100%;
        }
    </style>
    <div class="wrap">
        <h1><?php esc_html_e('Notice Settings','dna88-wp-notice') ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'dna88-notice-settingss-settings-group' ); ?>
            <?php do_settings_sections( 'dna88-notice-settingss-settings-group' ); ?>
            <div >
                <table class="form-table form-table-notify" >
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Show Notice','dna88-wp-notice') ?></th>
                        <td>
                            <input type="checkbox" name="dna88_wp_notifications_enable" size="100" value="<?php echo (get_option('dna88_wp_notifications_enable')!=''? esc_attr( get_option('dna88_wp_notifications_enable')) : '1' ); ?>" <?php echo (get_option('dna88_wp_notifications_enable') == '' ? esc_attr( get_option('dna88_wp_notifications_enable')): esc_attr( 'checked="checked"' )); ?>  />  
                            <i><?php esc_html_e('Enable this option to Show Notice ','dna88-wp-notice') ?></i>                           
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Message','dna88-wp-notice') ?></th>
                        <td>
                            <?php $settings = array('textarea_name' =>
                                    'dna88_wp_notifications_message',
                                    'textarea_rows' => 20,
                                    'editor_height' => 100,
                                    'editor_class' => 'customNotificationClass',
                                    'media_buttons' => false
                                );

                                wp_editor(get_option('dna88_wp_notifications_message'), 'dna88_wp_notifications_message', $settings); ?>                           
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Background Color','dna88-wp-notice') ?></th>
                        <td>
                            <input type="text" name="dna88_wp_notifications_bg_color"
                               value="<?php echo get_option('dna88_wp_notifications_bg_color'); ?>"
                               class="dna88-wp-color">                          
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Font Color','dna88-wp-notice') ?></th>
                        <td>          

                            <input type="text" name="dna88_wp_notifications_font_color"
                               value="<?php echo get_option('dna88_wp_notifications_font_color'); ?>"
                               class="dna88-wp-color">                 
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Text Align','dna88-wp-notice') ?></th>
                        <td>
                            <p>
                                <label class="radio-inline" style="padding-right: 15px;">
                                    <input id="dna88_wp_notifications_text_align" type="radio" name="dna88_wp_notifications_text_align" value="left" <?php echo ((get_option('dna88_wp_notifications_text_align') == 'left' || get_option('dna88_wp_notifications_text_align') == '') ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                    <?php esc_html_e('Left','dna88-wp-notice') ?> </label>
                            
                                <label class="radio-inline">
                                    <input id="dna88_wp_notifications_text_align" type="radio" name="dna88_wp_notifications_text_align" value="center" <?php echo (get_option('dna88_wp_notifications_text_align') == 'center' ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                    <?php esc_html_e('Center ','dna88-wp-notice') ?>  </label>
                            
                                <label class="radio-inline">
                                    <input id="dna88_wp_notifications_text_align" type="radio" name="dna88_wp_notifications_text_align" value="right" <?php echo (get_option('dna88_wp_notifications_text_align') == 'right' ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                    <?php esc_html_e('Right','dna88-wp-notice') ?>  </label>
                            </p>                        
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Action Button','dna88-wp-notice') ?></th>
                        <td>
                        
                            <label class="radio-inline">
                                <input id="dna88_wp_notifications_call_action_btn" type="radio" name="dna88_wp_notifications_call_action_btn" value="enable" <?php echo (get_option('dna88_wp_notifications_call_action_btn') == 'enable' ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('Enable ','dna88-wp-notice') ?>  </label>   
                            
                            <label class="radio-inline" style="padding-right: 15px;">
                                <input id="dna88_wp_notifications_call_action_btn" type="radio" name="dna88_wp_notifications_call_action_btn" value="disable" <?php echo ((get_option('dna88_wp_notifications_call_action_btn') == 'disable' || get_option('dna88_wp_notifications_call_action_btn') == '') ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('Disable ','dna88-wp-notice') ?> </label>                          
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Action Button Text','dna88-wp-notice') ?></th>
                        <td>
                        
                            <input type="text" name="dna88_wp_notifications_call_action_text" value="<?php echo (get_option('dna88_wp_notifications_call_action_text') ? esc_attr( get_option('dna88_wp_notifications_call_action_text') ): '' ); ?>">
                                                         
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Action Button Link','dna88-wp-notice') ?></th>
                        <td>
                            <input type="url" name="dna88_wp_notifications_call_action_link" class="dna88_lan_text" value="<?php echo (get_option('dna88_wp_notifications_call_action_link') ? esc_url( get_option('dna88_wp_notifications_call_action_link') ): '' ); ?>" >
                                                         
                        </td>
                    </tr>
                    <tr valign="top">
                      <th scope="dna88_row"><?php esc_html_e('Button Text Align','dna88-wp-notice') ?></th>
                      <td><p>
                          <label class="radio-inline" style="padding-right: 15px;">
                            <input id="dna88_wp_notifications_button_text_align" type="radio" name="dna88_wp_notifications_button_text_align" value="dna88_button_left" <?php echo ( get_option('dna88_wp_notifications_button_text_align') == 'dna88_button_left' ? esc_attr( 'checked=checked' ): '' ); ?>>
                            <?php esc_html_e('Left','dna88-wp-notice') ?>
                          </label>
                          <label class="radio-inline">
                            <input id="dna88_wp_notifications_button_text_align" type="radio" name="dna88_wp_notifications_button_text_align" value="dna88_button_center" <?php echo  ( ( get_option('dna88_wp_notifications_button_text_align') == 'dna88_button_center' || get_option('dna88_wp_notifications_button_text_align') =='' ) ? esc_attr( 'checked=checked' ): '' ); ?>>
                            <?php esc_html_e('Center ','dna88-wp-notice') ?>
                          </label>
                          <label class="radio-inline">
                            <input id="dna88_wp_notifications_button_text_align" type="radio" name="dna88_wp_notifications_button_text_align" value="dna88_button_right" <?php echo ( get_option('dna88_wp_notifications_button_text_align') == 'dna88_button_right' ? esc_attr( 'checked=checked' ): '' ); ?>>
                            <?php esc_html_e('Right','dna88-wp-notice') ?>
                          </label>
                        </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Button Background Color','dna88-wp-notice') ?></th>
                        <td>
                            <input type="text" name="dna88_wp_notifications_btn_bg_color"
                               value="<?php echo get_option('dna88_wp_notifications_btn_bg_color'); ?>"
                               class="dna88-wp-color">                          
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Button Font Color','dna88-wp-notice') ?></th>
                        <td>          

                            <input type="text" name="dna88_wp_notifications_btn_font_color"
                               value="<?php echo get_option('dna88_wp_notifications_btn_font_color'); ?>"
                               class="dna88-wp-color">                 
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Open the Action Button Link','dna88-wp-notice') ?></th>
                        <td>
                            <input type="checkbox" name="dna88_wp_notifications_action_button" size="100" value="<?php echo (get_option('dna88_wp_notifications_action_button')!=''? esc_attr( get_option('dna88_wp_notifications_action_button')) : '1' ); ?>" <?php echo (get_option('dna88_wp_notifications_action_button') == '' ? esc_attr( get_option('dna88_wp_notifications_action_button')): esc_attr( 'checked="checked"' )); ?>  />  
                            <i><?php esc_html_e('Open Link in a New Tab ','dna88-wp-notice') ?></i>                           
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Notice Style','dna88-wp-notice') ?></th>
                        <td>
                            <select name="dna88_wp_notifications_banner_style">
                                <option value="<?php esc_attr_e('dna88_default','dna88-wp-notice') ?>"
                                    <?php if(get_option('dna88_wp_notifications_banner_style') == '' || get_option('dna88_wp_notifications_banner_style') == 'dna88_default'){ echo 'selected="selected"'; } ?> ><?php esc_html_e('Default Style','dna88-wp-notice') ?></option>
                                <option value="<?php esc_attr_e('dna88_gradiant','dna88-wp-notice') ?>" <?php if( get_option('dna88_wp_notifications_banner_style') == 'dna88_gradiant'){ echo 'selected="selected"'; } ?> ><?php esc_html_e('Gradient Box','dna88-wp-notice') ?></option>
                            </select>                             
                        </td>
                    </tr>

                    
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Show Notice Position','dna88-wp-notice') ?></th>
                        <td>
                        
                            <label class="radio-inline" style="padding-right: 15px;">
                                <input id="dna88_wp_notifications_banner_position" type="radio" name="dna88_wp_notifications_banner_position" value="dna88_bottom" <?php echo ((get_option('dna88_wp_notifications_banner_position') == 'dna88_bottom' || get_option('dna88_wp_notifications_banner_position') == '') ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('Bottom','dna88-wp-notice') ?> </label>
                        
                            <label class="radio-inline">
                                <input id="dna88_wp_notifications_banner_position" type="radio" name="dna88_wp_notifications_banner_position" value="dna88_top" <?php echo (get_option('dna88_wp_notifications_banner_position') == 'dna88_top' ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('Top ','dna88-wp-notice') ?>  </label>                              
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Sticky Notice','dna88-wp-notice') ?></th>
                        <td>
                        
                            <label class="radio-inline" style="padding-right: 15px;">
                                <input id="dna88_wp_notifications_banner_sticky" type="radio" name="dna88_wp_notifications_banner_sticky" value="no" <?php echo ((get_option('dna88_wp_notifications_banner_sticky') == 'no' || get_option('dna88_wp_notifications_banner_sticky') == '') ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('No','dna88-wp-notice') ?> </label>
                        
                            <label class="radio-inline">
                                <input id="dna88_wp_notifications_banner_sticky" type="radio" name="dna88_wp_notifications_banner_sticky" value="yes" <?php echo (get_option('dna88_wp_notifications_banner_sticky') == 'yes' ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('Yes ','dna88-wp-notice') ?>  </label>                              
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Display Front Page Only','dna88-wp-notice') ?></th>
                        <td>
                            <input type="checkbox" name="dna88_wp_notifications_front_display_only" size="100" value="<?php echo (get_option('dna88_wp_notifications_front_display_only')!=''? esc_attr( get_option('dna88_wp_notifications_front_display_only')) : '1' ); ?>" <?php echo (get_option('dna88_wp_notifications_front_display_only') == '' ? esc_attr( get_option('dna88_wp_notifications_front_display_only')): esc_attr( 'checked="checked"' )); ?>  />                             
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Set cookie for 24 hours','dna88-wp-notice') ?></th>
                        <td>
                            <input type="checkbox" name="dna88_wp_notifications_set_cookie" size="100" value="<?php echo (get_option('dna88_wp_notifications_set_cookie')!=''? esc_attr( get_option('dna88_wp_notifications_set_cookie')) : '1' ); ?>" <?php echo (get_option('dna88_wp_notifications_set_cookie') == '' ? esc_attr( get_option('dna88_wp_notifications_set_cookie')): esc_attr( 'checked="checked"' )); ?>  />  
                            <i><?php esc_html_e('If enabled, the notice will not show for 24 hours when closed','dna88-wp-notice') ?></i>           
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Close Icon','dna88-wp-notice') ?></th>
                        <td>
                            
                            <label class="radio-inline" style="padding-right: 15px;">
                                <input id="dna88_wp_notifications_close_icon" type="radio" name="dna88_wp_notifications_close_icon" value="show" <?php echo ((get_option('dna88_wp_notifications_close_icon') == 'show' || get_option('dna88_wp_notifications_close_icon') == '') ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('Show','dna88-wp-notice') ?> </label>
                        
                            <label class="radio-inline">
                                <input id="dna88_wp_notifications_close_icon" type="radio" name="dna88_wp_notifications_close_icon" value="hide" <?php echo (get_option('dna88_wp_notifications_close_icon') == 'hide' ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('Hide ','dna88-wp-notice') ?>  </label>                             
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Close Icon Position','dna88-wp-notice') ?></th>
                        <td>
                            
                            <label class="radio-inline" style="padding-right: 15px;">
                                <input id="dna88_wp_notifications_close_icon_position" type="radio" name="dna88_wp_notifications_close_icon_position" value="dna88_default" <?php echo ((get_option('dna88_wp_notifications_close_icon_position') == 'dna88_default' || get_option('dna88_wp_notifications_close_icon_position') == '') ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('Default','dna88-wp-notice') ?> </label>
                        
                            <label class="radio-inline">
                                <input id="dna88_wp_notifications_close_icon_position" type="radio" name="dna88_wp_notifications_close_icon_position" value="dna88_center" <?php echo (get_option('dna88_wp_notifications_close_icon_position') == 'dna88_center' ? esc_attr( 'checked="checked"' ): '' ); ?>>
                                <?php esc_html_e('Center ','dna88-wp-notice') ?>  </label>                             
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Vertical Distance from the Browser Edge','dna88-wp-notice') ?></th>
                        <td>
                            <input type="number" name="dna88_wp_notifications_scroll_top_position" value="<?php echo (get_option('dna88_wp_notifications_scroll_top_position') ? esc_attr( get_option('dna88_wp_notifications_scroll_top_position') ): '' ); ?>" style="width: 75px;">
                                    <i><?php esc_html_e('Px. Default: 0px ','dna88-wp-notice') ?></i>                     
                        </td>
                    </tr>


                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Hide Notice For Logged-in Users','dna88-wp-notice') ?></th>
                        <td>
                            <input type="checkbox" name="dna88_wp_notifications_logged_in_users" size="100" value="<?php echo (get_option('dna88_wp_notifications_logged_in_users')!=''? esc_attr( get_option('dna88_wp_notifications_logged_in_users')) : '1' ); ?>" <?php echo (get_option('dna88_wp_notifications_logged_in_users') == '' ? esc_attr( get_option('dna88_wp_notifications_logged_in_users')): esc_attr( 'checked="checked"' )); ?>  />                             
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Display Notice On Mobile ','dna88-wp-notice') ?></th>
                        <td>
                            <input type="checkbox" name="dna88_wp_notifications_mobile" size="100" value="<?php echo (get_option('dna88_wp_notifications_mobile')!=''? esc_attr( get_option('dna88_wp_notifications_mobile')) : '1' ); ?>" <?php echo (get_option('dna88_wp_notifications_mobile') == '' ? esc_attr( get_option('dna88_wp_notifications_mobile')): esc_attr( 'checked="checked"' )); ?>  />                             
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Custom CSS','dna88-wp-notice') ?></th>
                        <td>  
                            <textarea name="dna88_wp_notifications_custom_global_css" class=" dna88_wp_notifications_custom_global_css dna88_lan_text"  rows="5"><?php esc_html_e( str_replace('\\', '', get_option('dna88_wp_notifications_custom_global_css') ) ); ?></textarea>                         
                        </td>
                    </tr>

                    
                </table>
            </div>
            
            <?php submit_button(); ?>

        </form>
        
    </div>

    <?php
    }


    public function dna88_wp_notification_banner() {
       $dna88_wp_notifications_logged_in_users = get_option( 'dna88_wp_notifications_logged_in_users' );
       $dna88_wp_notifications_action_button = get_option( 'dna88_wp_notifications_action_button' ) ? '_blank':'_self';
       $dna88_wp_notifications_button_text_align = get_option( 'dna88_wp_notifications_button_text_align' );

        if( ( isset( $dna88_wp_notifications_logged_in_users ) && !empty( $dna88_wp_notifications_logged_in_users ) ) && is_user_logged_in() ) {
            return;
        }

        ob_start();

        ?>

            <div class="dna88_wp_notify_banner <?php esc_attr_e(get_option('dna88_wp_notifications_banner_style')); ?> <?php esc_attr_e(get_option('dna88_wp_notifications_banner_position')); ?>" id="dna88_wp_notify_banner_id">
                <p id="dna88_wp_notify_banner_text">

                    <?php printf( esc_html__( '%s ', 'dna88-wp-notice' ), get_option( 'dna88_wp_notifications_message' ) ); ?>

                    <?php 
                    $call_action_btn = get_option( 'dna88_wp_notifications_call_action_btn' );
                    if(isset( $call_action_btn ) && ( $call_action_btn == 'enable' ) ): 
                    ?>
                        <p><a href="<?php echo esc_url( get_option('dna88_wp_notifications_call_action_link')); ?>" class="dna88_call_action_btn <?php esc_attr_e($dna88_wp_notifications_button_text_align); ?>" target="<?php esc_attr_e($dna88_wp_notifications_action_button); ?>">
                            <?php esc_html_e( get_option('dna88_wp_notifications_call_action_text')); ?>
                        </a></p>
                    <?php endif; ?>
                </p>
               <a id="dna88_wp_notify_close_button_link" class="dna88_wp_notify_close_button <?php esc_attr_e(get_option('dna88_wp_notifications_close_icon_position')); ?>"></a>
            </div>
        <?php 
    }


    
    
}
new Dna88_wp_notification_admin_area_Controller();



/*****************************************************
 * Plugin default data set when activation.
 *****************************************************/
register_activation_hook(__FILE__, 'dna88_wp_notification_activation_options');
if (!function_exists('dna88_wp_notification_activation_options')) {
    function dna88_wp_notification_activation_options(){
        
        if(!get_option('dna88_wp_notifications_enable')) {
            update_option('dna88_wp_notifications_enable', '');
        }
        if(!get_option('dna88_wp_notifications_action_button')) {
            update_option('dna88_wp_notifications_action_button', '');
        }
        
        if(!get_option('dna88_wp_notifications_text_align')) {
            update_option('dna88_wp_notifications_text_align', 'center');
        }
        
        if(!get_option('dna88_wp_notifications_close_icon')) {
            update_option('dna88_wp_notifications_close_icon', 'show');
        }
        
        if(!get_option('dna88_wp_notifications_banner_position')) {
            update_option('dna88_wp_notifications_banner_position', 'dna88_bottom');
        }
        
        if(!get_option('dna88_wp_notifications_banner_sticky')) {
            update_option('dna88_wp_notifications_banner_sticky', 'no');
        }
        
        if(!get_option('dna88_wp_notifications_banner_style')) {
            update_option('dna88_wp_notifications_banner_style', 'dna88_default');
        }
        
        if(!get_option('dna88_wp_notifications_bg_color')) {
            update_option('dna88_wp_notifications_bg_color', '');
        }
        
        if(!get_option('dna88_wp_notifications_font_color')) {
            update_option('dna88_wp_notifications_font_color', '');
        }
        
        if(!get_option('dna88_wp_notifications_set_cookie')) {
            update_option('dna88_wp_notifications_set_cookie', '');
        }
        
        if(!get_option('dna88_wp_notifications_call_action_btn')) {
            update_option('dna88_wp_notifications_call_action_btn', '');
        }
        
        if(!get_option('dna88_wp_notifications_call_action_text')) {
            update_option('dna88_wp_notifications_call_action_text', '');
        }
        
        if(!get_option('dna88_wp_notifications_call_action_link')) {
            update_option('dna88_wp_notifications_call_action_link', '');
        }
        
        if(!get_option('dna88_wp_notifications_button_text_align')) {
            update_option('dna88_wp_notifications_button_text_align', '');
        }
        
        if(!get_option('dna88_wp_notifications_btn_bg_color')) {
            update_option('dna88_wp_notifications_btn_bg_color', '');
        }
        
        if(!get_option('dna88_wp_notifications_btn_font_color')) {
            update_option('dna88_wp_notifications_btn_font_color', '');
        }
        
        if(!get_option('dna88_wp_notifications_close_icon_position')) {
            update_option('dna88_wp_notifications_close_icon_position', 'dna88_default');
        }
        
        if(!get_option('dna88_wp_notifications_scroll_position')) {
            update_option('dna88_wp_notifications_scroll_position', 'dna88_disable');
        }
    


    }


}


include_once('class-dna88-free-plugin-upgrade-notice.php');