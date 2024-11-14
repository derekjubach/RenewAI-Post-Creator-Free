jQuery(document).ready(function ($) {
	// Custom Alerts
	function showCustomAlert(message) {
		$('#renewai-pc-custom-alert-message').text(message);
		$('#renewai-pc-custom-alert').show();
	}

	$('#renewai-pc-custom-alert-close').on('click', function () {
		$('#renewai-pc-custom-alert').hide();
	});

	// Generate Content
	$('#renewai-generate').on('click', function (e) {
		e.preventDefault();

		var $button = $(this);
		var keywords = $('#renewai-keywords').val();
		var $status = $('#renewai-status-text');
		var $spinner = $('#renewai-spinner');

		// Disable the button and show the spinner
		$button.prop('disabled', true);
		$status.text('Generating content...');
		$spinner.css('visibility', 'visible');

		$.ajax({
			url: renewai_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'generate_post_content',
				keywords: keywords,
				nonce: renewai_ajax.nonce,
			},
			success: function (response) {
				if (response.success && response.data && response.data.content) {
					var contentInserted = false;

					if (renewai_ajax.is_gutenberg) {
						// Gutenberg editor
						const { parse } = wp.blocks;
						const { resetBlocks } = wp.data.dispatch('core/block-editor');

						// Parse the HTML content into blocks
						const blocks = parse(response.data.content);

						// Replace all existing blocks with the new blocks
						resetBlocks(blocks);
						contentInserted = true;
					} else {
						// Check for default WordPress editor
						var defaultEditorId = 'content';
						if (
							typeof tinyMCE !== 'undefined' &&
							tinyMCE.get(defaultEditorId) &&
							!tinyMCE.get(defaultEditorId).isHidden()
						) {
							tinyMCE.get(defaultEditorId).setContent(response.data.content);
							contentInserted = true;
						} else if ($('#' + defaultEditorId).length) {
							$('#' + defaultEditorId).val(response.data.content);
							contentInserted = true;
						} else {
							// Check for ACF WYSIWYG editor
							var acfEditor = $('textarea[id^="acf-editor-"]').first();
							if (acfEditor.length) {
								var acfEditorId = acfEditor.attr('id');
								if (
									typeof tinyMCE !== 'undefined' &&
									tinyMCE.get(acfEditorId) &&
									!tinyMCE.get(acfEditorId).isHidden()
								) {
									tinyMCE.get(acfEditorId).setContent(response.data.content);
									contentInserted = true;
								} else {
									acfEditor.val(response.data.content);
									contentInserted = true;
								}
							}
						}
					}

					if (contentInserted) {
						$status.html('<p>Content generated and inserted successfully!</p>');
						if (response.data.titles) {
							displayTitleSuggestions(response.data.titles);
						}
					} else {
						$status.html(
							'<p>Content generated, but no suitable editor found to insert it. Please copy the content manually:</p><textarea rows="5" style="width: 100%;">' +
								response.data.content +
								'</textarea>'
						);
					}
				} else {
					$status.html(
						'<p>Error: Unable to generate content. Please try again.</p>'
					);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				$status.html('<p>An error occurred. Please try again.</p>');
			},
			complete: function () {
				// Re-enable the button and hide the spinner
				$button.prop('disabled', false);
				$spinner.css('visibility', 'hidden');
			},
		});
	});

	// Display title suggestions
	function displayTitleSuggestions(titles) {
		// Remove any existing title suggestions
		$('#renewai-pc-title-suggestions').remove();

		var $suggestionsContainer = $(
			'<div id="renewai-pc-title-suggestions"></div>'
		);
		$suggestionsContainer.append('<h4>Suggested Titles:</h4>');
		var $list = $('<ul></ul>');

		titles.forEach(function (title) {
			// Remove outer quotes if present
			title = title.replace(/^["'](.+(?=["']$))["']$/, '$1');
			var $li = $('<li class="renewai-pc-title-suggestion"></li>');
			var $icon = $(
				'<span class="dashicons dashicons-insert" title="Insert this title"></span>'
			);
			var $titleText = $('<span class="renewai-pc-title-text"></span>').text(
				title
			);

			$icon.on('click', function () {
				insertTitle(title);
			});

			$li.append($icon, $titleText);
			$list.append($li);
		});

		$suggestionsContainer.append($list);
		$('#renewai-pc-post-creator-meta-box').append($suggestionsContainer);
	}

	// Insert suggested title
	function insertTitle(title) {
		var $titleField = $('#title');
		$titleField.val(title);

		// For classic editor
		if ($titleField.length) {
			var $notification = $(
				'<div class="notice notice-success is-dismissible"><p>Title inserted successfully!</p></div>'
			);
			$('#renewai-pc-post-creator-meta-box').prepend($notification);
			setTimeout(function () {
				$notification.fadeOut(function () {
					$(this).remove();
				});
			}, 3000);
		}

		// If using Gutenberg editor, we need to update the title block
		if (wp.data && wp.data.select('core/editor')) {
			wp.data.dispatch('core/editor').editPost({ title: title });
		}
	}

	// Remove the existing click handler
	$('#refresh_openai_models').off('click');

	

	

	// Delete log file
	$('#renewai-pc-delete-log-file').on('click', function (e) {
		e.preventDefault();
		if (confirm('Are you sure you want to delete the log file?')) {
			$.ajax({
				url: renewai_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'delete_renewai_log_file',
					nonce: renewai_ajax.nonce,
				},
				success: function (response) {
					if (response.success) {
						showCustomAlert(response.data);
						// Hide the buttons and show "No log file exists" message
						$(e.target)
							.closest('p')
							.replaceWith('<p>' + renewai_ajax.no_log_file_text + '</p>');
					} else {
						showCustomAlert(response.data);
					}
				},
				error: function () {
					showCustomAlert('An error occurred. Please try again.');
				},
			});
		}
	});

	// Settings page - API provider select
	// Handle API provider change
	$('#renewai_api_provider').on('change', function () {
		var selectedProvider = $(this).val();
		$('.provider-settings').hide();
		$('#provider_settings').hide();

		if (selectedProvider && selectedProvider !== '') {
			$('#provider_settings').show();
			$('#' + selectedProvider + '_settings').show();
		}
	});
	// Trigger change event on page load to set initial state
	$('#renewai_api_provider').trigger('change');
});

console.log('API Keys page loaded');
//API Keys page - Trigger change event on page load to set initial state
function toggleApiKeyField(provider) {
	console.log('toggleApiKeyField called with provider:', provider);
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
