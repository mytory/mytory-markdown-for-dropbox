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

    function __construct()
    {
        add_action('plugins_loaded', array(&$this, 'init'));
        add_action('add_meta_boxes', array(&$this, 'metaBox'));
        add_action('save_post', array(&$this, 'savePost'));
        add_action('wp_ajax_mm4d_convert_from_dropbox', array(&$this, 'convertFromDropbox'));
        add_action('admin_menu', array(&$this, 'addMenu'));
        add_action('admin_init', array(&$this, 'registerSettings'));
        add_action('admin_enqueue_scripts', array(&$this, 'enqueueScripts'));
    }

    function init()
    {
        load_plugin_textdomain('mm4d', false, dirname(plugin_basename(__FILE__)) . '/lang');
    }

    function enqueueScripts()
    {

    }

    function metaBox()
    {

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
            array(&$this, 'printSettingsPage'));
    }

    function registerSettings()
    {
        // whitelist options
        if (!current_user_can('activate_plugins')) {
            return null;
        }
        register_setting('mm4d', 'api_key');
        register_setting('mm4d', 'secret_key');
    }

    function printSettingsPage()
    {
        include 'settings.php';
    }

}

new MytoryMarkdownForDropbox;