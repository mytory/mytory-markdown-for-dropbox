<h2>Mytory Markdown for Dropbox <?php _e('Settings') ?></h2>

<form method="post" action="options.php" id="mm4d-form">
    <?php
    settings_fields('mm4d');
    do_settings_sections('mm4d');
    ?>
    <input type="hidden" name="access_token" value="<?= get_option('access_token') ?>">
    <input type="hidden" name="account_id" value="<?= get_option('account_id') ?>">
    <input type="hidden" name="token_type" value="<?= get_option('token_type') ?>">
    <input type="hidden" name="uid" value="<?= get_option('uid') ?>">

    <?php
    $query_string = http_build_query(array(
        'client_id' => get_option('app_key'),
        'redirect_uri' => menu_page_url('mm4d', false),
        'response_type' => 'token',
        'state' => wp_create_nonce('_dropbox_auth'),
    ));
    if (get_option('access_token')) { ?>
        <a class="button  button-primary" href="https://www.dropbox.com/oauth2/authorize?<?= $query_string ?>&force_reapprove=true"><?php _e('Auth Dropbox Again', 'mm4d') ?></a>
    <?php } else if (get_option('app_key') and get_option('app_secret')) { ?>
        <a class="button  button-primary" href="https://www.dropbox.com/oauth2/authorize?<?= $query_string ?>"><?php _e('Auth Dropbox', 'mm4d') ?></a>
    <?php } else { ?>
        <p class="description">
            <?php _e('get app key and app secret on <a target="_blank" href="https://www.dropbox.com/developers/apps/">your app page</a> in dropbox', 'mm4d') ?>
        </p>
        <p class="description">
            <?php _e('Please fill in the app key and the app secret to authenticate Dropbox.', 'mm4d') ?>
        </p>
    <?php } ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row">App key</th>
            <td>
                <input type="text" class="large-text" name="app_key" value="<?= get_option('app_key') ?>" title="app key">
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">App secret</th>
            <td>
                <input type="text" class="large-text" name="app_secret" value="<?= get_option('app_secret') ?>" title="app secret">
            </td>
        </tr>
    </table>

    <?php
    submit_button(null, 'primary', '');
    ?>
</form>