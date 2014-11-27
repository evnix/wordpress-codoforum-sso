<?php
/**
 * @package wordpress-codoforum-sso
 * @version 1.0
 */
/*
  Plugin Name: wordpress-codoforum-sso
  Description: This is a wordpress plugin to integrate wordpress with codoforum.
  Author: Codologic
  Version: 1.0
  Author URI: http://codoforum.com/
 */



// Add settings link on plugin page
function codoforum_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=codoforum-options">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'codoforum_settings_link');

function codoforum_register_settings() {
    add_option('codoforum_clientid', 'codoforum');
    add_option('codoforum_secret', 'Xe24!rf');
    register_setting('default', 'codoforum_clientid');
    register_setting('default', 'codoforum_secret');
}

add_action('admin_init', 'codoforum_register_settings');

function codoforum_register_options_page() {
    add_options_page('Codoforum SSO', 'Codoforum SSO', 'manage_options', 'codoforum-options', 'codoforum_options_page');
}

add_action('admin_menu', 'codoforum_register_options_page');

function codoforum_options_page() {
    ?>
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2>Codoforum SSO Settings</h2>
        <form method="post" action="options.php"> 
            <?php settings_fields('default'); ?>

            <p>Note: The values below must match with values you entered in Codoforum -> Admin -> Plugins -> Single Sign On ->Settings</p>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="codoforum_clientid">SSO Client ID:</label></th>
                    <td><input type="text" id="codoforum_clientid" name="codoforum_clientid" value="<?php echo get_option('codoforum_clientid'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="codoforum_secret">API Filter</label></th>
                    <td><input type="text" id="codoforum_secret" name="codoforum_secret" value="<?php echo get_option('codoforum_secret'); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function init_codoforum_sso() {
    if (is_user_logged_in() && isset($_GET['codoforum']) && $_GET['codoforum'] == 'sso') {


        require 'sso.php';

        /**
         * 
         * The SSO client id and secret MUST be same as that set in the Codoforum
         * SSO plugin settings
         */
        $settings = array(
            "client_id" => get_option('codoforum_clientid', 'codoforum_DEFAULT'),
            "secret" => get_option('codoforum_secret', 'codoforum_DEFAULT'),
            "timeout" => 6000
        );

        $sso = new codoforum_sso($settings);

        $account = array();
        /**
         * 
         * Here comes your logic to check if the user is logged in or not.
         * A simple example would be using PHP SESSION
         */
        $current_user = wp_get_current_user();
        $account['uid'] = $current_user->ID; //Your logged in user's userid
        $account['name'] = $current_user->user_login; //Your logged in user's username
        $account['mail'] = $current_user->user_email; //Your logged in user's email id
        $account['avatar'] = ''; //not used as of now

        $sso->output_jsonp($account); //output above as JSON back to Codoforum
        exit();
    } else {
        
    }
}

add_action('init', 'init_codoforum_sso');

