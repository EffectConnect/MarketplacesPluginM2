define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, uiRegistry, select) {
    'use strict';

    return select.extend({

        initialize: function()
        {
            return this._super();
        },

        filterByConnectionId: function (connectionId)
        {
            var source = this.initialOptions,
                result;

            result = _.filter(source, function (item) {
                return (item.connection_id.indexOf(parseInt(connectionId)) >= 0);
            });

            this.setOptions(result);
        }

    });
});