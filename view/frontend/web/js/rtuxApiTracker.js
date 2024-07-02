/**
 * RTUX API JS Tracker Widget for Magento2
 *
 * Sends tracking events to the Boxalino RTUX server
 * The dependencies for each tracked event can be custom
 * For the guidelines purposes, the default-Magento2 compatible ones are being used
 */
define([
    'jquery',
    'Boxalino_RealTimeUserExperience/js/api/events/view-page',
    'Boxalino_RealTimeUserExperience/js/api/events/view-navigation',
    'Boxalino_RealTimeUserExperience/js/api/events/view-search',
    'Boxalino_RealTimeUserExperience/js/api/events/view-product',
    'Boxalino_RealTimeUserExperience/js/api/events/add-to-cart',
    'Boxalino_RealTimeUserExperience/js/api/events/purchase',
], function (
    $,
    ViewPageEvent,
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
            if(!$.boxalino.rtuxApiHelper.hasCookieRestriction()) {
                $.boxalino.rtuxApiHelper.useDebugCookie();

                this.events = [];

                this.registerDefaultEvents();
                this.handleEvents();
            }
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
            this.registerEvent(ViewPageEvent);
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
