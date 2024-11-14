<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
//Do you have permission to be here
if ( !current_user_can( 'manage_options' ) ) {
    wp_die( 'You do not have sufficient permissions to access this page.' );
}
// Initialize settings update flag
$settings_updated = false;
// Define default prompts
define( 'RENEWAI_PC_DEFAULT_PROMPT', 'You are a senior content writer tasked with creating blog posts from a list of keywords or ideas. Structure your content using HTML tags (h2 for main title, h3 for subheadings, and p for paragraphs) to ensure SEO-friendly formatting.' );
// Check if form is submitted
if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    // Update debug mode
    $debug_mode = ( isset( $_POST['renewai_debug_mode'] ) ? 1 : 0 );
    update_option( 'renewai_debug_mode', $debug_mode );
    //If a provider is set
    if ( isset( $_POST['renewai_api_provider'] ) ) {
        // Sanitize and update API provider
        $new_provider = sanitize_text_field( wp_unslash( $_POST['renewai_api_provider'] ) );
        $old_provider = get_option( 'renewai_api_provider', 'openai' );
        update_option( 'renewai_api_provider', $new_provider );
        $this->log_to_file( "API provider changed from {$old_provider} to {$new_provider}" );
        // Define minimum and maximum tokens
        $min_tokens = 1;
        // Minimum allowed value
        $max_tokens = 4096;
        // Maximum allowed value
        $default_tokens = 1000;
        // Default value
        // Handle OpenAI settings
        if ( $new_provider === 'openai' ) {
            $default_openai_model = 'gpt-4';
            $openai_model = ( isset( $_POST['renewai_openai_model'] ) ? sanitize_text_field( wp_unslash( $_POST['renewai_openai_model'] ) ) : $default_openai_model );
            update_option( 'renewai_openai_model', $openai_model );
            $this->log_to_file( "OpenAI Model updated to: " . $openai_model );
            $max_tokens_openai = ( isset( $_POST['renewai_openai_max_tokens'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['renewai_openai_max_tokens'] ) ) ) : $default_tokens );
            $safe_tokens_openai = max( $min_tokens, min( $max_tokens_openai, $max_tokens ) );
            update_option( 'renewai_openai_max_tokens', $safe_tokens_openai );
            $temperature_openai = ( isset( $_POST['renewai_openai_temperature'] ) ? $this->sanitize_temperature( sanitize_text_field( wp_unslash( $_POST['renewai_openai_temperature'] ) ) ) : 0.7 );
            // Default value
            update_option( 'renewai_openai_temperature', $temperature_openai );
        }
        // Update system prompts for OpenAI
        if ( isset( $_POST['renewai_openai_system_prompt'] ) ) {
            update_option( 'renewai_openai_system_prompt', sanitize_textarea_field( wp_unslash( $_POST['renewai_openai_system_prompt'] ) ) );
        }
        $user_id = get_current_user_id();
        // Handle newsletter subscription only if it's the first interaction
        if ( $this->get_first_interaction( $user_id ) ) {
            $this->handle_newsletter_subscription();
            // Set first interaction to false
            $this->set_first_interaction( false, $user_id );
        }
    }
    // Set flag to show settings updated message
    $settings_updated = true;
}
// Retrieve OpenAI Settings
$api_provider = get_option( 'renewai_api_provider', 'openai' );
$openai_api_key = $this->get_api_key( 'openai' );
$openai_model = get_option( 'renewai_openai_model', 'gpt-4' );
$openai_system_prompt = get_option( 'renewai_openai_system_prompt', RENEWAI_PC_DEFAULT_PROMPT );
$openai_models_free = $this->get_openai_models__free();
// Retrieve Debug Mode
$debug_mode = get_option( 'renewai_debug_mode', 0 );
// Get the current user's email
$current_user = wp_get_current_user();
$user_email = $current_user->user_email;
// Default OpenAI Provider
$providers = array('openai');
$any_key_set = false;
$available_providers = array();
foreach ( $providers as $provider ) {
    $api_key = $this->get_api_key( $provider );
    if ( !empty( $api_key ) ) {
        $any_key_set = true;
        if ( $provider === 'openai' ) {
            $available_providers[$provider] = 'OpenAI';
        } elseif ( $provider === 'gemini' ) {
            $available_providers[$provider] = 'Google Gemini';
        } else {
            $available_providers[$provider] = ucfirst( $provider );
        }
    }
}
?>

<div class="wrap renewai-pc-wrap">
  <div id="renewai-pc-custom-alert" class="renewai-pc-custom-alert" style="display: none;">
    <div class="renewai-pc-custom-alert-content">
      <p id="renewai-pc-custom-alert-message"></p>
      <button id="renewai-pc-custom-alert-close" class="button"><?php 
esc_html_e( 'Close', 'renewai-post-creator' );
?></button>
    </div>
  </div>

  <?php 
