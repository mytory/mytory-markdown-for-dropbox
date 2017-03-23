<style>
    .u-button-like-text {
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
        font-size: inherit;
    }
</style>

<input type="hidden" id="mm4d_access_token" value="<?= get_option('mm4d_access_token') ?>">

<table class="form-table">
    <tr>
        <th scope="row"><label for="mm4d-path"><?php _e('File', 'mm4d') ?></label></th>
        <td>
            <input type="button" class="button  js-open-dropbox-list"
                   value="<?php esc_attr_e(__('Select')) ?>" title="Dropbox">
            <input type="text" name="mm4d_path" id="mm4d-path" class="large-text" value="<?php echo $mm4d_path ?>">
        </td>
    </tr>
    <tr>
        <th><?php _e('Update') ?></th>
        <td>
            <button type="button" class="button js-update-content">
                <?php _e('Update Editor Content', 'mm4d') ?>
            </button>
        </td>
    </tr>
</table>

<div class="js-dropbox-list" data-remodal-id="modal">
    <button data-remodal-action="close" class="remodal-close"></button>
    <h1 class="js-dropbox-list-title">/</h1>

    <div class="js-dropbox-list-content"></div>
</div>

<script type="text/template" id="template-mm4d-li">
    <li class="<%- tag %>">
        <button class="u-button-like-text  <%- tag == 'folder' ? 'js-mm4d-change-directory' : 'js-mm4d-select' %>"
                data-id="<%- id %>" data-path="<%- path %>">
            <%- name %>
        </button>
    </li>
</script>

