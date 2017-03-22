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

    public $error = array(
        'status' => false,
        'msg' => '',
    );
    private $markdown;

    function __construct()
    {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('add_meta_boxes', array($this, 'metaBox'));
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_action('wp_ajax_mm4d_verify_state_nonce', array($this, 'verifyStateNonce'));
        add_action('wp_ajax_mm4d_get_converted_content', array($this, 'getConvertedContent'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        $this->initMarkdown();
    }

    function init()
    {
        load_plugin_textdomain('mm4d', false, dirname(plugin_basename(__FILE__)) . '/lang');
    }

    function activate()
    {
        if (phpversion() >= '5.3') {
            update_option('markdown_engine', 'parsedown');
        } else {
            update_option('markdown_engine', 'markdownExtra');
        }
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
        // whitelist options
        if (!current_user_can('activate_plugins')) {
            return null;
        }
        register_setting('mm4d', 'access_token');
        register_setting('mm4d', 'markdown_engine');
    }

    function printSettingsPage()
    {
        $access_token = get_option('access_token');
        include 'settings.php';
    }

    function getConvertedContent()
    {
        $id = $_POST['id'];
        $response = array();
        $response['content'] = $this->convert($this->getFileContent($id));
        echo json_encode($response);
        die();
    }

    /**
     * get contents from path
     * @param  string $path file path or id or rev
     * @return string
     */
    private function getFileContent($path)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/download');
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        if (!ini_get('open_basedir')) {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . get_option('access_token'),
            'Dropbox-API-Arg: ' . json_encode(array(
                'path' => $path,
            )),
        ));
        $content = curl_exec($curl);

        if (!$this->checkCurlError($curl)) {
            return false;
        }

        return $content;
    }

    private function checkCurlError($curl)
    {
        $curl_info = curl_getinfo($curl);
        if ($curl_info['http_code'] != '200') {
            $this->error = array(
                'status' => true,
                'msg' => __('Network Error! HTTP STATUS is ', 'mm4d') . $curl_info['http_code'],
            );
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

    private function initMarkdown()
    {
        switch (get_option('markdown_engine')) {
            case 'parsedown':
                include 'MM4DParsedown.php';
                $this->markdown = new MM4DParsedown;
                break;
            case 'markdownExtra':
                include 'MM4DMarkdownExtra.php';
                $this->markdown = new MM4DMarkdownExtra;
                break;
            default:
                $this->markdown = new MM4DMarkdownExtra;
            // pass through
        }
    }

    private function convert($md_content)
    {
        return $this->markdown->convert($md_content);
    }

}

new MytoryMarkdownForDropbox;