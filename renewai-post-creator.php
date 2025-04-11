<?php

/**
 * Plugin Name: RenewAI Post Creator
 * Description: Generate high-quality blog post content using AI models from OpenAI, with premium features for Anthropic, Google Gemini and Perplexity.
 * Version: 1.3.6
 * Author: Derek Jubach
 * Author URI:  https://github.com/derekjubach/RenewAI-Post-Creator-Free
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}
// Directory and URL Constants
if (!defined('RENEWAI_PC__PLUGIN_DIR')) {
  define('RENEWAI_PC__PLUGIN_DIR', dirname(__FILE__));
  define('RENEWAI_PC__PLUGIN_URL', plugins_url(plugin_basename(RENEWAI_PC__PLUGIN_DIR)));
}
// Directory and URL Constants
if (!defined('RENEWAI_PC__NEWSLETTER_ADDR')) {
  define('RENEWAI_PC__NEWSLETTER_ADDR', 'success@perpetuaiconsult.com');
}
if (function_exists('renewai_pc_fs')) {
  renewai_pc_fs()->set_basename(false, __FILE__);
} else {
  // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
  if (!function_exists('renewai_pc_fs')) {
    // Create a helper function for easy SDK access.
    function renewai_pc_fs()
    {
      global $renewai_pc_fs;
      if (!isset($renewai_pc_fs)) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/vendor/freemius/wordpress-sdk/start.php';
        $renewai_pc_fs = fs_dynamic_init(array(
          'id'             => '16636',
          'slug'           => 'renewai-post-creator',
          'type'           => 'plugin',
          'public_key'     => 'pk_899a309cacb0cfaf58d7f169b0fcf',
          'is_premium'     => false,
          'premium_suffix' => 'RenewAI Post Creator Premium',
          'has_addons'     => false,
          'has_paid_plans' => true,
          'menu'           => array(
            'slug' => 'renewai-post-creator-settings',
          ),
          'is_live'        => true,
        ));
      }
      return $renewai_pc_fs;
    }

    // Init Freemius.
    renewai_pc_fs();
    // Signal that SDK was initiated.
    do_action('renewai_pc_fs_loaded');
  }
  class RenewAI_Post_Creator
  {
    public function __construct()
    {
      // Hook into WordPress actions
      add_action('admin_menu', array($this, 'add_settings_page'));
      add_action('add_meta_boxes', array($this, 'add_meta_box'));
      add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
      add_action('wp_ajax_generate_post_content', array($this, 'generate_post_content'));
      add_action('wp_ajax_delete_renewai_log_file', array($this, 'delete_log_file'));
      add_action('admin_init', array($this, 'register_settings'));
      add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_action_links'));
    }

    /**
     * Register Settings
     */
    public function register_settings()
    {
      register_setting('renewai_settings', 'renewai_prompt_library', array($this, 'sanitize_settings'));
      // Define provider arrays
      $providers_free = array('openai');
      $providers_premium = array(
        'openai',
        'anthropic',
        'perplexity',
        'gemini'
      );
      // Choose which array to use
      $providers_to_use = (renewai_pc_fs()->can_use_premium_code__premium_only() ? $providers_premium : $providers_free);
      // Register settings for each provider
      foreach ($providers_to_use as $provider) {
        register_setting('renewai_settings', "renewai_{$provider}_max_tokens", 'intval');
        register_setting('renewai_settings', "renewai_{$provider}_temperature", array($this, 'sanitize_temperature'));
      }
    }

    /**
     * Add settings page and submenus to WordPress admin
     */
    public function add_settings_page()
    {
      $parent_slug = 'renewai-post-creator-settings';
      add_menu_page(
        'RenewAI Post Creator',
        'RenewAI Post Creator',
        'manage_options',
        $parent_slug,
        array($this, 'render_settings_page'),
        'dashicons-welcome-write-blog'
      );
      add_submenu_page(
        $parent_slug,
        'Settings',
        'Settings',
        'manage_options',
        $parent_slug
      );
      add_submenu_page(
        $parent_slug,
        'API Keys',
        'API Keys',
        'manage_options',
        'renewai-post-creator-api-keys',
        array($this, 'render_api_keys_page')
      );
      add_submenu_page(
        $parent_slug,
        'Help',
        'Help',
        'manage_options',
        'renewai-post-creator-help',
        array($this, 'render_help_page')
      );
    }

    /**
     * Render the main settings page
     */
    public function render_settings_page()
    {
      if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
          'message' => 'You do not have permission to access this page.',
        ));
        return;
      }
      $current_user = wp_get_current_user();
      $user_email = $current_user->user_email;
      include RENEWAI_PC__PLUGIN_DIR . '/pages/settings-page.php';
    }

    /**
     * Render the API keys page
     */
    public function render_api_keys_page()
    {
      if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
          'message' => 'You do not have permission to access this page.',
        ));
        return;
      }
      include RENEWAI_PC__PLUGIN_DIR . '/pages/api-keys.php';
    }

    /**
     * Render the help page
     */
    public function render_help_page()
    {
      if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
          'message' => 'You do not have permission to access this page.',
        ));
        return;
      }
      include RENEWAI_PC__PLUGIN_DIR . '/pages/help-page.php';
    }

    /**
     * Enqueue scripts and styles for the plugin
     */
    public function enqueue_scripts($hook)
    {
      // Enqueue styles for both classic and Gutenberg editor
      wp_enqueue_style(
        'renewai-admin-styles',
        RENEWAI_PC__PLUGIN_URL . '/assets/css/renewai-styles.css',
        array(),
        '1.0'
      );
      // Determine if Gutenberg is being used
      $is_gutenberg = function_exists('use_block_editor_for_post') && use_block_editor_for_post(get_post());
      // Enqueue script for both classic and Gutenberg editor
      wp_enqueue_script(
        'renewai-script',
        RENEWAI_PC__PLUGIN_URL . '/assets/js/app.js',
        ($is_gutenberg ? array(
          'jquery',
          'wp-blocks',
          'wp-element',
          'wp-editor',
          'wp-components',
          'wp-i18n',
          'wp-data'
        ) : array('jquery')),
        '1.0',
        true
      );
      // Localize script
      wp_localize_script('renewai-script', 'renewai_ajax', array(
        'ajax_url'         => admin_url('admin-ajax.php'),
        'nonce'            => wp_create_nonce('renewai_nonce'),
        'is_gutenberg'     => $is_gutenberg,
        'no_log_file_text' => __('No log file exists.', 'renewai-post-creator'),
      ));
    }

    /**
     * Log messages to a file when debug mode is enabled
     */
    private function log_to_file($message)
    {
      $debug_mode = get_option('renewai_debug_mode', 0);
      if ($debug_mode) {
        $log_file = plugin_dir_path(__FILE__) . 'renewai-log.txt';
        $timestamp = gmdate("Y-m-d H:i:s");
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = (isset($backtrace[1]['function']) ? $backtrace[1]['function'] : 'Unknown');
        $log_message = "[{$timestamp}] [{$caller}] {$message}\n";
        // Set up WP_Filesystem
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
          require_once ABSPATH . '/wp-admin/includes/file.php';
          WP_Filesystem();
        }
        // Append to log file using WP_Filesystem
        if ($wp_filesystem->exists($log_file)) {
          $current_content = $wp_filesystem->get_contents($log_file);
          $wp_filesystem->put_contents($log_file, $current_content . $log_message, FS_CHMOD_FILE);
        } else {
          $wp_filesystem->put_contents($log_file, $log_message, FS_CHMOD_FILE);
        }
      }
    }

    /**
     * Delete the log file
     */
    public function delete_log_file()
    {
      // Check for nonce and user capabilities
      if (!check_ajax_referer('renewai_nonce', 'nonce', false) || !current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action.');
        return;
      }
      $log_file = RENEWAI_PC__PLUGIN_DIR . '/renewai-log.txt';
      // Set up WP_Filesystem
      global $wp_filesystem;
      if (empty($wp_filesystem)) {
        require_once ABSPATH . '/wp-admin/includes/file.php';
        WP_Filesystem();
      }
      if ($wp_filesystem->exists($log_file)) {
        // Use WP_Filesystem delete method
        $deleted = $wp_filesystem->delete($log_file);
        if ($deleted) {
          wp_send_json_success('Log file deleted successfully. Uncheck debug mode and save settings.');
        } else {
          // If deletion fails, get more information
          $error_message = 'Failed to delete the log file. ';
          if (!$wp_filesystem->is_writable($log_file)) {
            $error_message .= 'The file is not writable. ';
          }
          $error_message .= 'Please check file permissions.';
          wp_send_json_error($error_message);
        }
      } else {
        wp_send_json_error('Log file does not exist.');
      }
    }

    /**
     * Add meta box to post edit screen
     */
    public function add_meta_box()
    {
      if (current_user_can('edit_posts')) {
        add_meta_box(
          'renewai_post_creator_meta_box',
          'RenewAI Post Creator',
          array($this, 'render_post_creator_meta_box'),
          array('post', 'page'),
          'side',
          'high'
        );
      }
    }

    /**
     * Render the post creator meta box
     */
    public function render_post_creator_meta_box($post)
    {
?>
      <div id="renewai-pc-post-creator-meta-box">
        <textarea id="renewai-keywords" rows="3" style="width: 100%;" placeholder="Enter keywords or ideas..."></textarea>
        <button id="renewai-generate" class="button button-primary" style="margin-top: 10px;">Generate Post</button>
        <div id="renewai-status" style="margin-top: 10px;">
          <span id="renewai-status-text"></span>
          <span id="renewai-spinner" class="spinner" style="float: none; visibility: hidden;"></span>
        </div>
      </div>
<?php
    }

    /**
     * Generate post content using the selected AI provider
     */
    public function generate_post_content()
    {
      if (!current_user_can('edit_posts')) {
        wp_send_json_error(array(
          'message' => 'You do not have permission to perform this action.',
        ));
        return;
      }
      check_ajax_referer('renewai_nonce', 'nonce');
      if (isset($_POST['keywords'])) {
        $keywords = sanitize_text_field(wp_unslash($_POST['keywords']));
      } else {
        wp_send_json_error(array(
          'message' => 'No keywords provided.',
        ));
        return;
      }
      $api_provider = get_option('renewai_api_provider', 'openai');
      $this->log_to_file("Current API provider: " . $api_provider);
      // Generate content for OpenAI Default
      if ($api_provider === 'openai') {
        $response = $this->generate_openai_content($keywords);
      }
      // Handle the response
      if (is_wp_error($response)) {
        $this->log_to_file("Error generating content: " . $response->get_error_message());
        wp_send_json_error(array(
          'message' => 'Error generating content.',
          'error'   => $response->get_error_message(),
        ));
      } else {
        wp_send_json_success($response);
      }
    }

    /**
     * Generate content using OpenAI API
     */
    private function generate_openai_content($keywords)
    {
      $api_key = $this->get_api_key('openai');
      if (empty($api_key)) {
        $this->log_to_file("Error: OpenAI API key is empty");
        return new WP_Error('openai_error', 'OpenAI API key is not set.');
      }
      $this->log_to_file("OpenAI API Key length: " . strlen($api_key) . ", First 5 chars: " . substr($api_key, 0, 5));
      $model = get_option('renewai_openai_model', 'gpt-4');
      $this->log_to_file("OpenAI Model: " . $model);
      $default_system_prompt = 'You are a senior content writer tasked with creating blog posts from a list of keywords or ideas. Structure your content using HTML tags (h2 for main title, h3 for subheadings, and p for paragraphs) to ensure SEO-friendly formatting.';
      $system_prompt = get_option('renewai_openai_system_prompt', $default_system_prompt);
      $user_prompt = "Generate a blog post about: {$keywords}\r\n\r\n    Never provide Markdown. Only HTML. Your content should be well-structured using HTML tags. Use <h2> for the main title, <h3> for subheadings, <p> for paragraphs, <ul> for lists, <li> for list items, <a> for links, <img> for images, <table> for tables, <tr> for table rows, <th> for table headers, <td> for table data. Ensure the content is SEO-friendly and engaging.\r\n\r\n    After the blog post content, provide 3 suggested titles for this post.\r\n\r\n    Format your response exactly like this:\r\n    [CONTENT]\r\n    (Your generated blog post content here)\r\n    [/CONTENT]\r\n\r\n    [TITLES]\r\n    1. (First suggested title)\r\n    2. (Second suggested title)\r\n    3. (Third suggested title)\r\n    [/TITLES]";
      $max_tokens = intval(get_option('renewai_openai_max_tokens', 1000));
      $temperature = floatval(get_option('renewai_openai_temperature', 0.7));
      // Construct the API request body
      $request_body = wp_json_encode(array(
        'model'       => $model,
        'messages'    => array(array(
          'role'    => 'system',
          'content' => $system_prompt,
        ), array(
          'role'    => 'user',
          'content' => $user_prompt,
        )),
        'max_tokens'  => $max_tokens,
        'temperature' => $temperature,
      ));
      // Log the request details
      $this->log_to_file("OpenAI Request - Endpoint: https://api.openai.com/v1/chat/completions, Body: {$request_body}");
      // Make the API call
      $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'timeout'   => 60,
        'headers'   => array(
          'Authorization' => 'Bearer ' . $api_key,
          'Content-Type'  => 'application/json',
        ),
        'body'      => $request_body,
        'sslverify' => true,
      ));
      if (is_wp_error($response)) {
        // Log the error if the API request fails
        $this->log_to_file("OpenAI API Error: " . $response->get_error_message());
        return $response;
      }
      // Log the response code and body
      $response_code = wp_remote_retrieve_response_code($response);
      $response_body = wp_remote_retrieve_body($response);
      $this->log_to_file("OpenAI Response - Status Code: {$response_code}, Body: {$response_body}");
      // Parse the response body
      $body = json_decode($response_body, true);
      if (isset($body['choices'][0]['message']['content'])) {
        return $this->parse_ai_response($body['choices'][0]['message']['content']);
      } else {
        return new WP_Error('openai_error', 'Unable to generate content from OpenAI response.');
      }
    }

    /**
     * Get free OpenAI models
     */
    private function get_openai_models__free()
    {
      return array(
        'gpt-4'         => 'gpt-4',
        'gpt-4-turbo'   => 'gpt-4-turbo',
        'gpt-3.5-turbo' => 'gpt-3.5-turbo',
      );
    }

    /**
     * Parse AI Response - Handles content and titles
     */
    private function parse_ai_response($response)
    {
      $this->log_to_file("Parsing content...");
      // Decode HTML entities
      $response = html_entity_decode($response, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      // Replace Unicode escaped characters
      $response = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
      }, $response);
      $content = $this->extract_between($response, '[CONTENT]', '[/CONTENT]');
      $titles_section = $this->extract_between($response, '[TITLES]', '[/TITLES]');
      $titles = array_map('trim', explode("\n", $titles_section));
      $titles = array_filter($titles, 'strlen');
      $titles = array_map(function ($title) {
        // Remove HTML tags and decode entities
        $title = wp_strip_all_tags($title);
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remove any numbering at the start
        return preg_replace('/^\\d+\\.\\s*/', '', $title);
      }, $titles);
      // Log the values
      $this->log_to_file("Content to be inserted into the post: " . $content);
      $this->log_to_file("Titles to be used for the post: " . implode(", ", $titles));
      return array(
        'content' => trim($content),
        'titles'  => array_values($titles),
      );
    }

    private function extract_between($string, $start, $end)
    {
      $string = ' ' . $string;
      $ini = strpos($string, $start);
      if ($ini == 0) {
        return '';
      }
      $ini += strlen($start);
      $len = strpos($string, $end, $ini) - $ini;
      return substr($string, $ini, $len);
    }

    /**
     * Convert Markdown to HTML - Perplexity Specific Issue
     */
    private function markdown_to_html($text)
    {
      $this->log_to_file("markdown_to_html input: " . substr($text, 0, 100) . "...");
      $patterns = array(
        '/^#####\\s(.*)$/m'        => '<h5>$1</h5>',
        '/^####\\s(.*)$/m'         => '<h4>$1</h4>',
        '/^###\\s(.*)$/m'          => '<h3>$1</h3>',
        '/^##\\s(.*)$/m'           => '<h2>$1</h2>',
        '/^#\\s(.*)$/m'            => '<h2>$1</h2>',
        '/\\*\\*(.*?)\\*\\*/s'     => '<strong>$1</strong>',
        '/\\*(.*?)\\*/s'           => '<em>$1</em>',
        '/\\[(.*?)\\]\\((.*?)\\)/' => '<a href="$2">$1</a>',
        '/^\\s*[-*+]\\s(.*)$/m'    => '<ul><li>$1</li></ul>',
        '/^\\s*\\d+\\.\\s(.*)$/m'  => '<ol><li>$1</li></ol>',
      );
      foreach ($patterns as $pattern => $replacement) {
        $text = preg_replace($pattern, $replacement, $text);
        if ($text === null) {
          // Handle preg_replace error
          error_log("preg_replace error in markdown_to_html function");
          return $text;
          // Return original text if there's an error
        }
      }
      // Wrap plain text in <p> tags
      $text = '<p>' . preg_replace('/\\n\\n/', '</p><p>', $text) . '</p>';
      $text = str_replace('<p><h', '<h', $text);
      // Remove <p> tags around headers
      $text = str_replace('</h2></p>', '</h2>', $text);
      $text = str_replace('</h3></p>', '</h3>', $text);
      $this->log_to_file("markdown_to_html output: " . substr($text, 0, 100) . "...");
      return $text;
    }

    /**
     * Sanitize temperature field
     */
    public function sanitize_temperature($input)
    {
      return max(0, min(1, floatval($input)));
    }

    /**
     * Update API key for a provider
     */
    private function update_api_key($provider, $key)
    {
      if (!empty($key)) {
        // Check if the key is the placeholder value
        if ($key === str_repeat('•', 32)) {
          $this->log_to_file("Attempted to update {$provider} API key with placeholder value. Skipping update.");
          return;
        }
        $encrypted_key = $this->encrypt_api_key($key);
        $this->log_to_file("Updating {$provider} API key. Original key length: " . strlen($key) . ", Encrypted key length: " . strlen($encrypted_key));
        update_option("renewai_{$provider}_api_key", $encrypted_key);
        // Verify the key was stored correctly
        $stored_key = get_option("renewai_{$provider}_api_key", '');
        $this->log_to_file("Stored {$provider} API key length: " . strlen($stored_key));
        // Attempt to decrypt and verify
        $decrypted_key = $this->decrypt_api_key($stored_key);
        $this->log_to_file("Decrypted {$provider} API key length: " . strlen($decrypted_key) . ", First 5 chars: " . bin2hex(substr($decrypted_key, 0, 5)));
      }
    }

    /**
     * Get API key for a provider
     */
    private function get_api_key($provider)
    {
      $encrypted_key = get_option("renewai_{$provider}_api_key", '');
      $this->log_to_file("Retrieving {$provider} API key. Encrypted key exists: " . ((!empty($encrypted_key) ? 'Yes' : 'No')) . ", Encrypted key length: " . strlen($encrypted_key));
      $decrypted_key = $this->decrypt_api_key($encrypted_key);
      $this->log_to_file("Decrypted {$provider} API key length: " . strlen($decrypted_key));
      $this->log_to_file("Decrypted {$provider} API key (first 5 chars): " . bin2hex(substr($decrypted_key, 0, 5)));
      return $decrypted_key;
    }

    /**
     * Encrypt API key
     */
    private function encrypt_api_key($key)
    {
      if (!extension_loaded('openssl')) {
        return base64_encode($key);
      }
      $iv = openssl_random_pseudo_bytes(16);
      $encrypted = openssl_encrypt(
        $key,
        'AES-256-CBC',
        wp_salt('auth'),
        0,
        $iv
      );
      return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt API key
     */
    private function decrypt_api_key($encrypted_key)
    {
      if (empty($encrypted_key)) {
        return '';
      }
      if (!extension_loaded('openssl')) {
        return base64_decode($encrypted_key);
      }
      $encrypted = base64_decode($encrypted_key);
      $iv = substr($encrypted, 0, 16);
      $encrypted = substr($encrypted, 16);
      $decrypted = openssl_decrypt(
        $encrypted,
        'AES-256-CBC',
        wp_salt('auth'),
        0,
        $iv
      );
      return ($decrypted !== false ? $decrypted : '');
    }

    /**
     * Get First Interaction (newsletter optin)
     */
    public function get_first_interaction($user_id = null)
    {
      if (!$user_id) {
        $user_id = get_current_user_id();
      }
      $value = get_user_meta($user_id, 'renewai_first_interaction', true);
      // If the value is empty or not set, it means it's the first interaction
      return $value === '' || $value === false;
    }

    /**
     * Set First Interaction (newsletter optin)
     */
    public function set_first_interaction($value, $user_id = null)
    {
      if (!$user_id) {
        $user_id = get_current_user_id();
      }
      // We'll store '0' for false and '1' for true
      $store_value = ($value ? '1' : '0');
      update_user_meta($user_id, 'renewai_first_interaction', $store_value);
      $this->log_to_file("Set renewai_first_interaction to " . $store_value . " for user " . $user_id);
    }

    /**
     * Handle newsletter subscription
     */
    public function handle_newsletter_subscription()
    {
      $this->log_to_file("Entering handle_newsletter_subscription function");
      $user_id = get_current_user_id();
      if ($this->get_first_interaction($user_id) && isset($_POST['renewai_newsletter_optin']) && $_POST['renewai_newsletter_optin'] == '1') {
        $this->log_to_file("Newsletter opt-in checkbox is checked");
        $current_user = wp_get_current_user();
        if (isset($_POST['renewai_newsletter_email'])) {
          $user_email = sanitize_email(wp_unslash($_POST['renewai_newsletter_email']));
        } else {
          $user_email = '';
        }
        $first_name = $current_user->first_name;
        $last_name = $current_user->last_name;
        $this->log_to_file("User details - Email: {$user_email}, First Name: {$first_name}, Last Name: {$last_name}");
        $to = RENEWAI_PC__NEWSLETTER_ADDR;
        $subject = 'RenewAI Post Creator: New Email Newsletter Subscription';
        $message = "New newsletter subscription:\n\n";
        $message .= "Email: {$user_email}\n";
        $message .= "First Name: {$first_name}\n";
        $message .= "Last Name: {$last_name}\n";
        $this->log_to_file("Preparing to send email to admin: {$to}");
        $email_sent = wp_mail($to, $subject, $message);
        if ($email_sent) {
          $this->log_to_file("Email sent successfully");
        } else {
          $this->log_to_file("Failed to send email");
          // Additional debugging for email failure
          global $phpmailer;
          if (isset($phpmailer)) {
            $this->log_to_file("PHPMailer error: " . $phpmailer->ErrorInfo);
          }
        }
      } else {
        $this->log_to_file("Newsletter opt-in checkbox is not checked");
      }
      $this->log_to_file("Exiting handle_newsletter_subscription function");
    }

    /**
     * API Pricing Links
     */
    private function get_api_pricing_links()
    {
      return array(
        'openai'     => 'https://openai.com/api/pricing/',
        'anthropic'  => 'https://www.anthropic.com/pricing#anthropic-api',
        'gemini'     => 'https://cloud.google.com/vertex-ai/docs/generative-ai/pricing',
        'perplexity' => 'https://docs.perplexity.ai/guides/pricing',
      );
    }

    /**
     * Plugin links - plugins page
     */
    public function add_plugin_action_links($links)
    {
      $plugin_links = array('<a href="' . admin_url('admin.php?page=renewai-post-creator-settings') . '">Settings</a>', '<a href="' . admin_url('admin.php?page=renewai-post-creator-api-keys') . '">API Keys</a>', '<a href="' . admin_url('admin.php?page=renewai-post-creator-help') . '">Help</a>');
      return array_merge($plugin_links, $links);
    }
  }

  // Initialize the plugin
  new RenewAI_Post_Creator();
}
