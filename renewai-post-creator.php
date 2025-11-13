<?php

/**
 * Plugin Name: RenewAI Post Creator
 * Description: Generate high-quality blog post content using AI models from OpenAI, with premium features for Anthropic, Google Gemini and Perplexity.
 * Version: 1.4
 * Author: FullScope
 * Author URI: https://fullsco.pe
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Directory and URL Constants
if ( !defined( 'RENEWAI_PC__PLUGIN_DIR' ) ) {
    define( 'RENEWAI_PC__PLUGIN_DIR', dirname( __FILE__ ) );
    define( 'RENEWAI_PC__PLUGIN_URL', plugins_url( plugin_basename( RENEWAI_PC__PLUGIN_DIR ) ) );
}
// Directory and URL Constants
if ( !defined( 'RENEWAI_PC__NEWSLETTER_ADDR' ) ) {
    define( 'RENEWAI_PC__NEWSLETTER_ADDR', 'success@perpetuaiconsult.com' );
}
if ( function_exists( 'renewai_pc_fs' ) ) {
    renewai_pc_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if ( !function_exists( 'renewai_pc_fs' ) ) {
        // Create a helper function for easy SDK access.
        function renewai_pc_fs() {
            global $renewai_pc_fs;
            if ( !isset( $renewai_pc_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
                $renewai_pc_fs = fs_dynamic_init( array(
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
                ) );
            }
            return $renewai_pc_fs;
        }

        // Init Freemius.
        renewai_pc_fs();
        // Signal that SDK was initiated.
        do_action( 'renewai_pc_fs_loaded' );
    }
    class RenewAI_Post_Creator {
        public function __construct() {
            // Hook into WordPress actions
            add_action( 'admin_menu', array($this, 'add_settings_page') );
            add_action( 'add_meta_boxes', array($this, 'add_meta_box') );
            add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
            add_action( 'wp_ajax_generate_post_content', array($this, 'generate_post_content') );
            add_action( 'wp_ajax_delete_renewai_log_file', array($this, 'delete_log_file') );
            add_action( 'admin_init', array($this, 'register_settings') );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($this, 'add_plugin_action_links') );
        }

        /**
         * Register Settings
         */
        public function register_settings() {
            register_setting( 'renewai_settings', 'renewai_prompt_library', array($this, 'sanitize_settings') );
            // Define provider arrays
            $providers_free = array('openai');
            $providers_premium = array(
                'openai',
                'anthropic',
                'perplexity',
                'gemini'
            );
            // Choose which array to use
            $providers_to_use = ( renewai_pc_fs()->can_use_premium_code__premium_only() ? $providers_premium : $providers_free );
            // Register settings for each provider
            foreach ( $providers_to_use as $provider ) {
                register_setting( 'renewai_settings', "renewai_{$provider}_max_tokens", 'intval' );
                register_setting( 'renewai_settings', "renewai_{$provider}_temperature", array($this, 'sanitize_temperature') );
            }
        }

        /**
         * Add settings page and submenus to WordPress admin
         */
        public function add_settings_page() {
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
        public function render_settings_page() {
            if ( !current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array(
                    'message' => 'You do not have permission to access this page.',
                ) );
                return;
            }
            $current_user = wp_get_current_user();
            $user_email = $current_user->user_email;
            include RENEWAI_PC__PLUGIN_DIR . '/pages/settings-page.php';
        }

        /**
         * Render the API keys page
         */
        public function render_api_keys_page() {
            if ( !current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array(
                    'message' => 'You do not have permission to access this page.',
                ) );
                return;
            }
            include RENEWAI_PC__PLUGIN_DIR . '/pages/api-keys.php';
        }

        /**
         * Render the help page
         */
        public function render_help_page() {
            if ( !current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array(
                    'message' => 'You do not have permission to access this page.',
                ) );
                return;
            }
            include RENEWAI_PC__PLUGIN_DIR . '/pages/help-page.php';
        }

        /**
         * Enqueue scripts and styles for the plugin
         */
        public function enqueue_scripts( $hook ) {
            // Enqueue styles for both classic and Gutenberg editor
            wp_enqueue_style(
                'renewai-admin-styles',
                RENEWAI_PC__PLUGIN_URL . '/assets/css/renewai-styles.css',
                array(),
                '1.0'
            );
            // Determine if Gutenberg is being used
            $is_gutenberg = function_exists( 'use_block_editor_for_post' ) && use_block_editor_for_post( get_post() );
            // Enqueue script for both classic and Gutenberg editor
            wp_enqueue_script(
                'renewai-script',
                RENEWAI_PC__PLUGIN_URL . '/assets/js/app.js',
                ( $is_gutenberg ? array(
                    'jquery',
                    'wp-blocks',
                    'wp-element',
                    'wp-editor',
                    'wp-components',
                    'wp-i18n',
                    'wp-data'
                ) : array('jquery') ),
                '1.0',
                true
            );
            // Localize script
            wp_localize_script( 'renewai-script', 'renewai_ajax', array(
                'ajax_url'         => admin_url( 'admin-ajax.php' ),
                'nonce'            => wp_create_nonce( 'renewai_nonce' ),
                'is_gutenberg'     => $is_gutenberg,
                'no_log_file_text' => __( 'No log file exists.', 'renewai-post-creator' ),
            ) );
        }

        /**
         * Log messages to a file when debug mode is enabled
         */
        private function log_to_file( $message ) {
            $debug_mode = get_option( 'renewai_debug_mode', 0 );
            if ( $debug_mode ) {
                $log_file = plugin_dir_path( __FILE__ ) . 'renewai-log.txt';
                $timestamp = gmdate( "Y-m-d H:i:s" );
                $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 );
                $caller = ( isset( $backtrace[1]['function'] ) ? $backtrace[1]['function'] : 'Unknown' );
                $log_message = "[{$timestamp}] [{$caller}] {$message}\n";
                // Set up WP_Filesystem
                global $wp_filesystem;
                if ( empty( $wp_filesystem ) ) {
                    require_once ABSPATH . '/wp-admin/includes/file.php';
                    WP_Filesystem();
                }
                // Append to log file using WP_Filesystem
                if ( $wp_filesystem->exists( $log_file ) ) {
                    $current_content = $wp_filesystem->get_contents( $log_file );
                    $wp_filesystem->put_contents( $log_file, $current_content . $log_message, FS_CHMOD_FILE );
                } else {
                    $wp_filesystem->put_contents( $log_file, $log_message, FS_CHMOD_FILE );
                }
            }
        }

        /**
         * Delete the log file
         */
        public function delete_log_file() {
            // Check for nonce and user capabilities
            if ( !check_ajax_referer( 'renewai_nonce', 'nonce', false ) || !current_user_can( 'manage_options' ) ) {
                wp_send_json_error( 'You do not have permission to perform this action.' );
                return;
            }
            $log_file = RENEWAI_PC__PLUGIN_DIR . '/renewai-log.txt';
            // Set up WP_Filesystem
            global $wp_filesystem;
            if ( empty( $wp_filesystem ) ) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }
            if ( $wp_filesystem->exists( $log_file ) ) {
                // Use WP_Filesystem delete method
                $deleted = $wp_filesystem->delete( $log_file );
                if ( $deleted ) {
                    wp_send_json_success( 'Log file deleted successfully. Uncheck debug mode and save settings.' );
                } else {
                    // If deletion fails, get more information
                    $error_message = 'Failed to delete the log file. ';
                    if ( !$wp_filesystem->is_writable( $log_file ) ) {
                        $error_message .= 'The file is not writable. ';
                    }
                    $error_message .= 'Please check file permissions.';
                    wp_send_json_error( $error_message );
                }
            } else {
                wp_send_json_error( 'Log file does not exist.' );
            }
        }

        /**
         * Add meta box to post edit screen
         */
        public function add_meta_box() {
            if ( current_user_can( 'edit_posts' ) ) {
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
        public function render_post_creator_meta_box( $post ) {
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
        public function generate_post_content() {
            if ( !current_user_can( 'edit_posts' ) ) {
                wp_send_json_error( array(
                    'message' => 'You do not have permission to perform this action.',
                ) );
                return;
            }
            check_ajax_referer( 'renewai_nonce', 'nonce' );
            if ( isset( $_POST['keywords'] ) ) {
                $keywords = sanitize_text_field( wp_unslash( $_POST['keywords'] ) );
            } else {
                wp_send_json_error( array(
                    'message' => 'No keywords provided.',
                ) );
                return;
            }
            $api_provider = get_option( 'renewai_api_provider', 'openai' );
            $this->log_to_file( "Current API provider: " . $api_provider );
            // Generate content for OpenAI Default
            if ( $api_provider === 'openai' ) {
                $response = $this->generate_openai_content( $keywords );
            }
            // Handle the response
            if ( is_wp_error( $response ) ) {
                $this->log_to_file( "Error generating content: " . $response->get_error_message() );
                wp_send_json_error( array(
                    'message' => 'Error generating content.',
                    'error'   => $response->get_error_message(),
                ) );
            } else {
                wp_send_json_success( $response );
            }
        }

        /**
         * Generate content using OpenAI API
         */
        private function generate_openai_content( $keywords ) {
            $api_key = $this->get_api_key( 'openai' );
            if ( empty( $api_key ) ) {
                $this->log_to_file( "Error: OpenAI API key is empty" );
                return new WP_Error('openai_error', 'OpenAI API key is not set.');
            }
            $this->log_to_file( "OpenAI API Key length: " . strlen( $api_key ) . ", First 5 chars: " . substr( $api_key, 0, 5 ) );
            $model = get_option( 'renewai_openai_model', 'gpt-4' );
            $this->log_to_file( "OpenAI Model: " . $model );
            $default_system_prompt = 'You are a senior content writer tasked with creating blog posts from a list of keywords or ideas. Structure your content using HTML tags (h2 for main title, h3 for subheadings, and p for paragraphs) to ensure SEO-friendly formatting.';
            $system_prompt = get_option( 'renewai_openai_system_prompt', $default_system_prompt );
            $user_prompt = "Generate a blog post about: {$keywords}\r\n\r\n    Never provide Markdown. Only HTML. Your content should be well-structured using HTML tags. Use <h2> for the main title, <h3> for subheadings, <p> for paragraphs, <ul> for lists, <li> for list items, <a> for links, <img> for images, <table> for tables, <tr> for table rows, <th> for table headers, <td> for table data. Ensure the content is SEO-friendly and engaging.\r\n\r\n    After the blog post content, provide 3 suggested titles for this post.\r\n\r\n    Format your response exactly like this:\r\n    [CONTENT]\r\n    (Your generated blog post content here)\r\n    [/CONTENT]\r\n\r\n    [TITLES]\r\n    1. (First suggested title)\r\n    2. (Second suggested title)\r\n    3. (Third suggested title)\r\n    [/TITLES]";
            $max_tokens = intval( get_option( 'renewai_openai_max_tokens', 1000 ) );
            $temperature = floatval( get_option( 'renewai_openai_temperature', 0.7 ) );
            // Construct the API request body
            $request_body = wp_json_encode( array(
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
            ) );
            // Log the request details
            $this->log_to_file( "OpenAI Request - Endpoint: https://api.openai.com/v1/chat/completions, Body: {$request_body}" );
            // Make the API call
            $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
                'timeout'   => 60,
                'headers'   => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ),
                'body'      => $request_body,
                'sslverify' => true,
            ) );
            if ( is_wp_error( $response ) ) {
                // Log the error if the API request fails
                $this->log_to_file( "OpenAI API Error: " . $response->get_error_message() );
                return $response;
            }
            // Log the response code and body
            $response_code = wp_remote_retrieve_response_code( $response );
            $response_body = wp_remote_retrieve_body( $response );
            $this->log_to_file( "OpenAI Response - Status Code: {$response_code}, Body: {$response_body}" );
            $body = json_decode( $response_body, true );
            // Check for API errors (non-2xx status codes)
            if ( $response_code >= 400 ) {
                $error_message = 'API Error';
                // Extract detailed error information from API response
                if ( isset( $body['error'] ) && isset( $body['error']['message'] ) ) {
                    $error_message = $body['error']['message'];
                }
                $this->log_to_file( "OpenAI API Error - Status: {$response_code}, Message: {$error_message}" );
                return new WP_Error('openai_api_error', $error_message, array(
                    'status_code'   => $response_code,
                    'response_body' => $response_body,
                ));
            }
            // Parse the response body for successful responses
            if ( isset( $body['choices'][0]['message']['content'] ) ) {
                return $this->parse_ai_response( $body['choices'][0]['message']['content'] );
            } else {
                return new WP_Error('openai_error', 'Unable to generate content from OpenAI response.');
            }
        }

        /**
         * Get free OpenAI models
         */
        private function get_openai_models__free() {
            return array('gpt-4o-mini', 'gpt-4', 'gpt-3.5-turbo');
        }

        /**
         * Check if an OpenAI model is suitable for content generation
         */
        private function is_suitable_openai_model( $model_id ) {
            // Exclude non-chat models
            $excluded_patterns = array(
                'embedding',
                'whisper',
                'tts',
                'dall-e',
                'moderation',
                'instruct',
                ':ft-',
                // Fine-tuned models
                'babbage',
                'davinci',
                'ada',
                'curie',
                'preview',
                'audio',
                'transcribe',
                'realtime',
                'nano',
                'chat',
                'search',
                'code',
                'image',
            );
            foreach ( $excluded_patterns as $pattern ) {
                if ( strpos( strtolower( $model_id ), $pattern ) !== false ) {
                    return false;
                }
            }
            // Only include GPT models
            if ( strpos( $model_id, 'gpt-' ) !== 0 ) {
                return false;
            }
            // Exclude versioned/dated models (e.g., gpt-4-0613, gpt-3.5-turbo-1106, gpt-4o-2024-05-13)
            if ( $this->is_versioned_openai_model( $model_id ) ) {
                return false;
            }
            return true;
        }

        /**
         * Check if an OpenAI model is a versioned/dated variant
         */
        private function is_versioned_openai_model( $model_id ) {
            // Patterns that indicate versioned models
            $versioned_patterns = array(
                // Date patterns (YYYY-MM-DD)
                '/\\d{4}-\\d{2}-\\d{2}/',
                // Version numbers (4 digits like 0613, 1106)
                '/-\\d{4}$/',
                // Specific suffixes to exclude
                '/-16k$/',
                '/-32k$/',
                '/-preview$/',
                // Any model ending with numbers after a dash
                '/-\\d+$/',
            );
            foreach ( $versioned_patterns as $pattern ) {
                if ( preg_match( $pattern, $model_id ) ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Sort OpenAI models by preference (newer/better models first)
         */
        private function sort_openai_models( $models ) {
            // Define model priority (higher number = higher priority)
            $model_priority = array(
                'gpt-4o'        => 100,
                'gpt-4o-mini'   => 95,
                'gpt-4-turbo'   => 90,
                'gpt-4'         => 85,
                'gpt-3.5-turbo' => 80,
            );
            usort( $models, function ( $a, $b ) use($model_priority) {
                $priority_a = ( isset( $model_priority[$a] ) ? $model_priority[$a] : 0 );
                $priority_b = ( isset( $model_priority[$b] ) ? $model_priority[$b] : 0 );
                // If priorities are different, sort by priority
                if ( $priority_a !== $priority_b ) {
                    return $priority_b - $priority_a;
                }
                // If same priority, sort alphabetically
                return strcmp( $a, $b );
            } );
            return $models;
        }

        /**
         * Get fallback OpenAI models (used when API fails)
         */
        private function get_fallback_openai_models() {
            return array(
                'gpt-4o',
                'gpt-4o-mini',
                'gpt-4-turbo',
                'gpt-4',
                'gpt-3.5-turbo'
            );
        }

        /**
         * Fallback models in case API fails
         */
        private function get_fallback_anthropic_models() {
            return array(
                'claude-sonnet-4-5-20250929' => 'Claude Sonnet 4.5',
                'claude-haiku-4-5-20251001'  => 'Claude Haiku 4.5',
                'claude-opus-4-1-20250805'   => 'Claude Opus 4.1',
                'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
                'claude-3-5-haiku-20241022'  => 'Claude 3.5 Haiku',
            );
        }

        /**
         * Check if an Anthropic model is suitable for content generation
         */
        private function is_suitable_anthropic_model( $model_id ) {
            // Include only Claude models suitable for content generation
            $suitable_patterns = array(
                'claude-3',
                'claude-4',
                'claude-sonnet',
                'claude-haiku',
                'claude-opus'
            );
            foreach ( $suitable_patterns as $pattern ) {
                if ( strpos( strtolower( $model_id ), $pattern ) === 0 ) {
                    // Exclude deprecated models
                    if ( strpos( strtolower( $model_id ), 'claude-instant' ) !== false ) {
                        return false;
                    }
                    return true;
                }
            }
            return false;
        }

        /**
         * Sort Anthropic models by preference
         */
        private function sort_anthropic_models( $models ) {
            // Define model priority (higher number = higher priority)
            $model_priority = array(
                'claude-4-5-sonnet' => 100,
                'claude-4-5-haiku'  => 95,
                'claude-4-1-opus'   => 90,
                'claude-3-5-sonnet' => 85,
                'claude-3-5-haiku'  => 80,
            );
            uksort( $models, function ( $a, $b ) use($model_priority) {
                $priority_a = 0;
                $priority_b = 0;
                // Find priority based on model name patterns
                foreach ( $model_priority as $pattern => $priority ) {
                    if ( strpos( $a, $pattern ) !== false ) {
                        $priority_a = $priority;
                    }
                    if ( strpos( $b, $pattern ) !== false ) {
                        $priority_b = $priority;
                    }
                }
                // If priorities are different, sort by priority
                if ( $priority_a !== $priority_b ) {
                    return $priority_b - $priority_a;
                }
                // If same priority, sort alphabetically
                return strcmp( $a, $b );
            } );
            return $models;
        }

        /**
         * Check if a Gemini model is suitable for content generation
         */
        private function is_suitable_gemini_model( $model_name ) {
            // Include only models suitable for content generation
            $suitable_patterns = array(
                'gemini-2.5-pro',
                'gemini-2.5-flash',
                'gemini-2.5-flash-lite',
                'gemini-2.0-flash',
                'gemini-2.0-flash-lite',
                'gemini-pro',
                'gemini-1.0-pro'
            );
            foreach ( $suitable_patterns as $pattern ) {
                if ( strpos( strtolower( $model_name ), $pattern ) !== false ) {
                    // Exclude vision-only or embedding models
                    if ( strpos( strtolower( $model_name ), 'vision' ) !== false || strpos( strtolower( $model_name ), 'embedding' ) !== false ) {
                        return false;
                    }
                    // Exclude versioned/experimental variants
                    if ( $this->is_versioned_gemini_model( $model_name ) ) {
                        return false;
                    }
                    return true;
                }
            }
            return false;
        }

        /**
         * Check if a Gemini model is a versioned/experimental variant
         */
        private function is_versioned_gemini_model( $model_name ) {
            $model_lower = strtolower( $model_name );
            // Regex patterns that indicate versioned/experimental models
            $regex_patterns = array(
                // Experimental models
                '/-exp$/',
                '/-exp-/',
                '/thinking-exp/',
                // Version numbers and dates
                '/-\\d{4}$/',
                '/-\\d{2}-\\d{2}$/',
                '/-\\d{1,3}$/',
                // Image generation models
                '/-image$/',
                // Lite variants (but allow base lite models)
                '/flash-lite-/',
            );
            foreach ( $regex_patterns as $pattern ) {
                if ( preg_match( $pattern, $model_name ) ) {
                    return true;
                }
            }
            // Also exclude if it contains common versioned keywords
            $versioned_keywords = array(
                'preview',
                'experimental',
                'beta',
                'alpha',
                'test'
            );
            foreach ( $versioned_keywords as $keyword ) {
                if ( strpos( $model_lower, $keyword ) !== false ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Sort Gemini models by preference
         */
        private function sort_gemini_models( $models ) {
            // Define model priority (higher number = higher priority)
            $model_priority = array(
                'gemini-2.5-pro'        => 110,
                'gemini-2.5-flash'      => 105,
                'gemini-2.5-flash-lite' => 100,
                'gemini-2.0-flash'      => 95,
                'gemini-2.0-flash-lite' => 90,
                'gemini-1.5-pro'        => 85,
                'gemini-1.5-flash'      => 80,
                'gemini-pro'            => 75,
                'gemini-1.0-pro'        => 70,
            );
            uksort( $models, function ( $a, $b ) use($model_priority) {
                $priority_a = 0;
                $priority_b = 0;
                // Find priority based on model name patterns
                foreach ( $model_priority as $pattern => $priority ) {
                    if ( strpos( $a, $pattern ) !== false ) {
                        $priority_a = $priority;
                    }
                    if ( strpos( $b, $pattern ) !== false ) {
                        $priority_b = $priority;
                    }
                }
                // If priorities are different, sort by priority
                if ( $priority_a !== $priority_b ) {
                    return $priority_b - $priority_a;
                }
                // If same priority, sort alphabetically
                return strcmp( $a, $b );
            } );
            return $models;
        }

        /**
         * Get fallback Gemini models (used when API fails)
         */
        private function get_fallback_gemini_models() {
            return array(
                'models/gemini-1.5-pro'   => 'Gemini 1.5 Pro',
                'models/gemini-1.5-flash' => 'Gemini 1.5 Flash',
                'models/gemini-pro'       => 'Gemini Pro',
                'models/gemini-1.0-pro'   => 'Gemini 1.0 Pro',
            );
        }

        /**
         * Parse AI Response - Handles content and titles
         */
        private function parse_ai_response( $response ) {
            $this->log_to_file( "Parsing content..." );
            // Decode HTML entities
            $response = html_entity_decode( $response, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
            // Replace Unicode escaped characters
            $response = preg_replace_callback( '/\\\\u([0-9a-fA-F]{4})/', function ( $match ) {
                return mb_convert_encoding( pack( 'H*', $match[1] ), 'UTF-8', 'UCS-2BE' );
            }, $response );
            $content = $this->extract_between( $response, '[CONTENT]', '[/CONTENT]' );
            $titles_section = $this->extract_between( $response, '[TITLES]', '[/TITLES]' );
            $titles = array_map( 'trim', explode( "\n", $titles_section ) );
            $titles = array_filter( $titles, 'strlen' );
            $titles = array_map( function ( $title ) {
                // Remove HTML tags and decode entities
                $title = wp_strip_all_tags( $title );
                $title = html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                // Remove any numbering at the start
                return preg_replace( '/^\\d+\\.\\s*/', '', $title );
            }, $titles );
            // Log the values
            $this->log_to_file( "Content to be inserted into the post: " . $content );
            $this->log_to_file( "Titles to be used for the post: " . implode( ", ", $titles ) );
            return array(
                'content' => trim( $content ),
                'titles'  => array_values( $titles ),
            );
        }

        private function extract_between( $string, $start, $end ) {
            $string = ' ' . $string;
            $ini = strpos( $string, $start );
            if ( $ini == 0 ) {
                return '';
            }
            $ini += strlen( $start );
            $len = strpos( $string, $end, $ini ) - $ini;
            return substr( $string, $ini, $len );
        }

        /**
         * Convert Markdown to HTML - Perplexity Specific Issue
         */
        private function markdown_to_html( $text ) {
            $this->log_to_file( "markdown_to_html input: " . substr( $text, 0, 100 ) . "..." );
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
            foreach ( $patterns as $pattern => $replacement ) {
                $text = preg_replace( $pattern, $replacement, $text );
                if ( $text === null ) {
                    // Handle preg_replace error
                    error_log( "preg_replace error in markdown_to_html function" );
                    return $text;
                    // Return original text if there's an error
                }
            }
            // Wrap plain text in <p> tags
            $text = '<p>' . preg_replace( '/\\n\\n/', '</p><p>', $text ) . '</p>';
            $text = str_replace( '<p><h', '<h', $text );
            // Remove <p> tags around headers
            $text = str_replace( '</h2></p>', '</h2>', $text );
            $text = str_replace( '</h3></p>', '</h3>', $text );
            $this->log_to_file( "markdown_to_html output: " . substr( $text, 0, 100 ) . "..." );
            return $text;
        }

        /**
         * Sanitize temperature field
         */
        public function sanitize_temperature( $input ) {
            return max( 0, min( 1, floatval( $input ) ) );
        }

        /**
         * Update API key for a provider
         */
        private function update_api_key( $provider, $key ) {
            if ( !empty( $key ) ) {
                // Check if the key is the placeholder value
                if ( $key === str_repeat( '•', 32 ) ) {
                    $this->log_to_file( "Attempted to update {$provider} API key with placeholder value. Skipping update." );
                    return;
                }
                $encrypted_key = $this->encrypt_api_key( $key );
                $this->log_to_file( "Updating {$provider} API key. Original key length: " . strlen( $key ) . ", Encrypted key length: " . strlen( $encrypted_key ) );
                update_option( "renewai_{$provider}_api_key", $encrypted_key );
                // Verify the key was stored correctly
                $stored_key = get_option( "renewai_{$provider}_api_key", '' );
                $this->log_to_file( "Stored {$provider} API key length: " . strlen( $stored_key ) );
                // Attempt to decrypt and verify
                $decrypted_key = $this->decrypt_api_key( $stored_key );
                $this->log_to_file( "Decrypted {$provider} API key length: " . strlen( $decrypted_key ) . ", First 5 chars: " . bin2hex( substr( $decrypted_key, 0, 5 ) ) );
            }
        }

        /**
         * Get API key for a provider
         */
        private function get_api_key( $provider ) {
            $encrypted_key = get_option( "renewai_{$provider}_api_key", '' );
            $this->log_to_file( "Retrieving {$provider} API key. Encrypted key exists: " . (( !empty( $encrypted_key ) ? 'Yes' : 'No' )) . ", Encrypted key length: " . strlen( $encrypted_key ) );
            $decrypted_key = $this->decrypt_api_key( $encrypted_key );
            $this->log_to_file( "Decrypted {$provider} API key length: " . strlen( $decrypted_key ) );
            $this->log_to_file( "Decrypted {$provider} API key (first 5 chars): " . bin2hex( substr( $decrypted_key, 0, 5 ) ) );
            return $decrypted_key;
        }

        /**
         * Encrypt API key
         */
        private function encrypt_api_key( $key ) {
            if ( !extension_loaded( 'openssl' ) ) {
                return base64_encode( $key );
            }
            $iv = openssl_random_pseudo_bytes( 16 );
            $encrypted = openssl_encrypt(
                $key,
                'AES-256-CBC',
                wp_salt( 'auth' ),
                0,
                $iv
            );
            return base64_encode( $iv . $encrypted );
        }

        /**
         * Decrypt API key
         */
        private function decrypt_api_key( $encrypted_key ) {
            if ( empty( $encrypted_key ) ) {
                return '';
            }
            if ( !extension_loaded( 'openssl' ) ) {
                return base64_decode( $encrypted_key );
            }
            $encrypted = base64_decode( $encrypted_key );
            $iv = substr( $encrypted, 0, 16 );
            $encrypted = substr( $encrypted, 16 );
            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                wp_salt( 'auth' ),
                0,
                $iv
            );
            return ( $decrypted !== false ? $decrypted : '' );
        }

        /**
         * Get First Interaction (newsletter optin)
         */
        public function get_first_interaction( $user_id = null ) {
            if ( !$user_id ) {
                $user_id = get_current_user_id();
            }
            $value = get_user_meta( $user_id, 'renewai_first_interaction', true );
            // If the value is empty or not set, it means it's the first interaction
            return $value === '' || $value === false;
        }

        /**
         * Set First Interaction (newsletter optin)
         */
        public function set_first_interaction( $value, $user_id = null ) {
            if ( !$user_id ) {
                $user_id = get_current_user_id();
            }
            // We'll store '0' for false and '1' for true
            $store_value = ( $value ? '1' : '0' );
            update_user_meta( $user_id, 'renewai_first_interaction', $store_value );
            $this->log_to_file( "Set renewai_first_interaction to " . $store_value . " for user " . $user_id );
        }

        /**
         * Handle newsletter subscription
         */
        public function handle_newsletter_subscription() {
            $this->log_to_file( "Entering handle_newsletter_subscription function" );
            $user_id = get_current_user_id();
            if ( $this->get_first_interaction( $user_id ) && isset( $_POST['renewai_newsletter_optin'] ) && $_POST['renewai_newsletter_optin'] == '1' ) {
                $this->log_to_file( "Newsletter opt-in checkbox is checked" );
                $current_user = wp_get_current_user();
                if ( isset( $_POST['renewai_newsletter_email'] ) ) {
                    $user_email = sanitize_email( wp_unslash( $_POST['renewai_newsletter_email'] ) );
                } else {
                    $user_email = '';
                }
                $first_name = $current_user->first_name;
                $last_name = $current_user->last_name;
                $this->log_to_file( "User details - Email: {$user_email}, First Name: {$first_name}, Last Name: {$last_name}" );
                $to = RENEWAI_PC__NEWSLETTER_ADDR;
                $subject = 'RenewAI Post Creator: New Email Newsletter Subscription';
                $message = "New newsletter subscription:\n\n";
                $message .= "Email: {$user_email}\n";
                $message .= "First Name: {$first_name}\n";
                $message .= "Last Name: {$last_name}\n";
                $this->log_to_file( "Preparing to send email to admin: {$to}" );
                $email_sent = wp_mail( $to, $subject, $message );
                if ( $email_sent ) {
                    $this->log_to_file( "Email sent successfully" );
                } else {
                    $this->log_to_file( "Failed to send email" );
                    // Additional debugging for email failure
                    global $phpmailer;
                    if ( isset( $phpmailer ) ) {
                        $this->log_to_file( "PHPMailer error: " . $phpmailer->ErrorInfo );
                    }
                }
            } else {
                $this->log_to_file( "Newsletter opt-in checkbox is not checked" );
            }
            $this->log_to_file( "Exiting handle_newsletter_subscription function" );
        }

        /**
         * API Pricing Links
         */
        private function get_api_pricing_links() {
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
        public function add_plugin_action_links( $links ) {
            $plugin_links = array('<a href="' . admin_url( 'admin.php?page=renewai-post-creator-settings' ) . '">Settings</a>', '<a href="' . admin_url( 'admin.php?page=renewai-post-creator-api-keys' ) . '">API Keys</a>', '<a href="' . admin_url( 'admin.php?page=renewai-post-creator-help' ) . '">Help</a>');
            return array_merge( $plugin_links, $links );
        }

    }

    // Initialize the plugin
    new RenewAI_Post_Creator();
}