<div class="wrap">
    <?php
    $message = CW_Session::getInstance()->get( 'message' );
    if (! $message) {
        $message = array(
            'content' => __( 'Please input your ChatWing token first!', CW_TEXTDOMAIN ),
            'type' => 'error'
        );
    }
    
    if(is_string($message)) {
        $message = array('content' => $message, 'type' => 'updated fade');
    }

    ?>
    <div id="message" class="<?php echo $message['type'] ?>">
        <p><?php echo $message['content']; ?></p>
    </div>

    
    <div class="liquid-wrap">
        <div class="liquid-left">
            <div class="panel-left">
                <form action="<?php echo admin_url( 'admin.php?noheader=true' ); ?>" method="post">
                    <input type="hidden" name="action" value="cw_token_save" />
                    <input type="hidden" name="task" value="add">
                    <?php  wp_nonce_field('cw-token-update'); ?>
                    <h3><?php _e( 'Plugin Configuration', CW_TEXTDOMAIN ) ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="token"><?php _e( 'Token', CW_TEXTDOMAIN ) ?></label></th>
                            <td>
                                <input type="text" class="code regular-text" name="cw_token" value="" id="token" />
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button-primary" name="save" value="<?php _e(
                            'Save Changes',
                            CW_TEXTDOMAIN
                        ); ?>" />
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>


