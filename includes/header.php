<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

//Get the current page
$current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
?>

<div class="renewai-pc-header">
  <h3 class="renewai-pc-title"><?php esc_html_e('RenewAI Post Creator', 'renewai-post-creator'); ?></h3>
  <nav class="renewai-pc-nav">
    <a href="?page=renewai-post-creator-settings" class="<?php echo $current_page === 'renewai-post-creator-settings' ? 'active' : ''; ?>"><?php esc_html_e('Settings', 'renewai-post-creator'); ?></a>
    <a href="?page=renewai-post-creator-api-keys" class="<?php echo $current_page === 'renewai-post-creator-api-keys' ? 'active' : ''; ?>"><?php esc_html_e('Manage API Keys', 'renewai-post-creator'); ?></a>
    <a href="?page=renewai-post-creator-help" class="<?php echo $current_page === 'renewai-post-creator-help' ? 'active' : ''; ?>"><?php esc_html_e('Help', 'renewai-post-creator'); ?></a>
  </nav>
</div>