// Display settings updated message if applicable
if ( $settings_updated ) {
    ?>
    <div class="notice notice-success is-dismissible">
      <p><?php 
    esc_html_e( 'Settings saved successfully!', 'renewai-post-creator' );
    ?></p>
    </div>
  <?php 
}
?>
  <?php 
// Display active debug mode message if applicable
if ( $debug_mode ) {
    ?>
    <div class="notice notice-warning is-dismissible">
      <p><?php 
    esc_html_e( 'Debug mode is enabled for RenewAI Post Creator. For security, disable this on live sites.', 'renewai-post-creator' );
    ?></p>
    </div>
  <?php 
}
// Include header template
include plugin_dir_path( __FILE__ ) . '../includes/header.php';
?>

  <form method="post" action="" class="renewai-pc-settings-form">
    <h1>
      <h1><?php 
esc_html_e( 'API Provider and Prompt Settings', 'renewai-post-creator' );
?></h1>
    </h1>
    <!-- Existing API Settings Section -->
    <div class="renewai-pc-settings-section">
      <h2>API Settings</h2>
      <?php 
if ( $any_key_set ) {
    ?>
        <table class="renewai-pc-form-table">
          <tr>
            <th><label for="renewai_api_provider"><?php 
    esc_html_e( 'API Provider', 'renewai-post-creator' );
    ?></label></th>
            <td>
              <select id="renewai_api_provider" name="renewai_api_provider">
                <option value=""><?php 
    esc_html_e( 'Select Provider', 'renewai-post-creator' );
    ?></option>
                <?php 
    foreach ( $available_providers as $provider_key => $provider_name ) {
        echo '<option value="' . esc_attr( $provider_key ) . '" ' . selected( $api_provider, $provider_key, false ) . '>' . esc_html( $provider_name ) . '</option>';
    }
    ?>
              </select>
            </td>
          </tr>
        </table>
      <?php 
} else {
    ?>
        <p><?php 
    esc_html_e( 'No API keys are set. Please', 'renewai-post-creator' );
    ?> <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=renewai-post-creator-api-keys' ) );
    ?>"><?php 
    esc_html_e( 'add your API keys', 'renewai-post-creator' );
    ?></a> <?php 
    esc_html_e( 'to use the plugin.', 'renewai-post-creator' );
    ?></p>
      <?php 
}
?>
    </div>

    <div id="provider_settings" class="renewai-pc-settings-section" style="display: none;">
      <!-- OpenAI Settings -->
      <div id="openai_settings" class="provider-settings">
        <table class="renewai-pc-form-table">
          <tr class="top-align">
            <th><label for="renewai_openai_model"><?php 
esc_html_e( 'OpenAI Model', 'renewai-post-creator' );
?></label></th>
            <td>
              <select id="renewai_openai_model" name="renewai_openai_model">
                <?php 
$models_to_use = ( renewai_pc_fs()->can_use_premium_code__premium_only() ? $openai_models_premium : $openai_models_free );
foreach ( $models_to_use as $model ) {
    ?>
                  <option value="<?php 
    echo esc_attr( $model );
    ?>" <?php 
    selected( $openai_model, $model );
    ?>><?php 
    echo esc_html( $model );
    ?></option>
                <?php 
}
?>
              </select><br>
              <p class="renewai-pc-description">
                <strong><?php 
esc_html_e( 'Note:', 'renewai-post-creator' );
?></strong> <?php 
esc_html_e( 'Before using an OpenAI model, please review the', 'renewai-post-creator' );
?>
                <a href="<?php 
echo esc_url( $this->get_api_pricing_links()['openai'] );
?>" target="_blank" rel="noopener noreferrer">
                  <?php 
esc_html_e( 'API pricing details', 'renewai-post-creator' );
?>
                </a>
                <?php 
esc_html_e( 'to understand the associated usage costs.', 'renewai-post-creator' );
?>
              </p>
              <?php 
?>
              <?php 
//Upsell
if ( renewai_pc_fs()->is_not_paying() ) {
    echo '<div class="upgrade-notice">
                <h4>' . esc_html__( 'Need access to more models? Upgrade now to access all of the OpenAI models, unlock new features and support Perplexity, Anthropic and Google Gemini! ', 'renewai-post-creator' ) . '<a href="' . esc_url( renewai_pc_fs()->get_upgrade_url() ) . '">' . esc_html__( 'Upgrade Now!', 'renewai-post-creator' ) . '</a></h4>
                </div>';
}
?>
            </td>
          </tr>
          <tr>
            <th><label for="renewai_openai_max_tokens"><?php 
esc_html_e( 'Additional Options', 'renewai-post-creator' );
?></label></th>
            <td>
              <div style="display: flex; justify-content: space-between;">
                <div style="flex: 1;">
                  <label for="renewai_openai_max_tokens"><?php 
esc_html_e( 'Max Tokens', 'renewai-post-creator' );
?></label>
                  <input type="number" id="renewai_openai_max_tokens" name="renewai_openai_max_tokens"
                    value="<?php 
