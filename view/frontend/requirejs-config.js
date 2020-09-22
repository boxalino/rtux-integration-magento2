var config = {
    map: {
        '*': {
            nouislider: 'Boxalino_RealTimeUserExperienceIntegration/js/nouislider',
            rtuxApiTracker: 'Boxalino_RealTimeUserExperienceIntegration/js/rtuxApiTracker'
        }
    },
    deps: ["rtuxApiHelper"],
    config: {
        mixins: {
            'Magento_Search/js/form-mini': {
                'Boxalino_RealTimeUserExperienceIntegration/js/rtuxApiAutocompleteMixin': true
            }
        }
    }
};
