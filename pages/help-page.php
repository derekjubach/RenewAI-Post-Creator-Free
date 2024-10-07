<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}
//Do you have permission to be here
if (!current_user_can('manage_options')) {
  wp_die('You do not have sufficient permissions to access this page.');
}
?>
<div class="wrap renewai-pc-wrap">
  <?php include
    // Include header template
    plugin_dir_path(__FILE__) . '../includes/header.php'; ?>
  <div class="renewai-pc-settings-form">
    <h1><?php esc_html_e('Help and Documentation', 'renewai-post-creator'); ?></h1>
    <div class="renewai-pc-settings-section">
      <h2><?php esc_html_e('RenewAI Post Creator Documentation', 'renewai-post-creator'); ?></h2>
      <h3><?php esc_html_e('Introduction', 'renewai-post-creator'); ?></h3>
      <p><?php
          esc_html_e('RenewAI Post Creator is a WordPress plugin designed to generate blog post content using various AI models. The free version supports OpenAI, while the premium version adds support for Anthropi, Google Gemini and Perplexity. This plugin allows users to input keywords or ideas and receive well-structured blog posts in return.', 'renewai-post-creator');
          ?>
      </p>
      <hr>
      <h3><?php esc_html_e('Key Features', 'renewai-post-creator'); ?></h3>
      <h4><?php esc_html_e('Free Version:', 'renewai-post-creator'); ?></h4>
      <ul>
        <li><?php esc_html_e('Support for OpenAI API GPT 4, GPT 4 Turbo, GPT 3.5 Turbo.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('User-friendly settings page for secure API key management and model selection.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Meta box in the post and page editors for easy content generation.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Logging functionality for debugging and monitoring.', 'renewai-post-creator'); ?></li>
      </ul>
      <h4><?php esc_html_e('Premium Version (includes all free features plus):', 'renewai-post-creator'); ?></h4>
      <ul>
        <li><?php esc_html_e('Supports additional AI providers: Anthropic, Google Gemini and Perplexity.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Access to all available AI models.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Prompt library feature for efficient content generation.', 'renewai-post-creator'); ?></li>
      </ul>
      <hr>
      <h3><?php esc_html_e('Note on API Pricing:', 'renewai-post-creator'); ?></h3>
      <p><?php esc_html_e('RenewAI Post Creator uses external AI services, and users are responsible for any associated API costs. Please review the pricing details for each provider:', 'renewai-post-creator'); ?></p>
      <ul>
        <li><?php esc_html_e('OpenAI: ', 'renewai-post-creator'); ?> <a href="https://openai.com/api/pricing/" target="_blank">https://openai.com/api/pricing/</a> </li>
        <li><?php esc_html_e('Anthropic (Premium only): ', 'renewai-post-creator'); ?> <a href="https://www.anthropic.com/pricing#anthropic-api" target="_blank">https://www.anthropic.com/pricing#anthropic-api</a></li>
        <li><?php esc_html_e('Gemini (Premium only): ', 'renewai-post-creator'); ?> <a href="https://cloud.google.com/vertex-ai/docs/generative-ai/pricing" target="_blank">https://cloud.google.com/vertex-ai/docs/generative-ai/pricing</a></li>
        <li><?php esc_html_e('Perplexity (Premium only): ', 'renewai-post-creator'); ?> <a href="https://docs.perplexity.ai/guides/pricing" target="_blank">https://docs.perplexity.ai/guides/pricing</a></li>
      </ul>
      <p><?php esc_html_e('API usage and costs are determined by the respective providers and may change. We recommend reviewing their pricing pages for the most up-to-date information.', 'renewai-post-creator'); ?></p>
      <hr>
      <h3><?php esc_html_e('Installation', 'renewai-post-creator'); ?></h3>
      <h4><?php esc_html_e('Via WordPress Dashboard', 'renewai-post-creator'); ?></h4>
      <ol>
        <li><?php esc_html_e('Go to the Plugins section in your WordPress admin panel.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Click on Add New.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Search for RenewAI Post Creator.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Click Install Now and then Activate.', 'renewai-post-creator'); ?></li>
      </ol>
      <h4><?php esc_html_e('Manual Installation', 'renewai-post-creator'); ?></h4>
      <ol>
        <li><?php esc_html_e('Download the plugin ZIP file.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Go to the Plugins section in your WordPress admin panel.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Click on Add New and then Upload Plugin.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Choose the downloaded ZIP file and click Install Now.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Activate the plugin after installation.', 'renewai-post-creator'); ?></li>
      </ol>
      <hr>
      <h3><?php esc_html_e('Requirements', 'renewai-post-creator'); ?></h3>
      <ul>
        <li><?php esc_html_e('WordPress version 5.0 or higher.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('PHP version 7.0 or higher.', 'renewai-post-creator'); ?></li>
      </ul>
      <hr>
      <h3><?php esc_html_e('Configuration', 'renewai-post-creator'); ?></h3>
      <ol>
        <li><?php esc_html_e('Navigate to Settings > RenewAI Post Creator in your WordPress admin panel.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('For the free version, select OpenAI as your API Provider.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Enter your OpenAI API Key.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Choose the desired AI model from the dropdown menu.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Set the System Prompt to customize the content generation.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Click Save Changes to apply your settings.', 'renewai-post-creator'); ?></li>
      </ol>
      <hr>
      <h3><?php esc_html_e('Obtaining API Keys', 'renewai-post-creator'); ?></h3>
      <p><?php esc_html_e('To use the AI providers, you\'ll need to obtain API keys from their respective platforms. Here\'s how to get started with each:', 'renewai-post-creator'); ?></p>

      <h4><?php esc_html_e('OpenAI (Free Version)', 'renewai-post-creator'); ?></h4>
      <ol>
        <li><?php esc_html_e('Visit the', 'renewai-post-creator'); ?> <a href="https://platform.openai.com/signup" target="_blank"><?php esc_html_e('OpenAI website', 'renewai-post-creator'); ?></a> <?php esc_html_e('and sign up for an account.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Once logged in, navigate to the API section.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Create a new API key and copy it.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Paste the API key into the OpenAI API Key field in the plugin settings and click "Save Changes".', 'renewai-post-creator'); ?></li>
      </ol>

      <h4><?php esc_html_e('Anthropic (Premium Version)', 'renewai-post-creator'); ?></h4>
      <ol>
        <li><?php esc_html_e('Go to the', 'renewai-post-creator'); ?> <a href="https://www.anthropic.com" target="_blank"><?php esc_html_e('Anthropic website', 'renewai-post-creator'); ?></a> <?php esc_html_e('and sign up for an account.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Navigate to the API section in your account dashboard.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Generate a new API key and copy it.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Enter the API key in the Anthropic API Key field in the plugin settings and click "Save Changes".', 'renewai-post-creator'); ?></li>
      </ol>

      <h4><?php esc_html_e('Gemini (Premium Version)', 'renewai-post-creator'); ?></h4>
      <ol>
        <li><?php esc_html_e('Visit the', 'renewai-post-creator'); ?> <a href="https://makersuite.google.com/app/apikey" target="_blank"><?php esc_html_e('Google AI Studio', 'renewai-post-creator'); ?></a> <?php esc_html_e('and sign in with your Google account.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Click on "Get API key" and create a new API key.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Copy the generated API key.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Paste the API key into the Gemini API Key field in the plugin settings and click "Save Changes".', 'renewai-post-creator'); ?></li>
      </ol>

      <h4><?php esc_html_e('Perplexity (Premium Version)', 'renewai-post-creator'); ?></h4>
      <ol>
        <li><?php esc_html_e('Go to the', 'renewai-post-creator'); ?> <a href="https://www.perplexity.ai" target="_blank"><?php esc_html_e('Perplexity AI website', 'renewai-post-creator'); ?></a> <?php esc_html_e('and create an account.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Navigate to the API section in your account settings.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Generate a new API key and copy it.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Enter the API key in the Perplexity API Key field in the plugin settings and click "Save Changes".', 'renewai-post-creator'); ?></li>
      </ol>

      <p><?php esc_html_e('Remember to keep your API keys secure and never share them publicly. If you suspect your key has been compromised, regenerate it immediately from the respective provider\'s website.', 'renewai-post-creator'); ?></p>
      <hr>
      <h3><?php esc_html_e('Usage', 'renewai-post-creator'); ?></h3>
      <ol>
        <li><?php esc_html_e('Go to the Post or Page editor in WordPress.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Locate the RenewAI Post Creator meta box in the top right corner.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Enter keywords or ideas in the text area provided.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('Click the Generate Post button to create content.', 'renewai-post-creator'); ?></li>
        <li><?php esc_html_e('The generated content will appear in the editor, ready for editing, saving, or publishing.', 'renewai-post-creator'); ?></li>
      </ol>
      <hr>
      <h3><?php esc_html_e('Troubleshooting', 'renewai-post-creator'); ?></h3>
      <ul>
        <li><?php esc_html_e('Issue: Generated content is being cut off, truncated or incomplete.', 'renewai-post-creator'); ?>
          <ul>
            <li><?php esc_html_e('Solution: Ensure that the maximum token limit is set to a higher value for the content you are trying to generate.', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('Check the log for more information.', 'renewai-post-creator'); ?></li>
          </ul>
        </li>
        <li><?php esc_html_e('Issue: Titles are not generated.', 'renewai-post-creator'); ?>
          <ul>
            <li><?php esc_html_e('Solution: Ensure that the maximum token limit is set to a higher value for the content you are trying to generate. Titles are returned at the end of the generated content, so if the max tokens is set too low, the title will not be generated.', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('Check the log for more information.', 'renewai-post-creator'); ?></li>
          </ul>
        </li>
        <li><?php esc_html_e('Issue: API key not working.', 'renewai-post-creator'); ?>
          <ul>
            <li><?php esc_html_e('Solution: Ensure that the API key is correctly entered and that it has the necessary permissions.', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('Check the log for more information.', 'renewai-post-creator'); ?></li>
          </ul>
        </li>
        <li><?php esc_html_e('Issue: Generated content is not appearing.', 'renewai-post-creator'); ?>
          <ul>
            <li><?php esc_html_e('Solution: Check the browser console for any JavaScript errors and ensure that the AJAX requests are being sent correctly.', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('Check the log for more information.', 'renewai-post-creator'); ?></li>
          </ul>
        </li>
      </ul>
      <hr>
      <h3><?php esc_html_e('Using the Prompt Library', 'renewai-post-creator'); ?></h3>
      <p><?php esc_html_e('Premium users get access to the Prompt Library feature, which allows you to manage and reuse prompts efficiently:', 'renewai-post-creator'); ?></p>
      <ol>
        <li><strong><?php esc_html_e('Selecting a Prompt:', 'renewai-post-creator'); ?></strong>
          <ul>
            <li><?php esc_html_e('Go to the RenewAI Post Creator settings page', 'renewai-post-creator'); ?> </li>
            <li><?php esc_html_e('Find the "Prompt Library" dropdown in the settings', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('Choose a prompt from the list', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('Click "Apply Prompt" to use it in the current system prompt field', 'renewai-post-creator'); ?></li>
          </ul>
        </li>
        <li><strong><?php esc_html_e('Creating a Custom Prompt:', 'renewai-post-creator'); ?></strong>
          <ul>
            <li><?php esc_html_e('Modify the system prompt in any of the provider sections (OpenAI, Anthropic, or Perplexity)', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('When you make changes, you\'ll see a message "Prompt modified. Click below to save as a new prompt."', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('Click the "Add to Prompt Library" button', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('Enter a name for your new prompt when prompted', 'renewai-post-creator'); ?></li>
          </ul>
        </li>
        <li><strong><?php esc_html_e('Managing Prompts:', 'renewai-post-creator'); ?></strong>
          <ul>
            <li><?php esc_html_e('To delete a prompt, select it from the dropdown and click the "Delete Selected Prompt" button', 'renewai-post-creator'); ?></li>
            <li><?php esc_html_e('Custom prompts can be modified by applying them, making changes, and saving as a new prompt', 'renewai-post-creator'); ?></li>
          </ul>
        </li>
      </ol>
      <p><?php esc_html_e('The Prompt Library allows you to build a collection of effective prompts for different types of content, improving your workflow and consistency in content generation.', 'renewai-post-creator'); ?></p>
      <hr>
      <h3><?php esc_html_e('FAQs', 'renewai-post-creator'); ?></h3>
      <h4><?php esc_html_e('Q: What\'s the difference between the free and premium versions?', 'renewai-post-creator'); ?></h4>
      <p><?php esc_html_e('A: The free version supports OpenAI integration, while the premium version adds support for Anthropic and Perplexity, includes a prompt library feature, and allows access to all available AI models. Premium users also get exclusive access to future page builder add-ons.', 'renewai-post-creator'); ?></p>
      <h4><?php esc_html_e('Q: Can I use multiple API providers in the free version?', 'renewai-post-creator'); ?></h4>
      <p><?php esc_html_e('A: No, the free version only supports OpenAI. To use multiple providers, you\'ll need to upgrade to the premium version.', 'renewai-post-creator'); ?></p>
      <h4><?php esc_html_e('Q: What happens if the API provider is down?', 'renewai-post-creator'); ?></h4>
      <p><?php esc_html_e('A: The plugin will log an error message, and content generation will not be possible until the service is restored.', 'renewai-post-creator'); ?></p>
      <hr>
      <h3><?php esc_html_e('Support', 'renewai-post-creator'); ?> </h3>
      <p><?php esc_html_e('For support, please contact us via email.', 'renewai-post-creator'); ?> <a href="mailto:success+support@perpetuaiconsult.com?subject=Support%20Request">Email Support</a></p>
    </div>
  </div>
</div>