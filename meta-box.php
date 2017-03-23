<input type="hidden" id="mm4d_access_token" value="<?= get_option('mm4d_access_token') ?>">
<input type="hidden" id="mm4d_extensions" value="<?= get_option('mm4d_extensions') ?>">

<?php if (!get_option('mm4d_access_token')) { ?>
    <p>
        <a href="<?php menu_page_url('mm4d') ?>">
            <?php _e('You need to sign in to Dropbox and grant this plugin access to your Dropbox files', 'mm4d') ?>
        </a>
    </p>
<?php } ?>

<table class="form-table">
    <tr>
        <th scope="row"><label for="mm4d-path"><?php _e('File', 'mm4d') ?></label></th>
        <td>
            <?php if (get_option('mm4d_access_token')) { ?>
                <input type="button" class="button  js-open-dropbox-list"
                       value="<?php esc_attr_e(__('Select')) ?>" title="Dropbox">
            <?php } ?>
            <input readonly type="text" name="_mm4d_path" id="mm4d-path" class="regular-text" value="<?= $mm4d_path ?>"
                   title="path">

            <input type="hidden" name="_mm4d_id" id="mm4d-id" class="large-text" value="<?= $mm4d_id ?>" title="id">
            <input type="hidden" name="_mm4d_rev" id="mm4d-rev" class="large-text" value="<?= $mm4d_rev ?>"
                   title="revision">
        </td>
    </tr>
    <?php if (get_option('mm4d_access_token')) { ?>
        <tr>
            <th><?php _e('Update') ?></th>
            <td>
                <button type="button" class="button js-mm4d-update">
                    <?php _e('Update Editor Content', 'mm4d') ?>
                </button>
            </td>
        </tr>
    <?php } ?>
</table>


<p>
    <?php _e('Please let me know about bugs, your ideas, etc!') ?>
    <a href="mailto:mail@mytory.net">Email</a>
    |
    <a target="_blank" href="https://twitter.com/mytory">Twitter</a>
    |
    <a target="_blank" href="https://github.com/mytory/mytory-markdown-for-dropbox/issues">GitHub</a>
</p>
<p>
    <a target="_blank" href="https://wordpress.org/support/plugin/mytory-markdown-for-dropbox/reviews/">
        <?php _e('Please Rate and Review', 'mm4d') ?>
    </a>
    |
    <a target="_blank"
       href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=QUWVEWJ3N7M4W&lc=GA&item_name=Mytory%20Markdown&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted">
        <?php _e('Donate', 'mm4d') ?>
    </a>
</p>


<div class="js-dropbox-list  dropbox-list" data-remodal-id="modal">
    <button data-remodal-action="close" class="remodal-close"></button>
    <h1 class="dropbox-list-title  js-dropbox-list-title">/</h1>

    <div class="dropbox-list-content  js-dropbox-list-content"></div>
</div>

<script type="text/template" id="template-mm4d-li">
    <li class="<%- tag %>">
        <button
            class="u-button-like-text  dropbox-item  <%- tag == 'folder' ? 'js-mm4d-change-directory' : 'js-mm4d-select-file' %>"
            data-id="<%- id %>" data-path="<%- path %>" data-rev="<%- rev %>">
            <% if (tag == 'folder') { %>
            <span class="dashicons dashicons-category"></span>
            <% } %>
            <% if (tag == 'file') { %>
            <span class="dashicons dashicons-media-text"></span>
            <% } %>

            <span class="js-name"><%- name %></span>
        </button>
    </li>
</script>

