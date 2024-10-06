<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Handle form submission
if ( isset( $_POST['renewai_update_api_keys'] ) ) {
    //Security check
    $nonce = ( isset( $_POST['renewai_api_key_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['renewai_api_key_nonce'] ) ) : '' );
    if ( !wp_verify_nonce( $nonce, 'renewai_update_api_keys' ) ) {
        wp_die( 'Security check failed' );
    }
    //Do you have permission to be here
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( 'You do not have sufficient permissions to access this page.' );
    }
    //Get the default provider
    $providers = array('openai');
    // Initialize update var
    $updated = false;
    //Loop through the providers and update the API keys
    foreach ( $providers as $provider ) {
        $key_field = "renewai_{$provider}_api_key";
        if ( isset( $_POST[$key_field] ) && $_POST[$key_field] !== str_repeat( '•', 32 ) ) {
            $new_key = sanitize_text_field( wp_unslash( $_POST[$key_field] ) );
            if ( !empty( $new_key ) ) {
                $this->update_api_key( $provider, $new_key );
                $updated = true;
            } else {
                // If the field is empty, delete the key
                delete_option( "renewai_{$provider}_api_key" );
                $updated = true;
            }
        }
    }
    //If the API keys were updated, display a message
    if ( $updated ) {
        add_settings_error(
            'renewai_messages',
            'renewai_message',
            __( 'API Keys updated successfully', 'renewai-post-creator' ),
            'updated'
        );
    }
}
// Fetch OpenAI Key - Free Version
$openai_api_key = $this->get_api_key( 'openai' );
?>

<div class="wrap renewai-pc-wrap">

  <?php 
// Include header template
include plugin_dir_path( __FILE__ ) . '../includes/header.php';
?>

  <?php 
settings_errors( 'renewai_messages' );
?>

  <form method="post" action="" class="renewai-pc-settings-form">
    <h1><?php 
esc_html_e( 'API Key Management', 'renewai-post-creator' );
?></h1>

    <?php 
wp_nonce_field( 'renewai_update_api_keys', 'renewai_api_key_nonce' );
?>

    <div class="renewai-pc-settings-section">
      <h2><?php 
esc_html_e( 'API Keys', 'renewai-post-creator' );
?></h2>
      <table class="renewai-pc-form-table">
        <tr>
          <th scope="row"><label for="renewai_openai_api_key"><?php 
esc_html_e( 'OpenAI API Key', 'renewai-post-creator' );
?></label></th>
          <td>
            <input type="text" name="renewai_openai_api_key" id="renewai_openai_api_key"
              value="<?php 
echo esc_attr( ( !empty( $openai_api_key ) ? str_repeat( '•', 32 ) : '' ) );
?>"
              class="regular-text" />
            <?php 
if ( !empty( $openai_api_key ) ) {
    ?>
              <button type="button" class="button" onclick="toggleApiKeyField('openai')"><?php 
    esc_html_e( 'Change API Key', 'renewai-post-creator' );
    ?></button>
            <?php 
}
?>
            <?php 
if ( rpc_fs()->is_not_paying() ) {
    echo '<div class="upgrade-notice">
              <h4>' . esc_html__( 'Upgrade now to add support for Anthropic, Google Gemini and Perplexity!', 'renewai-post-creator' ) . '</h4>';
    echo '<a href="' . esc_url( rpc_fs()->get_upgrade_url() ) . '">' . esc_html__( 'Upgrade Now!', 'renewai-post-creator' ) . '</a>';
    echo '</div>';
}
?>
          </td>
        </tr>
        <?php 
?>
      </table>
    </div>
    <?php 
submit_button( 'Update API Keys', 'primary', 'renewai_update_api_keys' );
?>
  </form>
</div>

<script>
  //Toggle the API key field
  function toggleApiKeyField(provider) {
    var fieldId = 'renewai_' + provider + '_api_key';
    var field = document.getElementById(fieldId);
    var button = field.nextElementSibling;

    if (field.value === '••••••••••••••••••••••••••••••••') {
      field.value = '';
      field.type = 'text';
      button.textContent = 'Cancel';
    } else {
      field.value = '••••••••••••••••••••••••••••••••';
      field.type = 'password';
      button.textContent = 'Change API Key';
    }
  }
</script>