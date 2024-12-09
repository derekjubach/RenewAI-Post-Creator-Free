=== RenewAI Post Creator ===
Contributors: djubach, freemius
Tags: content generation, AI content, OpenAI, Gemini, Perplexity, Anthropic, blog posts, Gutenberg
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.3.4
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generate high-quality blog post content using AI models from OpenAI, with premium features for Anthropic, Google Gemini and Perplexity.

== Description ==

RenewAI Post Creator is a powerful WordPress plugin that leverages advanced AI models to generate high-quality blog post content. The free version supports OpenAI, while the premium version offers additional AI providers including Anthropic and Perplexity, providing flexibility and cutting-edge content generation capabilities.

Key Features:

* Free Version:
  - Support for OpenAI API GPT 4, GPT 4 Turbo, GPT 3.5 Turbo 
  - Easy-to-use interface integrated into the WordPress post and page editors
  - Customizable system prompts for OpenAI
  - Debug mode for troubleshooting

* Premium Version (includes all free features plus):
  - Support for additional AI providers: All OpenAI models, Anthropic, Perplexity, and Google's Gemini
  - Access to all available AI models
  - Prompt library feature for efficient content generation
  - Future add-ons for popular page builders (sold separately)

Whether you're a blogger looking to streamline your content creation process or a website owner wanting to keep your site updated with fresh content, RenewAI Post Creator is the perfect tool to help you generate engaging blog posts quickly and efficiently.

API usage and costs are determined by the respective providers and may change. We recommend reviewing their pricing pages for the most up-to-date information.

*Note: This plugin does not support page builders such as Elementor, WP Bakery or Beaver Builder. Support for these page builders will be added in future releases.

== Installation ==

=== Via WordPress Dashboard ===

1. Go to the Plugins section in your WordPress admin panel.
2. Click on Add New.
3. Search for RenewAI Post Creator.
4. Click Install Now and then Activate.

=== Manual Installation ===

1. Download the plugin ZIP file.
2. Go to the Plugins section in your WordPress admin panel.
3. Click on Add New and then Upload Plugin.
4. Choose the downloaded ZIP file and click Install Now.
5. Activate the plugin after installation.

== Frequently Asked Questions ==

= Which AI providers are supported? =

The free version of RenewAI Post Creator supports OpenAI. The premium version adds support for Anthropic, Perplexity, and Google's Gemini AI providers as well as access to all available AI models.

= Do I need separate API keys for each provider? =

Yes, you will need to obtain API keys from each provider you wish to use. The plugin allows you to enter and manage these keys separately.

= What's the difference between the free and premium versions? =

The free version supports OpenAI integration with limited models, while the premium version adds support for Anthropic, Google Gemini and Perplexity, includes a prompt library feature, and allows access to all available AI models.

= Is there a limit to how much content I can generate? =

The content generation limits depend on the API restrictions of each provider. Please refer to the pricing and usage terms of your chosen AI provider.

Note on API Pricing:

RenewAI Post Creator uses external AI services, and users are responsible for any associated API costs. Please review the pricing details for each provider:

- [OpenAI](https://openai.com/api/pricing/)
- [Anthropic](https://www.anthropic.com/pricing#anthropic-api)
- [Gemini](https://cloud.google.com/vertex-ai/docs/generative-ai/pricing)
- [Perplexity](https://docs.perplexity.ai/guides/pricing)

== Third-Party Services ==

This plugin uses the following third-party services:

1. OpenAI API (Free & Premium Versions)
   - Purpose: Used for generating blog post content.
   - Circumstances of use: When a user requests content generation within the WordPress admin area.
   - [OpenAI website](https://openai.com/)
   - [Terms of Service](https://openai.com/policies/terms-of-use)
   - [Privacy Policy](https://openai.com/policies/privacy-policy)

2. Anthropic API (Premium Version Only)
   - Purpose: Used for generating blog post content.
   - Circumstances of use: When a premium user requests content generation using Anthropic's models.
   - [Anthropic website](https://www.anthropic.com/)
   - [Terms of Service](https://www.anthropic.com/legal/consumer-terms)
   - [Privacy Policy](https://www.anthropic.com/legal/privacy)

3. Google GeminiGoogle Gemini API (Premium Version Only)
   - Purpose: Used for generating blog post content.
   - Circumstances of use: When a premium user requests content generation using Google's Gemini models.
   - [Google Gemini website](https://gemini.google.com)
   - [Terms of Service](https://ai.google.dev/gemini-api/terms)
   - [Privacy Policy](https://support.google.com/gemini/answer/13594961)

4. Perplexity API (Premium Version Only)
   - Purpose: Used for generating blog post content.
   - Circumstances of use: When a premium user requests content generation using Perplexity's models.
   - [Perplexity website](https://www.perplexity.ai/)
   - [Terms of Service](https://www.perplexity.ai/hub/legal/terms-of-service)
   - [Privacy Policy](https://www.perplexity.ai/hub/legal/privacy-policy)

Please note that by using this plugin, you are agreeing to share certain data with these external services. The free version only uses OpenAI, while the premium version provides access to all listed services. Ensure you are compliant with any relevant data protection regulations in your jurisdiction when using these services.

For more detailed information about the premium version's features and services, please refer to our website or contact our support team.

== Screenshots ==

1. RenewAI Post Creator settings page
2. API keys management screen
3. Block Editor screen
4. Classic Editor screen
5. Generating content in the Block Editor
6. Help and Support screen

== Changelog ==

= 1.3.4 =
* Fixed issue with prompt library Add/Delete buttons (Premium)
* Namespace update for delete log file button
* Author and Author URI updated to FullScope (Premium)
* Updated Freemius SDK to version 2.9.0

= 1.3.3 =
* Compatibility for WordPress 6.7

= 1.3.2 =
Allow meta box on pages as well as posts.

= 1.3.1 =
Additional adjustments for WordPress.org compliance.

= 1.3 =
Use Composer to manage dependencies and improve security.

= 1.2.1 =
* Adjustments for WordPress.org compliance
* Security: Gloabal hardening of functions and security

= 1.2.01 =
* Added feature to delete log file
* Bug: Fixed newsletter signup email recipient

= 1.1 =
* Added Prompt Library feature for managing and reusing prompts
* Improved user interface for system prompt management

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.3.4 =
Fixed issue with prompt library Add/Delete buttons (Premium)
Namespace update for delete log file button
Author and Author URI updated to FullScope (Premium)
Updated Freemius SDK to version 2.9.0
