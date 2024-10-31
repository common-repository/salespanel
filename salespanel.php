<?php
/**
* Salespanel
*
* @package           Salespanel
* @author            Salespanel
* @copyright         2020 Salespanel
* @license           GPL-2.0-or-later
*
* @wordpress-plugin
* Plugin Name:       Salespanel
* Description:       Identify, track and qualify your leads.
* Version:           1.1.2
* Author:            Salespanel
* Author URI:        https://salespanel.io
* Text Domain:       salespanel
* License:           GPL v2 or later
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
*/


if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('SalespanelPlugin')) {

    class SalespanelPlugin {
        function __construct() {
            if (is_admin()) {
                add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
            }
            add_action('admin_init', array($this, 'settings_api_init'));
            add_action('admin_menu', array($this, 'salespanel_options_menu'));
            add_action('wp_head', array($this, 'add_tracking_code'));

        }

        public function plugin_action_links($links, $file) {
            if ($file == plugin_basename(dirname(__FILE__) . '/salespanel.php')) {
                $links[] = '<a href="options-general.php?page=salespanel-settings">' . __('Settings', 'salespanel') . '</a>';
            }
            return $links;
        }

        public function salespanel_options_menu() {

            if (is_admin()) {
                add_options_page('Salespanel', 'Salespanel', 'manage_options', 'salespanel-settings', array($this, 'options_page'));
            }

        }
        public function settings_api_init() {

            register_setting('sp_tracking_settings', 'sp_settings');

            add_settings_section(
                'salespanel_section',
                'General Settings',
                array($this, 'salespanel_settings_section_callback'),
                'sp_tracking_settings'
            );

            add_settings_field(
                'sp_client_id',
                'Salespanel Client ID',
                array($this, 'salespanel_client_id'),
                'sp_tracking_settings',
                'salespanel_section'
            );
            add_settings_field(
                'sp_automatic_lead_capture',
                'Automatic Lead Capture',
                array($this, 'salespanel_automatic_lead_capture'),
                'sp_tracking_settings',
                'salespanel_section'
            );
            add_settings_field(
                'sp_lead_capture_on_submit',
                'Lead Capture on Form Submit',
                array($this, 'salespanel_lead_capture_on_submit'),
                'sp_tracking_settings',
                'salespanel_section'
            );
            add_settings_field(
                'sp_track_by_default',
                'Track by default',
                array($this, 'salespanel_track_by_default'),
                'sp_tracking_settings',
                'salespanel_section'
            );

            $sp_settings = get_option('sp_settings');
            if (false === $sp_settings)
                $sp_settings = $this->get_default_sp_settings();
            update_option('sp_settings', $sp_settings);
        }

        function get_default_sp_settings() {
            $sp_settings = array(
                'sp_client_id' => '',
                'sp_automatic_lead_capture' => 1,
                'sp_lead_capture_on_submit' => 0,
                'sp_track_by_default' => 1,
            );
            return $sp_settings;
        }

        public function salespanel_settings_section_callback() {
            echo 'You need to have an Salespanel account for the client ID';
        }

        public function salespanel_client_id() {
            $sp_settings = get_option('sp_settings');
            $sp_client_id = isset($sp_settings['sp_client_id']) ? $sp_settings['sp_client_id'] : '';
            ?>
            <input type='text' name='sp_settings[sp_client_id]' value='<?php echo esc_attr($sp_client_id); ?>' placeholder='e.g. 30dd879c-ee2f-11db-8314-0800200c9a66' class='regular-text'>
            <p class="description">Enter your Salespanel Client ID for this website. You can find your Client ID <a href="https://salespanel.io/tracking-code/" target="_blank">here.</a></p>
            <?php
        }

        public function salespanel_automatic_lead_capture() {
            $sp_settings = get_option('sp_settings');
            $sp_automatic_lead_capture = isset($sp_settings['sp_automatic_lead_capture']) ? $sp_settings['sp_automatic_lead_capture'] : 0;
            ?>
            <input type='checkbox' name='sp_settings[sp_automatic_lead_capture]' value='1' class='regular-text' <?php echo checked( 1, $sp_automatic_lead_capture, false ); ?> />
            <p class="description">Capture leads automatically. Default: true</p>
            <?php
        }

        public function salespanel_lead_capture_on_submit() {
            $sp_settings = get_option('sp_settings');
            $sp_lead_capture_on_submit = isset($sp_settings['sp_lead_capture_on_submit']) ? $sp_settings['sp_lead_capture_on_submit'] : 0;
            ?>
            <input type='checkbox' name='sp_settings[sp_lead_capture_on_submit]' value='1' class='regular-text' <?php echo checked( 1, $sp_lead_capture_on_submit, false ); ?> />
            <p class="description">Capture leads only when form is submitted; requires <strong>Automatic Lead Capture</strong> set to <em>true</em>. Default: false</p>
            <?php
        }

        public function salespanel_track_by_default() {
            $sp_settings = get_option('sp_settings');
            $sp_track_by_default = isset($sp_settings['sp_track_by_default']) ? $sp_settings['sp_track_by_default'] : 0;
            ?>
            <input type='checkbox' name='sp_settings[sp_track_by_default]' value='1' class='regular-text' <?php echo checked( 1, $sp_track_by_default, false ); ?> />
            <p class="description">Load Salespanel tracking script by default. Default: true</p>
            <?php
        }

        public function options_page() {
            ?>
            <div class="wrap">
                <h2>Salespanel Settings</h2>

                <form action='options.php' method='post'>
                    <?php

                    settings_fields('sp_tracking_settings');
                    do_settings_sections('sp_tracking_settings');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php

        }

        public function add_tracking_code() {

            $boolean_values = array(0 => 'false', 1 => 'true');

            $sp_settings = get_option('sp_settings');
            $sp_client_id = isset($sp_settings['sp_client_id']) ? $sp_settings['sp_client_id'] : '';

            $sp_automatic_lead_capture = isset($sp_settings['sp_automatic_lead_capture']) ? $boolean_values[$sp_settings['sp_automatic_lead_capture']] : 'false';

            $sp_lead_capture_on_submit = isset($sp_settings['sp_lead_capture_on_submit']) ? $boolean_values[$sp_settings['sp_lead_capture_on_submit']] : 'false';

            $sp_track_by_default = isset($sp_settings['sp_track_by_default']) ? $boolean_values[$sp_settings['sp_track_by_default']] : 'false';

            if (!empty($sp_client_id)) {
                ?>

<!-- Salespanel Web Tracking code -->
<script type="text/javascript" >
    window.salespanelSettings = {
        "sp_automatic_lead_capture": <?php echo $sp_automatic_lead_capture; ?>,
        "sp_lead_capture_on_submit": <?php echo $sp_lead_capture_on_submit; ?>,
        "sp_track_by_default": <?php echo $sp_track_by_default; ?>
    };
    (function(e, f, g, h, i){
        $salespanel = window.$salespanel || (window.$salespanel = []);
        __sp = i;
        var a=f.createElement(g);
        a.type="text/javascript";
        a.async=1;
        a.src=("https:" == f.location.protocol ? "https://" : "http://") + h;
        var b = f.getElementsByTagName(g)[0];
        b.parentNode.insertBefore(a,b);
    })(window, document, "script", "salespanel.io/src/js/<?php echo esc_attr($sp_client_id); ?>/sp.js", "<?php echo esc_attr($sp_client_id); ?>");

</script>
<!-- Salespanel Web Tracking code -->

<?php
            }
        }

    }

    $salespanel_plugin = new SalespanelPlugin();
}
