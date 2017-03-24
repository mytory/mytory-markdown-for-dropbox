<?php global $wpdb ?>
<div class="wrap">
    <h2><?php _e('Migrate from Mytory Markdown to Mytory Markdown for Dropbox', 'mm4d') ?></h2>

    <?php
    if (!empty($message)) { ?>
        <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
            <p><strong><?= $message ?></strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Close.',
                        'mytory-markdown') ?></span></button>
        </div>
        <?php
        exit;
    } ?>

    <?php
    $help_file_path = dirname(__FILE__) . '/help/migrate-' . get_user_locale() . '.md';
    if (file_exists($help_file_path)) {
        $md_content = file_get_contents($help_file_path);
    } else {
        $md_content = file_get_contents(dirname(__FILE__) . '/help/migrate-en_US.md');
    }
    $help = $this->convert($md_content);
    echo $help['post_content'];

    // extract common substring in md path
    $results = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = 'mytory_md_path' AND meta_value != ''");
    $mytory_md_path_list = wp_list_pluck($results, 'meta_value');
    $mytory_md_path_list_raw = $mytory_md_path_list;

    foreach ($mytory_md_path_list as $i => $path) {
        $mytory_md_path_list[$i] = str_replace(array('http://', 'https://'), array('', ''), $path);
    }

    include_once 'extract-common-substring.php';
    $recommend_change_from = strCommonPrefixByStr($mytory_md_path_list);
    ?>

    <form method="post" class="js-form">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('URL corresponding to Dropbox folder', 'mm4d') ?></th>
                <td>
                    <input class="large-text" type="text" name="change_from" value="<?= $recommend_change_from ?>"
                           required title="<?php esc_attr_e(__('Change from')) ?>"/>
                    <?php if ($recommend_change_from) { ?>
                        <p>
                            <?php _e('Above is a string that extracted common substring from markdown paths.',
                                'mytory-markdown') ?>
                            <?php _e('Please edit appropriately and convert.', 'mm4d') ?>
                        </p>
                    <?php } ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Dropbox folder', 'mm4d') ?></th>
                <td>
                    <input class="large-text" type="text" name="change_to" value="/Public/"
                           required title="<?php esc_attr_e(__('Change to')) ?>"/>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary"
                   value="<?php _e('Convert', 'mytory-markdown') ?>">
        </p>
    </form>

    <div class="card" style="max-width: 100%;">
        <h3><?php _e('Reference: Your Markdown URL List', 'mytory-markdown') ?></h3>
        <ul>
            <?php foreach ($results as $result) { ?>
                <li>
                    <code><?= $result->meta_value ?></code>
                    <a target="_blank" href="<?= get_edit_post_link($result->post_id) ?>">
                        <?php _e('Edit') ?>
                    </a>
                    <?php
                    $mm4d_path = get_post_meta($result->post_id, '_mm4d_path', true);
                    if ($mm4d_path) { ?>
                        <br><?php _e('Result: ') ?><code><?= $mm4d_path ?></code>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>

<script type="text/javascript">
    jQuery(function ($) {
        $('.js-form').submit(function (e) {
            var $change_from = $('[name="change_from"]');
            var $change_to = $('[name="change_to"]');

            $change_from.val($.trim($change_from.val()));
            $change_to.val($.trim($change_to.val()));

            if ($change_from.val().substr(-1) !== '/') {
                $change_from.val($change_from.val() + '/');
            }
            if ($change_to.val().substr(-1) !== '/') {
                $change_to.val($change_to.val() + '/');
            }
            if ($change_to.val().substr(0, 1) !== '/') {
                $change_to.val('/' + $change_to.val());
            }
            if ($change_from.val().substr(0, 7) === 'http://') {
                $change_from.val($change_from.val().substr(7));
            }
            if ($change_from.val().substr(0, 8) === 'https://') {
                $change_from.val($change_from.val().substr(8));
            }
        });
    });
</script>