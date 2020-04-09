define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (_, uiRegistry, dynamicRows) {
    'use strict';

    return dynamicRows.extend({

        initialize: function() {
            return this._super();
        },

        filterByWebsiteId: function (websiteId) {
            var instance = this;
            _.each(this.elems(), (element) => {
                if (parseInt(element.data().website_id) === parseInt(websiteId)) {
                    element.setVisible(true);
                } else {
                    element.setVisible(false);
                }
            });

            // Because of the loading order sometimes the method above fails, this is a backup method.
            setTimeout(function () {
                _.each(instance.elems(), (element) => {
                    if (parseInt(element.data().website_id) === parseInt(websiteId)) {
                        element.setVisible(true);
                    } else {
                        element.setVisible(false);
                    }
                });
            }, 1000);
        }

    });
});
