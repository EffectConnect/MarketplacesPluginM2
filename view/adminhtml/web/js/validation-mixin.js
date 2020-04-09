define([
    'jquery',
    'EffectConnect_Marketplaces/js/lib/cron-validator'
], function ($, validator) {
    return function () {
        $.validator.addMethod(
            'validate-cron',
            function (value) {
                return validator.isValidCron(value);
            },
            $.mage.__('Cron expression is not valid.')
        );
    }
});