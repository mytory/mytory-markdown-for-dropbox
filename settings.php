<?php
global $mytoryMarkdownForDropbox;
$is_legacy_php = ( phpversion() < '5.3' );
?>
<div class="wrap">
    <h2>Mytory Markdown for Dropbox <?php _e( 'Settings' ) ?></h2>

	<?php if ( ! empty( $message ) ) { ?>
        <p><?= $message ?></p>
	<?php } ?>

	<?php
	$query_string = http_build_query( array(
		'client_id'     => MYTORY_MARKDOWN_APP_KEY,
		'response_type' => 'code',
	) );
	?>
	<?php if ( get_option( 'mm4d_code' ) ) { ?>
        <p>
            <button type="button" class="button  js-mm4d-revoke">
				<?php _e( 'Revoke', 'mm4d' ) ?>
            </button>
        </p>
	<?php } else { ?>
        <p><?php _e( 'Get code from Dropbox and paste it. Click button below.', 'mm4d' ) ?></p>
        <p>
            <a target="_blank" class="button"
               href="https://www.dropbox.com/oauth2/authorize?<?= $query_string ?>">
				<?php _e( 'Get Dropbox Code', 'mm4d' ) ?>
            </a>
        </p>
	<?php } ?>

    <form method="post" action="options.php" id="mm4d-form">
		<?php
		settings_fields( 'mm4d' );
		do_settings_sections( 'mm4d' );
		foreach ( $this->optionKeys as $key ) {
			if ( in_array( $key, array( 'mm4d_code', 'mm4d_markdown_engine', 'extensions' ) ) ) {
				continue;
			}
			?>
            <input type="hidden" id="mm4d_<?= $key ?>" name="mm4d_<?= $key ?>"
                   value="<?= get_option( 'mm4d_' . $key ) ?>">
		<?php } ?>
        <table class="form-table" <?= ( get_option( 'mm4d_access_token' ) ) ? 'hidden' : '' ?>>
            <tr valign="top">
                <th scope="row">Code</th>
                <td>
                    <input type="text" class="js-mm4d-input  large-text" name="mm4d_code"
                           title="code" value="<?= get_option( 'mm4d_code' ) ?>">
                </td>
            </tr>
        </table>

		<?php if ( get_option( 'mm4d_access_token' ) ) { ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e( 'Markdown Engine', 'mm4d' ) ?></th>
                    <td>
                        <p>
                            <label>
                                <input type="radio" name="mm4d_markdown_engine" value="parsedown"
									<?= ( get_option( 'mm4d_markdown_engine' ) == 'parsedown' ) ? 'checked' : '' ?>
									<?= $is_legacy_php ? 'disabled' : '' ?>>
                                Parsedown
                            </label>
                        </p>

                        <p class="description">
							<?php
							if ( $is_legacy_php ) {
								echo sprintf( __( 'Your PHP version is %s, so cannot use Parsedown.' ), phpversion() );
								echo '<br>';
							} ?>
							<?php _e( 'GitHub Flavored.', 'mm4d' ) ?>
							<?php _e( 'PHP 5.3 or later.', 'mm4d' ) ?>
                            <a target="_blank" href="http://parsedown.org/">Website</a>
                        </p>

                        <hr>

                        <p>
                            <label>
                                <input type="radio" name="mm4d_markdown_engine" value="parsedownExtra"
									<?= ( get_option( 'mm4d_markdown_engine' ) == 'parsedownExtra' ) ? 'checked' : '' ?>
									<?= $is_legacy_php ? 'disabled' : '' ?>>
                                ParsedownExtra
                            </label>
                        </p>

                        <p class="description">
							<?php
							if ( $is_legacy_php ) {
								echo sprintf( __( 'Your PHP version is %s, so cannot use ParsedownExtra.' ), phpversion() );
								echo '<br>';
							} ?>
							<?php _e( 'An extension of Parsedown that adds support for Markdown Extra.', 'mm4d' ) ?>
							<?php _e( 'PHP 5.3 or later.', 'mm4d' ) ?>
                            <a target="_blank" href="http://parsedown.org/extra/">Website</a>
                        </p>

                        <hr>

                        <p>
                            <label>
                                <input type="radio" name="mm4d_markdown_engine" value="markdownExtra"
									<?= ( get_option( 'mm4d_markdown_engine' ) == 'markdownExtra' ) ? 'checked' : '' ?>>
                                php Markdown Extra classic version
                            </label>
                        </p>

                        <p class="description">
							<?php _e( 'It works with PHP 4.0.5 or later. <strong>This version is no longer supported since February 1, 2014.</strong>', 'mm4d' ) ?>
                            <a target="_blank" href="https://michelf.ca/projects/php-markdown/extra/">Website</a>
                        </p>

                        <hr>

						<?php if ( $mytoryMarkdownForDropbox->hasMultimarkdownExecution() ) { ?>
                            <p>
                                <label>
                                    <input type="radio" name="mm4d_markdown_engine" value="multimarkdown"
										<?= ( get_option( 'mm4d_markdown_engine' ) == 'multimarkdown' ) ? 'checked' : '' ?>>
                                    Multimarkdown 6
                                </label>
                            </p>

                            <p class="description">
                                Lightweight markup processor to produce HTML, LaTeX, and more.
                                <a href="https://fletcher.github.io/MultiMarkdown-6/">Website</a>
                                <br>
                                It's not a PHP library. So you must have the Multimarkdown commandline execution on your
                                server.
                            </p>

						<?php } else { ?>

                            <p>
                                <label>
                                    <input type="radio" name="mm4d_markdown_engine" value="multimarkdown" disabled>
                                    Multimarkdown 6
                                </label>
                            </p>

                            <p class="description">
								<?php _e( 'You can use the Muitimarkdown if you install it on your server.' ) ?>
                                (<a href="https://fletcher.github.io/MultiMarkdown-6/">Website</a>)
                            </p>
                            <ol>
                                <li>
                                    <p class="description">
										<?php _e( 'Multimarkdown option will be enabled if a <code>multimarkdown</code> execution is on OS PATH and web server can run it.' ) ?>
                                    </p>
                                </li>
                                <li>
                                    <p class="description">
										<?php _e( 'Otherwise, you can define <code>MYTORY_MARKDOWN_MULTIMARKDOWN_EXECUTION</code> constant on <code>wp-config.php</code>.' ) ?>
                                        <br>
										<?php _e( "ex) <code>define('MYTORY_MARKDOWN_MULTIMARKDOWN_EXECUTION', '/opt/multimarkdown/bin/multimarkdown');</code>" ) ?>
                                    </p>
                                </li>
                            </ol>

						<?php } ?>

                        <hr>

                        <p><?= sprintf( __( 'Your PHP version is %s', 'mm4d' ), phpversion() ) ?></p>

                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e( 'Markdown File Extensions', 'mm4d' ) ?></th>
                    <td>
                        <input class="large-text" type="text" name="mm4d_extensions"
                               value="<?= get_option( 'mm4d_extensions' ) ?>" title="markdown extensions">

                        <p class="description">
							<?php _e( 'e.g. txt,md,markdown,mdown', 'mm4d' ) ?>
                            <br>
							<?php _e( 'When selecting a file, only files with that extension are displayed in the list.', 'mm4d' ) ?>
							<?php _e( 'Spaces are not allowed.', 'mm4d' ) ?>
                        </p>
                    </td>
                </tr>
            </table>
		<?php } ?>

		<?php
		submit_button( null, 'primary', '' );
		?>
    </form>
</div>