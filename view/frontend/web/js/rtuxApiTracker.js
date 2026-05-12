/**
 * RTUX API JS Tracker Widget for Magento2
 *
 * Sends tracking events to the Boxalino RTUX server
 * The dependencies for each tracked event can be custom
 * For the guidelines purposes, the default-Magento2 compatible ones are being used
 */
define([
    'jquery',
    'Boxalino_RealTimeUserExperience/js/api/events/view-navigation',
    'Boxalino_RealTimeUserExperience/js/api/events/view-search',
    'Boxalino_RealTimeUserExperience/js/api/events/view-product',
    'Boxalino_RealTimeUserExperience/js/api/events/add-to-cart',
    'Boxalino_RealTimeUserExperience/js/api/events/purchase',
], function (
    $,
    ViewNavigationEvent,
    ViewSearchEvent,
    ViewProductEvent,
    AddToCartEvent,
    PurchaseEvent
){
    'use strict';

    $.widget('boxalino.rtuxApiTracker', {

        /**
         * 1. initializes the tracker (sets the active account and required global parameters);
         * 2. registers the events (based on the declared dependencies)
         *
         * @private
         */
        _create: function () {
            if(this.isAllowed()) {
                // uncomment this if the the HTML tracking attributes must be tested
                // do not do it in production environment
                // $.boxalino.rtuxApiHelper.useDebugCookie();

                this.events = [];
                this.registerDefaultEvents();
                this.handleEvents();
            }
        },

        isAllowed: function() {
            if($.boxalino.rtuxApiHelper.hasCookieRestriction()) {
                if($.boxalino.rtuxApiHelper.isUserNotAllowed()) {
                    if($.boxalino.rtuxApiHelper.userCookieConfirmed()) {
                        return true;
                    }
                    $.boxalino.rtuxApiHelper.getApiProfileId();
                    $(document).on('user:allowed:save:cookie', function(event) {
                        $.boxalino.rtuxApiHelper.setUserCookieConsent(true);
                        this._create();
                    }.bind(this));
                    return false;
                }
            }

            return true;
        },

        /**
         * @public
         */
        handleEvents() {
            this.events.forEach(event => {
                if (!event.supports()) {
                    return;
                }
                event.execute();
            });
        },

        /**
         * @public
         */
        registerDefaultEvents() {
            this.registerEvent(ViewProductEvent);
            this.registerEvent(ViewSearchEvent);
            this.registerEvent(ViewNavigationEvent);
            this.registerEvent(AddToCartEvent);
            this.registerEvent(PurchaseEvent);
        },

        /**
         * @public
         * @param event
         */
        registerEvent(event) {
            this.events.push(event);
        },

        disableEvents() {
            this.events.forEach(event => {
                event.disable();
            });
        }
    });

    return $.boxalino.rtuxApiTracker;
});
