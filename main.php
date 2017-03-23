<?php

/*
Plugin Name: Mytory Markdown for Dropbox
Description: Link with Dropbox, select markdown file. Then, your content will be synced with markdown file in Dropbox. It's Cool.
Author: mytory
Version: 1.0.0
Author URI: https://mytory.net
*/

// If key and secret is set in wp-config.php, use it. Otherwise use default key and secret.
!defined('MYTORY_MARKDOWN_APP_KEY') and define('MYTORY_MARKDOWN_APP_KEY', '1y7djszzdziqchy');
!defined('MYTORY_MARKDOWN_APP_SECRET') and define('MYTORY_MARKDOWN_APP_SECRET', '3fxwz342stx7j0u');

class MytoryMarkdownForDropbox
{
    public $version = '1.0.0';
    public $error = array(
        'status' => false,
        'msg' => '',
        'is_error' => false,
    );
    public $optionKeys = array(
        'access_token',
        'token_type',
        'uid',
        'account_id',
        'markdown_engine',
        'code',
        'extensions',
    );
    private $markdown;

    function __construct()
    {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('add_meta_boxes', array($this, 'metaBox'));
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_action('wp_ajax_mm4d_get_converted_content', array($this, 'getConvertedContent'));
        add_action('wp_ajax_mm4d_delete_options', array($this, 'deleteOptions'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        $this->setDefaultOptions();
        $this->initMarkdownObject();
    }

    function init()
    {
        load_plugin_textdomain('mm4d', false, dirname(plugin_basename(__FILE__)) . '/lang');
    }

    function activate()
    {
        $this->setDefaultOptions();
    }

    function deleteOptions()
    {
        foreach ($this->optionKeys as $key) {
            delete_option('mm4d_' . $key);
        }
        die();
    }

    function adminEnqueueScripts($hook)
    {
        if (!in_array($hook, array('post.php', 'post-new.php', 'settings_page_mm4d'))) {
            return;
        }
        wp_enqueue_script('dropbox-sdk', 'https://unpkg.com/dropbox/dist/Dropbox-sdk.min.js', array(), null, true);
        wp_enqueue_script('remodal', plugins_url('js-lib/remodal/remodal.min.js', __FILE__), array(), null, true);
        wp_enqueue_style('remodal', plugins_url('js-lib/remodal/remodal.css', __FILE__));
        wp_enqueue_style('remodal-theme', plugins_url('js-lib/remodal/remodal-default-theme.css', __FILE__));
        wp_enqueue_script('mm4d-script', plugins_url('js/script.js', __FILE__),
            array('dropbox-sdk', 'underscore', 'remodal'),
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
        $mm4d_path = '';
        if (isset($_GET['post'])) {
            $mm4d_path = get_post_meta($_GET['post'], '_mm4d_path', true);
        }
        include 'meta-box.php';
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
        if (!current_user_can('activate_plugins')) {
            return null;
        }

        foreach ($this->optionKeys as $key) {
            register_setting('mm4d', 'mm4d_' . $key);
            register_setting('mm4d', 'mm4d_extensions', array(
                'sanitize_callback' => array($this, 'removeSpace')
            ));
        }
    }

    function removeSpace($string)
    {
        return preg_replace('/ +/', '', $string);
    }

    function printSettingsPage()
    {
        $access_token = get_option('mm4d_access_token');

        if (get_option('mm4d_code') && !$access_token) {
            $response = json_decode($this->getAccessTokenByCode());
            if ($this->error['is_error']) {
                $message = __('The code is invalid. ', 'mm4d') . $this->error['msg'];
                $message .= '<br>' . print_r($response, true);
            } else {
                update_option('mm4d_access_token', $response->access_token);
                update_option('mm4d_token_type', $response->token_type);
                update_option('mm4d_uid', $response->uid);
                update_option('mm4d_account_id', $response->account_id);
            }
        }

        include 'settings.php';
    }

    function getConvertedContent()
    {
        $id = $_POST['id'];
        if (!$content = $this->getFileContent($id)) {
            echo json_encode($this->error);
        } else {
            $response = $this->convert($content);
            echo json_encode($response);
        }
        die();
    }

    /**
     * get contents from path
     * @param  string $path file path or id or rev
     * @return string
     */
    private function getFileContent($path)
    {
        $endpoint = 'https://content.dropboxapi.com/2/files/download';
        $custom_header = array(
            'Dropbox-API-Arg: ' . json_encode(array(
                'path' => $path,
            )),
        );

        $content = $this->accessDropbox($endpoint, $custom_header);

        return $content;
    }

    private function checkCurlError($curl)
    {
        $curl_info = curl_getinfo($curl);
        if ($curl_info['http_code'] != '200') {
            $this->error['is_error'] = true;
            $this->error['status'] = true;
            $this->error['msg'] = __('Network Error! HTTP STATUS is ', 'mm4d') . $curl_info['http_code'];
            if ($curl_info['http_code'] == '404') {
                $this->error['msg'] = 'Incorrect URL. File not found.';
            }
            if ($curl_info['http_code'] == 0) {
                $this->error['msg'] = __('Network Error! Maybe, connection error.', 'mm4d');
            }
            $this->error['curl_info'] = $curl_info;
            return false;
        }
        return true;
    }

    private function initMarkdownObject()
    {
        switch (get_option('mm4d_markdown_engine')) {
            case 'parsedown':
                include 'MM4DParsedown.php';
                $this->markdown = new MM4DParsedown;
                break;
            case 'markdownExtra':
                include 'MM4DMarkdownExtra.php';
                $this->markdown = new MM4DMarkdownExtra;
                break;
            default:
                include 'MM4DMarkdownExtra.php';
                $this->markdown = new MM4DMarkdownExtra;
            // pass through
        }
    }

    private function convert($md_content)
    {
        $content = $this->markdown->convert($md_content);
        $post = array();
        $matches = array();
        preg_match('/<h1>(.*)<\/h1>/', $content, $matches);
        if (!empty($matches)) {
            $post['post_title'] = $matches[1];
        } else {
            $post['post_title'] = false;
        }
        $post['post_content'] = preg_replace('/<h1>(.*)<\/h1>/', '', $content, 1);

        return $post;
    }

    private function getAccessTokenByCode()
    {
        $response = $this->accessDropbox('https://api.dropboxapi.com/oauth2/token', array(
            'Content-Type: application/x-www-form-urlencoded'
        ), array(
            'code' => get_option('mm4d_code'),
            'grant_type' => 'authorization_code',
        ), array(
            CURLOPT_USERPWD => MYTORY_MARKDOWN_APP_KEY . ':' . MYTORY_MARKDOWN_APP_SECRET,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        ));
        return $response;
    }

    /**
     * @param $endpoint
     * @param array $post_data
     * @param array $custom_header
     * @param array $other_settings
     * @return bool|mixed
     */
    private function accessDropbox($endpoint, $custom_header = array(), $post_data = array(), $other_settings = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        if (!ini_get('open_basedir')) {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (!empty($other_settings)) {
            foreach ($other_settings as $key => $value) {
                curl_setopt($curl, $key, $value);
            }
        }

        if (!empty($post_data)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
        }

        if (get_option('mm4d_access_token')) {
            $custom_header[] = 'Authorization: Bearer ' . get_option('mm4d_access_token');
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $custom_header);

        $content = curl_exec($curl);
        $this->checkCurlError($curl);
        return $content;
    }

    private function setDefaultOptions()
    {
        if (!get_option('mm4d_markdown_engine')) {
            if (phpversion() >= '5.3') {
                update_option('mm4d_markdown_engine', 'parsedown');
            } else {
                update_option('mm4d_markdown_engine', 'markdownExtra');
            }
        }

        if (!get_option('mm4d_extensions')) {
            update_option('mm4d_extensions', 'txt,md,markdown,mdown');
        }
    }

}

new MytoryMarkdownForDropbox;