echo esc_attr( get_option( 'renewai_openai_max_tokens', '1000' ) );
?>"
                    min="1" max="4096" step="1" class="small-text"><br>
                  <p class="renewai-pc-description"><?php 
esc_html_e( 'Maximum number of tokens to generate (1-4096)', 'renewai-post-creator' );
?></p>
                </div>
                <div style="flex: 1;">
                  <label for="renewai_openai_temperature"><?php 
esc_html_e( 'Temperature', 'renewai-post-creator' );
?></label>
                  <input type="number" id="renewai_openai_temperature" name="renewai_openai_temperature"
                    value="<?php 
echo esc_attr( get_option( 'renewai_openai_temperature', '0.7' ) );
?>"
                    min="0" max="1" step="0.1" class="small-text"><br>
                  <p class="renewai-pc-description"><?php 
esc_html_e( 'Controls randomness (0-1, lower is more deterministic)', 'renewai-post-creator' );
?></p>
                </div>
              </div>
            </td>
          </tr>
          <tr class="top-align">
            <th><label for="renewai_openai_system_prompt"><?php 
esc_html_e( 'OpenAI System Prompt', 'renewai-post-creator' );
?></label></th>
            <td>
              <textarea id="renewai_openai_system_prompt" name="renewai_openai_system_prompt" rows="10" cols="50" class="large-text"><?php 
echo esc_textarea( wp_unslash( $openai_system_prompt ) );
?></textarea>
              <p class="renewai-pc-description"><?php 
esc_html_e( 'Enter the system prompt for OpenAI.', 'renewai-post-creator' );
?></p>

              <?php 
if ( renewai_pc_fs()->is_not_paying() ) {
    ?>
                <p><?php 
    esc_html_e( 'Note: After editing a prompt, be sure to click the Save Changes button at the bottom of the page', 'renewai-post-creator' );
    ?></p>
              <?php 
}
?>

              <?php 
?>
            </td>
          </tr>
        </table>
        <hr>
      </div>

      <?php 
?>
    </div>

    <!-- New Newsletter Opt-in Section -->
    <?php 
if ( $this->get_first_interaction() ) {
    ?>
      <div class="renewai-pc-settings-section">
        <h2><?php 
    esc_html_e( 'Stay Up to Date!', 'renewai-post-creator' );
    ?></h2>
        <p><?php 
    esc_html_e( 'Subscribe to our newsletter to stay up to date on new features, upcoming promotions and important news.', 'renewai-post-creator' );
    ?></p>
        <table class="renewai-pc-form-table">
          <tr>
            <th scope="row"><label for="renewai_newsletter_optin"><?php 
    esc_html_e( 'Subscribe to Newsletter', 'renewai-post-creator' );
    ?></label></th>
            <td>
              <input type="checkbox" id="renewai_newsletter_optin" name="renewai_newsletter_optin" value="1" checked>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="renewai_newsletter_email"><?php 
    esc_html_e( 'Email Address', 'renewai-post-creator' );
    ?></label></th>
            <td>
              <input type="email" id="renewai_newsletter_email" name="renewai_newsletter_email" value="<?php 
    echo esc_attr( $user_email );
    ?>" class="regular-text">
            </td>
          </tr>
        </table>
      </div>
    <?php 
}
?>
    <!-- Debug Settings -->
    <div class="renewai-pc-settings-section">
      <h2><?php 
esc_html_e( 'Debug Settings', 'renewai-post-creator' );
?></h2>
      <table class="renewai-pc-form-table">
        <tr>
          <th><label for="renewai_debug_mode"><?php 
esc_html_e( 'Enable Debug Mode', 'renewai-post-creator' );
?></label></th>
          <td>
            <input type="checkbox" id="renewai_debug_mode" name="renewai_debug_mode" value="1" <?php 
checked( $debug_mode, 1 );
?>>
            <span class="description"><?php 
esc_html_e( 'When enabled, debug information will be written to the log file.', 'renewai-post-creator' );
?></span>
            <?php 
if ( file_exists( RENEWAI_PC__PLUGIN_DIR . '/renewai-log.txt' ) ) {
    ?>
              <p>
                <?php 
    if ( $debug_mode ) {
        ?>
                  <a href="<?php 
        echo esc_url( RENEWAI_PC__PLUGIN_URL . '/renewai-log.txt' );
        ?>" target="_blank" class="button">View Log File</a>
                <?php 
    }
    ?>
                <button id="renewai-pc-delete-log-file" class="button">Delete Log File</button>
              </p>
            <?php 
} else {
    ?>
              <p><?php 
    esc_html_e( 'No log file exists. Enable debug mode to create one.', 'renewai-post-creator' );
    ?></p>
            <?php 
}
?>
          </td>
        </tr>
      </table>
    </div>

    <?php 
submit_button();
?>
  </form>
</div>