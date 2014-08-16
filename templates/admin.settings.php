<div class="wrap">
    <h2><?php _e( 'Chatwing settings', CW_TEXTDOMAIN ); ?></h2>

    <div class="liquid-wrap">
        <h3><?php _e( 'Chatboxes', CW_TEXTDOMAIN ); ?> </h3>
        <table class="widefat fixed">
            <thead>
            <tr>
                <th width="5%"><?php _e( 'ID', CW_TEXTDOMAIN ) ?></th>
                <th width="15%"><?php _e( 'Name', CW_TEXTDOMAIN ) ?></th>
                <th width="15%"><?php _e( 'Alias', CW_TEXTDOMAIN ) ?></th>
                <th><?php _e( 'Key', CW_TEXTDOMAIN ) ?></th>
                <th width="20%">&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ( isset( $boxes ) && ! empty( $boxes ) ) {
                foreach ( $boxes as $box ) {
                    ?>
                    <tr>
                        <td><?php echo $box['id']; ?></td>
                        <td><?php echo $box['name']; ?></td>
                        <td><?php echo $box['alias']; ?></td>
                        <td><?php echo $box['key'] ?></td>
                    </tr>
                <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="4">
                        <?php _e( 'No box found', CW_TEXTDOMAIN ); ?>
                    </td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>

        <h3><?php _e( 'Plugin configuration', CW_TEXTDOMAIN ); ?></h3>
        <script type="text/javascript">
            var updateToken = function () {
                var isDelete = false, message;
                if(tokenForm.cw_token.value.length == 0) {
                    isDelete = true;
                    message = '<?php _e("Leaving the input empty will delete current token key. After that you will have to insert new token in order to continue using this plugin. Are you sure to do this action ?", CW_TEXTDOMAIN); ?>';
                } else {
                    message = '<?php _e( "Are you sure to change to new token ? ", CW_TEXTDOMAIN ) ?>';
                }

                if (confirm(message)) {
                    if(isDelete) {
                        tokenForm.task.value = 'delete';
                    }
                    tokenForm.submit();
                    return true;
                }
                return false;
            }
        </script>
        <form id="tokenForm" action="<?php echo admin_url( 'admin.php?noheader=true' ) ?>" method="post">
            <input type="hidden" name="action" value="cw_token_save" />
            <input type="hidden" name="task" value="update">
            <?php wp_nonce_field( 'cw-token-update' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="new_token"><?php _e( 'New token', 'chatwing' ) ?></label></th>
                    <td>
                        <input type="text" class="code regular-text" name="cw_token" value="" id="new_token" />
                        <input type="submit" class="button-secondary" name="deletetoken" onclick="return updateToken();" value="<?php _e( 'Update token' ) ?>" />
                    </td>
                </tr>                
            </table>            
        </form>

        <h3><?php _e('Chatbox settings'); ?></h3>
        <?php 
        $cw_options = get_option( '__cw_options', array('width' => 640, 'height' => 480));
         ?>
        <form action="<?php echo admin_url( 'admin.php?noheader=true' ); ?>" method="post">
            <input type="hidden" name="action" value="cw_setting_save" />
            <?php wp_nonce_field( 'cw-settings-update' ); ?>
            <table class="form-table">
                <tr>
                    <td><label for="width"><?php _e( 'Width', CW_TEXTDOMAIN ) ?></label></td>
                    <td>
                        <input type="text" class="code" name="width" value="<?php echo $cw_options['width']; ?>" placeholder="<?php echo $cw_options['width'] ?>"> (in pixel)
                    </td>
                </tr>
                <tr>
                    <td><label for="height"><?php _e( 'Height', CW_TEXTDOMAIN ); ?></label></td>
                    <td>
                        <input type="text" class="code" name="height" value="<?php echo $cw_options['height']; ?>" placeholder="<?php echo $cw_options['height']; ?>"/> (in pixel)
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary" name="submit" value="<?php _e('Save settings'); ?>">
            </p>
        </form>
    </div>
</div>