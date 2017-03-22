<?php
$is_legacy_php = (phpversion() < '5.3');
?>
<div class="wrap">
    <h2>Mytory Markdown for Dropbox <?php _e('Settings') ?></h2>

    <?php if (!empty($message)) { ?>
        <p><?= $message ?></p>
    <?php } ?>

    <ul>
        <li>code: <?= get_option('code') ?></li>
        <li>access_token: <?= get_option('access_token') ?></li>
    </ul>

    <form method="post" action="https://api.dropboxapi.com/oauth2/token">
        <input type="text" name="code" value="<?= get_option('code') ?>">
        <input type="text" name="grant_type" value="authorization_code">
        <input type="submit" value="ok">
    </form>

    <form method="post" action="options.php" id="mm4d-form">
        <?php
        settings_fields('mm4d');
        do_settings_sections('mm4d');

        $query_string = http_build_query(array(
            'client_id' => (defined('MYTORY_MARKDOWN_APP_KEY') ? MYTORY_MARKDOWN_APP_KEY : '1y7djszzdziqchy'),
            'response_type' => 'code',
        ));
        ?>
        <p>
            <?php if (get_option('code')) { ?>
                <a target="_blank" class="button  button-primary"
                   href="https://www.dropbox.com/oauth2/authorize?<?= $query_string ?>&force_reapprove=true">
                    <?php _e('Get Dropbox Code Again', 'mm4d') ?>
                </a>
                |
                <a target="_blank" target="_blank" href="https://www.dropbox.com/account/security#apps"
                   title="<?php esc_attr_e(__('Go to Dropbox Setting page and revoke.', 'mm4d')) ?>">
                    <?php _e('to Revoke Authentication', 'mm4d') ?>
                </a>
            <?php } else { ?>
                <a target="_blank" class="button  button-primary"
                   href="https://www.dropbox.com/oauth2/authorize?<?= $query_string ?>">
                    <?php _e('Get Dropbox Code', 'mm4d') ?>
                </a>
            <?php } ?>
        </p>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">Code</th>
                <td>
                    <input type="text" class="js-mm4d-input  large-text" name="code"
                           value="<?= get_option('code') ?>" title="code">
                </td>
            </tr>
        </table>

        <table class="form-table">
            <tr valign="top">
                <th scope="row" rowspan="2"><?php _e('Markdown Engine', 'mm4d') ?></th>
                <td>
                    <label>
                        <input type="radio" name="markdown_engine" value="parsedown"
                            <?= (get_option('markdown_engine') == 'parsedown') ? 'checked' : '' ?>
                            <?= $is_legacy_php ? 'disabled' : '' ?>>
                        Parsedown
                    </label>

                    <p class="description">
                        <?php
                        if ($is_legacy_php) {
                            echo sprintf(__('Your PHP version is %s, so cannot use Parsedown.'), phpversion());
                            echo '<br>';
                        } ?>
                        <?php _e('GitHub Flavored.', 'mm4d') ?>
                        <?php _e('PHP 5.3 or later.', 'mm4d') ?>
                        <a target="_blank" href="http://parsedown.org/">Website</a>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <td>
                    <label>
                        <input type="radio" name="markdown_engine" value="markdownExtra"
                            <?= (get_option('markdown_engine') == 'markdownExtra') ? 'checked' : '' ?>>
                        php Markdown Extra classic version
                    </label>

                    <p class="description">
                        <?php _e('It works with PHP 4.0.5 or later. <strong>This version is no longer supported since February 1, 2014.</strong>') ?>
                        <a target="_blank" href="https://michelf.ca/projects/php-markdown/extra/">Website</a>
                    </p>
                </td>
            </tr>
        </table>

        <?php
        submit_button(null, 'primary', '');
        ?>
        <p style="text-align: right;">
            <button type="button" class="js-remove-settings  button"><?php _e('Remove Settings', 'mm4d') ?></button>
        </p>
    </form>
</div>