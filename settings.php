<?php
$is_legacy_php = (phpversion() < '5.3');
?>
<div class="wrap">
    <h2>Mytory Markdown for Dropbox <?php _e('Settings') ?></h2>

    <form method="post" action="options.php" id="mm4d-form">
        <?php
        settings_fields('mm4d');
        do_settings_sections('mm4d');
        ?>
        <input class="js-mm4d-input" type="hidden" name="access_token" value="<?= get_option('access_token') ?>">
        <input class="js-mm4d-input" type="hidden" name="account_id" value="<?= get_option('account_id') ?>">
        <input class="js-mm4d-input" type="hidden" name="token_type" value="<?= get_option('token_type') ?>">
        <input class="js-mm4d-input" type="hidden" name="uid" value="<?= get_option('uid') ?>">

        <?php
        $query_string = http_build_query(array(
            'client_id' => get_option('app_key'),
            'redirect_uri' => menu_page_url('mm4d', false),
            'response_type' => 'token',
            'state' => wp_create_nonce('_dropbox_auth'),
        ));
        if (get_option('access_token')) { ?>
            <a class="button  button-primary"
               href="https://www.dropbox.com/oauth2/authorize?<?= $query_string ?>&force_reapprove=true"><?php _e('Auth Dropbox Again',
                    'mm4d') ?></a>
        <?php } else {
            if (get_option('app_key') and get_option('app_secret')) { ?>
                <a class="button  button-primary"
                   href="https://www.dropbox.com/oauth2/authorize?<?= $query_string ?>"><?php _e('Auth Dropbox',
                        'mm4d') ?></a>
            <?php } else { ?>
                <p class="description">
                    <?php _e('get app key and app secret on <a target="_blank" href="https://www.dropbox.com/developers/apps/">your app page</a> in dropbox',
                        'mm4d') ?>
                </p>
                <p class="description">
                    <?php _e('Please fill in the app key and the app secret to authenticate Dropbox.', 'mm4d') ?>
                </p>
            <?php }
        } ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">App key</th>
                <td>
                    <input type="text" class="js-mm4d-input  large-text" name="app_key"
                           value="<?= get_option('app_key') ?>" title="app key">
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">App secret</th>
                <td>
                    <input type="text" class="js-mm4d-input  large-text" name="app_secret"
                           value="<?= get_option('app_secret') ?>" title="app secret">
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