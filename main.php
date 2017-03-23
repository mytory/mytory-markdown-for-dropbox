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
        add_action('save_post', array($this, 'savePost'));
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        add_action('wp_ajax_mm4d_get_converted_content', array($this, 'getConvertedContent'));
        add_action('wp_ajax_mm4d_delete_options', array($this, 'deleteOptions'));
        add_action('wp_ajax_mm4d_update_in_client', array($this, 'updateInClient'));
        register_activation_hook(__FILE__, array($this, 'activate'));

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array($this, 'pluginSettingPage'));
        add_action('admin_bar_menu', array($this, 'addAdminBarMenu'), 90);

        $this->setDefaultOptions();
        $this->initMarkdownObject();
    }

    function init()
    {
        load_plugin_textdomain('mm4d', false, 'lang');
    }

    function pluginSettingPage($links)
    {
        $settings_link = sprintf('<a href="%s">%s</a>', menu_page_url('mm4d', false), __('Settings'));
        array_unshift($links, $settings_link);
        return $links;
    }

    function addAdminBarMenu($wp_admin_bar)
    {
        global $wp_the_query;

        if (!is_admin()) {
            // 클라이언트단인 경우
            $current_object = $wp_the_query->get_queried_object();

            if (empty($current_object)) {
                // 글이나 목록을 보는 게 아닌 경우
                return;
            }

            if (!empty($current_object->post_type)
                && ($post_type_object = get_post_type_object($current_object->post_type))
                && current_user_can('edit_post', $current_object->ID)
                && $post_type_object->show_in_admin_bar
            ) {
                // 개별 글인 경우
                $wp_admin_bar->add_menu(array(
                    'id' => 'mm4d-update',
                    'title' => 'Markdown ' . __('Update'),
                    'href' => admin_url('admin-ajax.php'),
                    'meta' => array(
                        'rel' => $current_object->ID,
                    )
                ));
            }
        }
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

    function savePost($post_id)
    {
        if (!current_user_can('edit_post', $post_id)) {
            return null;
        }

        // 데이터 저장
        if (isset($_POST['_mm4d_path'])) {
            update_post_meta($post_id, '_mm4d_path', $_POST['_mm4d_path']);
            update_post_meta($post_id, '_mm4d_id', $_POST['_mm4d_id']);
            update_post_meta($post_id, '_mm4d_rev', $_POST['_mm4d_rev']);
        }
    }

    function updateInClient()
    {
        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);

        if (!current_user_can('edit_post', $post_id)) {
            return null;
        }

        $mm4d_id = get_post_meta($post_id, '_mm4d_id', true);
        $mm4d_path = get_post_meta($post_id, '_mm4d_path', true);
        $mm4d_rev = get_post_meta($post_id, '_mm4d_rev', true);

        $metadata = json_decode($this->accessDropbox('https://api.dropboxapi.com/2/files/get_metadata', array(
            "Content-Type: application/json",
        ), array(
            "path" => $mm4d_path,
            "include_media_info" => false,
            "include_deleted" => false,
            "include_has_explicit_shared_members" => false
        )));

        if (!empty($metadata->error_summary)) {
            echo json_encode(array(
                'result' => 'fail',
                'message' => $metadata->error_summary,
            ));
            die();
        } elseif ($metadata->rev == $mm4d_rev) {
            echo json_encode(array(
                'result' => 'fail',
                'message' => __('No change.', 'mm4d'),
            ));
            die();
        }

        $content = $this->accessDropbox('https://content.dropboxapi.com/2/files/download', array(
            'Dropbox-API-Arg: ' . json_encode(array(
                'path' => ($mm4d_id ? $mm4d_id : $mm4d_path)
            ))
        ));

        if (!is_null(json_decode($content))) {
            $content = json_decode($content);
        }

        if (!empty($content->error_summary)) {
            echo json_encode(array(
                'result' => 'fail',
                'message' => $content->error_summary,
            ));
        } else {

            update_post_meta($post_id, '_mm4d_rev', $metadata->rev);
            update_post_meta($post_id, '_mm4d_path', $metadata->path_display);

            $postarr = $this->convert($content);
            $postarr['ID'] = $post_id;
            $result = wp_update_post($postarr, true);

            if (is_wp_error($result)) {
                echo json_encode(array(
                    'result' => 'fail',
                    'message' => $result->get_error_message(),
                    'postarr' => $postarr
                ));
            } else {
                echo json_encode(array(
                    'result' => 'success',
                    'postarr' => $postarr
                ));
            }

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
        wp_enqueue_script('mm4d-script', plugins_url('js/admin.js', __FILE__),
            array('dropbox-sdk', 'underscore', 'remodal'),
            $this->version, true);
        wp_enqueue_style('mm4d-style', plugins_url('style.css', __FILE__));
    }

    function enqueueScripts()
    {
        if ((is_single() or is_page()) and current_user_can('edit_post', get_the_ID())) {
            wp_enqueue_script('mm4d-client', plugins_url('js/client.js', __FILE__),
                array('jquery'),
                $this->version, true);
        }
    }

    function metaBox()
    {
        add_meta_box(
            'mm4d',
            'Mytory Markdown for Dropbox',
            array($this, 'metaBoxInner')
        );
    }

    function metaBoxInner()
    {
        $mm4d_path = '';
        $mm4d_id = '';
        $mm4d_rev = '';
        if (isset($_GET['post'])) {
            $mm4d_path = get_post_meta($_GET['post'], '_mm4d_path', true);
            $mm4d_id = get_post_meta($_GET['post'], '_mm4d_id', true);
            $mm4d_rev = get_post_meta($_GET['post'], '_mm4d_rev', true);
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
            $post['post_title'] = html_entity_decode($matches[1], ENT_QUOTES, 'utf-8');
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
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
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
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
        }

        if (get_option('mm4d_access_token')) {
            array_unshift($custom_header, 'Authorization: Bearer ' . get_option('mm4d_access_token'));
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