define([
    'jquery',
    'underscore',
    'jquery/ui',
    'mage/mage',
    'Boxalino_RealTimeUserExperience/js/rtuxApiHelper'
], function ($, _, rtuxApiHelper){
    'use strict';

    $.widget('boxalino.rtuxApiAutocomplete', {

        /** @inheritdoc */
        _create: function () {
            console.log(rtuxApiHelper);
            var request = rtuxApiHelper.getApiRequestData("autocomplete", 10, {"sugestionHitCount":5});
            console.log(request);
            console.log("in _create;");
        }
    });

    return $.boxalino.rtuxApiAutocomplete;
});
