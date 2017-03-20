<table class="form-table">
    <tr class="js-mode  js-mode--url  hidden">
        <th scope="row"><label for="mytory-md-path">URL</label></th>
        <td>
            <input type="url" name="mytory_md_path" id="mytory-md-path" class="large-text" value="<?php echo $md_path ?>">
        </td>
    </tr>
    <tr class="js-mode js-mode--url hidden">
        <th><?php _e('Update', 'mm4d') ?></th>
        <td>
            <button type="button" class="button js-update-content"><?php _e('Update Editor Content',
                    'mm4d') ?></button>
        </td>
    </tr>
</table>
