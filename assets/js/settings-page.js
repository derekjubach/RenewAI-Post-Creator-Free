jQuery(document).ready(function ($) {
	console.log('settings-page.js loaded');
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
