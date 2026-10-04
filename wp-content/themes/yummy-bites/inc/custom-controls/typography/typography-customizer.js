/**
 * Typography Control JS
 *
 * This file includes several helper functions to run the typography control.
 * 
 * @since 1.0.1
 * Preset Styling added since Yummy Bites 1.2.0
 */
(function (api) {

	api.controlConstructor['yummy-bites-typography'] = api.Control.extend({
		ready: function () {
			var control = this;

			control.container.on('change', '.yummy-bites-font-family select',
				function () {
					var _this = jQuery(this),
						_value = _this.val(),
						_categoryID = _this.attr('data-category'),
						_variantsID = _this.attr('data-variants');

					// Set our font family
					control.settings['family'].set(_this.val());

					// Bail if our controls don't exist
					if ('undefined' == typeof control.settings['category'] || 'undefined' == typeof control.settings['variant']) {
						return;
					}

					setTimeout(function () {
						// Send our request to the yummy_bites_get_all_google_fonts_ajax function
						var response = jQuery.getJSON({
							type: 'POST',
							url: ajaxurl,
							data: {
								action: 'yummy_bites_get_all_google_fonts_ajax',
								yummy_bites_customize_nonce: yummy_bites_customize.nonce
							},
							async: false,
							dataType: 'json',
						});

						// Get our response
						var fonts = response.responseJSON;

						// Create an ID from our selected font
						var id = _value.split(' ').join('_').toLowerCase();

						// Set our values if we have them
						if (id in fonts) {

							// Get existing variants if this font is already selected
							var got_variants = false;
							jQuery('.yummy-bites-font-family select').not(_this).each(function (key, select) {
								var parent = jQuery(this).closest('.yummy-bites-font-family');

								if (_value == jQuery(select).val() && _this.data('category') !== jQuery(select).data('category')) {
									if (!got_variants) {
										updated_variants = jQuery(parent.next('.yummy-bites-font-variant').find('select')).val();
										got_variants = true;
									}
								}
							});

							// We're using a Google font, so show the variants field
							_this.closest('.yummy-bites-font-family').next('div').show();

							// Remove existing variants
							jQuery('select[name="' + _variantsID + '"]').find('option').remove();

							// Populate our select input with available variants
							jQuery.each(fonts[id].variants, function (key, value) {
								jQuery('select[name="' + _variantsID + '"]').append(jQuery('<option></option>').attr('value', value).text(value));
							});

							// Set our variants
							if (!got_variants) {
								control.settings['variant'].set(fonts[id].variants);
							} else {
								control.settings['variant'].set(updated_variants);
							}

							// Set our font category
							control.settings['category'].set(fonts[id].category);
							jQuery('input[name="' + _categoryID + '"').val(fonts[id].category);
						} else {
							_this.closest('.yummy-bites-font-family').next('div').hide();
							control.settings['category'].set('')
							control.settings['variant'].set('')
							jQuery('input[name="' + _categoryID + '"').val('');
							jQuery('select[name="' + _variantsID + '"]').find('option').remove();
						}
					}, 25);
				}
			);

			control.container.on('change', '.yummy-bites-font-variant select',
				function () {
					var _this = jQuery(this);
					var variants = _this.val();

					control.settings['variant'].set(variants);

					jQuery('.yummy-bites-font-variant select').each(function (key, value) {
						var this_control = jQuery(this).closest('li').attr('id').replace('customize-control-', '');
						var parent = jQuery(this).closest('.yummy-bites-font-variant');
						var font_val = api.control(this_control).settings['family'].get();

						if (font_val == control.settings['family'].get() && _this.attr('name') !== jQuery(value).attr('name')) {
							jQuery(parent.find('select')).not(_this).val(variants).triggerHandler('change');
							api.control(this_control).settings['variant'].set(variants);
						}
					});
				}
			);

			control.container.on('change', '.yummy-bites-font-category input',
				function () {
					control.settings['category'].set(jQuery(this).val());
				}
			);

			control.container.on('change', '.yummy-bites-font-weight select',
				function () {
					control.settings['weight'].set(jQuery(this).val());
				}
			);

			control.container.on('change', '.yummy-bites-font-transform select',
				function () {
					control.settings['transform'].set(jQuery(this).val());
				}
			);

		}
	});

})(wp.customize);

jQuery(document).ready(function ($) {

	$('.yummy-bites-font-family select').select2();

	$('.yummy-bites-font-variant').each(function (key, value) {
		var _this = $(this);
		var value = _this.data('saved-value');

		if (value) {
			value = value.toString().split(',');
		}

		_this.find('select').select2().val(value).trigger('change.select2');
	});

	$(".yummy-bites-font-family").each(function (key, value) {
		var _this = $(this);
		if ($.inArray(_this.find('select').val(), typography_defaults) !== -1) {
			_this.next('.yummy-bites-font-variant').hide();
		}
	});

	// Update Typography data on the basis of selected style presets in the customizer.
	function yummy_bites_update_typography_preset_style(preset, revert = false, addons = false) {
		let typography = '';
		if(revert){
			typography = yummy_bites_typography_presets['default'][preset];
		} else {
			let checkedInput = document.querySelector('input[name="_customize-radio-typography_preset_style"]:checked');
			let style = 'typo_preset_' + checkedInput.value;
			typography = yummy_bites_typography_presets[style][preset];
		}
		
		if(addons){
			let typoControl = [
				'#customize-control-' + preset + '_group .yummy-bites-font-family select',
			];
			let variantControl = [
				'#customize-control-' + preset + '_group .yummy-bites-font-variant select',
			];

			if(yummy_bites_typography_presets.proActive){
				if(preset == 'primary_font'){
					if(yummy_bites_typography_presets.extensions.includes('notification-bar')){
						typoControl.push(
							'#customize-control-notification_typography_group .yummy-bites-font-family select',
						);
						variantControl.push(
							'#customize-control-notification_typography_group .yummy-bites-font-variant select',
						);
					}
				}
			}

			typoControl.forEach(function(selector){
				let item = $(selector);
				item.val(typography).trigger('change');
			});
			variantControl.forEach(function(selector){
				let item = $(selector);
				item.val('').trigger('change');
			});
		} else {
			let typoControl = $('#customize-control-' + preset + '_group .yummy-bites-font-family select');
			let variantControl = $('#customize-control-' + preset + '_group .yummy-bites-font-variant select');
			typoControl.val(typography).trigger('change');
			variantControl.val('').trigger('change');
		}
	}

	// Update the typography control when a new preset is selected.
	$('body').on('click', '#input_typography_preset_style', function(event) {
		yummy_bites_update_typography_preset_style('primary_font', false, true);
		yummy_bites_update_typography_preset_style('heading_one');
		yummy_bites_update_typography_preset_style('heading_two');
		yummy_bites_update_typography_preset_style('heading_three');
		yummy_bites_update_typography_preset_style('heading_four');
		yummy_bites_update_typography_preset_style('heading_five');
		yummy_bites_update_typography_preset_style('heading_six');
    });

	$('body').on('click', '.typo-revert-change', function(event) {
        yummy_bites_update_typography_preset_style('primary_font', true, true);
		yummy_bites_update_typography_preset_style('heading_one', true);
		yummy_bites_update_typography_preset_style('heading_two', true);
		yummy_bites_update_typography_preset_style('heading_three', true);
		yummy_bites_update_typography_preset_style('heading_four', true);
		yummy_bites_update_typography_preset_style('heading_five', true);
		yummy_bites_update_typography_preset_style('heading_six', true);
    });

});
