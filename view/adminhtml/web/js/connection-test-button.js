define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/button',
    'Magento_Ui/js/modal/alert'
], function ($, _, uiRegistry, button, alert) {
    'use strict';

    return button.extend({

        initialize: function()
        {
            return this._super();
        },

        testConnection: function ()
        {
            // Get filled in public/secret key from form.
            var public_key = uiRegistry.get('index = public_key').value(),
                secret_key = uiRegistry.get('index = secret_key').value();

            // Get AJAX url.
            var source = uiRegistry.get(this.provider);
            var ajaxUrl = source.test_connection_url;

            // Do AJAX call to test the credentials.
            $.ajax({
                showLoader: true,
                url: ajaxUrl,
                data: {'public_key': public_key, 'secret_key': secret_key},
                method: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert({content: $.mage.__('The credentials are valid.')});
                    } else {
                        alert({content: $.mage.__('The credentials are NOT valid.')});
                    }
                },
                error: function () {
                    alert({content: $.mage.__('An error occured when testing the credentials.')});
                },
            });
        }

    });
});