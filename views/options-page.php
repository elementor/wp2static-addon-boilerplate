<h2>Boilerplate Deployment Options</h2>

<form
    name="wp2static-boilerplate-save-options"
    method="POST"
    action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">

    <?php wp_nonce_field( $view['nonce_action'] ); ?>
    <input name="action" type="hidden" value="wp2static_boilerplate_save_options" />

<table class="widefat striped">
    <tbody>
        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['boilerplateAccountAPIKey']->name; ?>"
                ><?php echo $view['options']['boilerplateAccountAPIKey']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['boilerplateAccountAPIKey']->name; ?>"
                    name="<?php echo $view['options']['boilerplateAccountAPIKey']->name; ?>"
                    type="password"
                    value="<?php echo $view['options']['boilerplateAccountAPIKey']->value !== '' ?
                        \WP2Static\CoreOptions::encrypt_decrypt('decrypt', $view['options']['boilerplateAccountAPIKey']->value) :
                        ''; ?>"
                />
            </td>
        </tr>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['boilerplateStorageZoneName']->name; ?>"
                ><?php echo $view['options']['boilerplateStorageZoneName']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['boilerplateStorageZoneName']->name; ?>"
                    name="<?php echo $view['options']['boilerplateStorageZoneName']->name; ?>"
                    type="text"
                    value="<?php echo $view['options']['boilerplateStorageZoneName']->value !== '' ? $view['options']['boilerplateStorageZoneName']->value : ''; ?>"
                />
            </td>
        </tr>
    </tbody>
</table>

<br>

    <button class="button btn-primary">Save Boilerplate Options</button>
</form>

