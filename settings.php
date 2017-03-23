<?php
$is_legacy_php = (phpversion() < '5.3');
?>
<div class="wrap">
    <h2>Mytory Markdown for Dropbox <?php _e('Settings') ?></h2>

    <?php if (!empty($message)) { ?>
        <p><?= $message ?></p>
    <?php } ?>

    <?php
    $query_string = http_build_query(array(
        'client_id' => MYTORY_MARKDOWN_APP_KEY,
        'response_type' => 'code',
    ));
    ?>
    <p>
        <?php if (get_option('mm4d_code')) { ?>
            <button type="button" class="button  js-mm4d-revoke">
                <?php _e('Revoke', 'mm4d') ?>
            </button>
        <?php } else { ?>
            <a target="_blank" class="button  button-primary"
               href="https://www.dropbox.com/oauth2/authorize?<?= $query_string ?>">
                <?php _e('Get Dropbox Code', 'mm4d') ?>
            </a>
        <?php } ?>
    </p>

    <form method="post" action="options.php" id="mm4d-form">
        <?php
        settings_fields('mm4d');
        do_settings_sections('mm4d');
        foreach ($this->optionKeys as $key) {
            if (in_array($key, array('mm4d_code', 'mm4d_markdown_engine', 'extensions'))) { continue; }
            ?>
            <input type="hidden" id="mm4d_<?= $key ?>" name="mm4d_<?= $key ?>" value="<?= get_option('mm4d_' . $key) ?>">
        <?php } ?>
        <table class="form-table" <?= (get_option('mm4d_access_token')) ? 'hidden' : '' ?>>
            <tr valign="top">
                <th scope="row">Code</th>
                <td>
                    <input type="text" class="js-mm4d-input  large-text" name="mm4d_code"
                           title="code" value="<?= get_option('mm4d_code') ?>">
                </td>
            </tr>
        </table>

        <?php if (get_option('mm4d_access_token')) { ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" rowspan="2"><?php _e('Markdown Engine', 'mm4d') ?></th>
                    <td>
                        <label>
                            <input type="radio" name="mm4d_markdown_engine" value="parsedown"
                                <?= (get_option('mm4d_markdown_engine') == 'parsedown') ? 'checked' : '' ?>
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
                            <input type="radio" name="mm4d_markdown_engine" value="markdownExtra"
                                <?= (get_option('mm4d_markdown_engine') == 'markdownExtra') ? 'checked' : '' ?>>
                            php Markdown Extra classic version
                        </label>

                        <p class="description">
                            <?php _e('It works with PHP 4.0.5 or later. <strong>This version is no longer supported since February 1, 2014.</strong>') ?>
                            <a target="_blank" href="https://michelf.ca/projects/php-markdown/extra/">Website</a>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Markdown Extensions', 'mm4d') ?></th>
                    <td>
                        <input class="large-text" type="text" name="mm4d_extensions" value="<?= get_option('mm4d_extensions') ?>" title="markdown extensions">
                        <p class="description">
                            <?php _e('e.g. txt,md,markdown,mdown', 'mm4d') ?>
                            <br>
                            <?php _e('Space is not allowed.', 'mm4d') ?>
                        </p>
                    </td>
                </tr>
            </table>
        <?php } ?>

        <?php
        submit_button(null, 'primary', '');
        ?>
    </form>
</div>