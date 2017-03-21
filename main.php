<?php

/*
Plugin Name: Mytory Markdown for Dropbox
Description: Link with Dropbox, select markdown file. Then, your content will be synced with markdown file in Dropbox. It's Cool.
Author: mytory
Version: 1.0.0
Author URI: https://mytory.net
*/

class MytoryMarkdownForDropbox
{
    public $version = '1.0.0';
    public $authKeys = array(
        'access_token',
        'account_id',
        'state',
        'token_type',
        'uid',
    );

    function __construct()
    {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('add_meta_boxes', array($this, 'metaBox'));
        add_action('save_post', array($this, 'savePost'));
        add_action('wp_ajax_mm4d_convert_from_dropbox', array($this, 'convertFromDropbox'));
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_action('wp_ajax_mm4d_verify_state_nonce', array($this, 'verifyStateNonce'));
    }

    function init()
    {
        load_plugin_textdomain('mm4d', false, dirname(plugin_basename(__FILE__)) . '/lang');
    }

    function adminEnqueueScripts($hook)
    {
        if (!in_array($hook, array('post.php', 'post-new.php', 'settings_page_mm4d'))) {
            return;
        }
        wp_enqueue_script('dropbox-sdk', 'https://unpkg.com/dropbox/dist/Dropbox-sdk.min.js', array(), null, true);
        wp_enqueue_script('mm4d-script', plugins_url('js/script.js', __FILE__), array('dropbox-sdk', 'underscore'),
            $this->version, true);
    }

    function metaBox()
    {
        add_meta_box(
            'mm4d',
            __('Markdown File', 'mm4d'),
            array($this, 'metaBoxInner')
        );
    }

    function metaBoxInner()
    {
        $md_path = '';
        if (isset($_GET['post'])) {
            $md_path = get_post_meta($_GET['post'], 'mytory_md_path', true);
        }
        include 'meta-box.php';
    }


    function savePost()
    {

    }

    function convertFromDropbox()
    {

    }

    function addMenu()
    {
        if (!current_user_can('activate_plugins')) {
            return null;
        }
        add_submenu_page('options-general.php', 'Mytory Markdown for Dropbox: ' . __('Settings', 'mm4d'),
            'Mytory Markdown for Dropbox: <span style="white-space: nowrap;">' . __('Settings', 'mm4d') . '</span>',
            'activate_plugins', 'mm4d',
            array($this, 'printSettingsPage'));
    }

    function registerSettings()
    {
        // whitelist options
        if (!current_user_can('activate_plugins')) {
            return null;
        }
        register_setting('mm4d', 'app_key');
        register_setting('mm4d', 'app_secret');
        register_setting('mm4d', 'access_token');
        foreach ($this->authKeys as $key) {
            if ($key === 'state') { continue; }
            register_setting('mm4d', $key);
        }

    }

    function printSettingsPage()
    {
        $secret_key = get_option('secret_key');
        $app_key = get_option('app_key');
        $access_token = get_option('access_token');
        include 'settings.php';
    }

    /**
     * verify 'state' from Dropbox. This is nonce of Dropbox.
     */
    function verifyStateNonce()
    {
        if (!wp_verify_nonce($_POST['state'], '_dropbox_auth')) {
            echo 'fail';
        } else {
            echo 'pass';
        }
        die();
    }

    /**
     * @return bool
     */
    private function hasAuthKeys()
    {
        $hasAuthKeys = true;

        foreach ($this->authKeys as $key) {
            if (empty($_POST[$key])) {
                $hasAuthKeys = false;
            }
        }
        return $hasAuthKeys;
    }

}

new MytoryMarkdownForDropbox;