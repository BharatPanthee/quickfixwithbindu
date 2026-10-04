wp.customize.controlConstructor['yummy-bites-visibility'] = wp.customize.Control.extend({
    ready: function () {
        'use strict';

        var control = this;

        // Initialize the checkboxValue with the current setting value
        var checkboxValues = control.setting.get();

        // Update the checkboxes based on the initial saved values (split comma-separated string into an array)
        control.container.find('input[type="checkbox"]').each(function () {
            if (checkboxValues.includes(jQuery(this).val())) {
                jQuery(this).prop('checked', true);
            }
        });

        // Listen for changes on the checkboxes
        control.container.on('change', 'input[type="checkbox"]', function () {
            // Get all checked checkboxes and join their values into a comma-separated string
            checkboxValues = control.container.find('input[type="checkbox"]:checked')
                .map(function () {
                    return this.value;
                })
                .get()
                .join(',');

            // Update the hidden input and trigger the change
            control.container.find('input[type="hidden"]').val(checkboxValues).trigger('change');

            // Save the updated value in the Customizer setting
            control.setting.set(checkboxValues);
        });
    }
});