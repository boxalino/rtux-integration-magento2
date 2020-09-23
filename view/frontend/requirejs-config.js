var config = {
    map: {
        '*': {
            nouislider: 'BoxalinoClientProject_BoxalinoRealTimeUserExperienceIntegration/js/nouislider',
            rtuxApiTracker: 'BoxalinoClientProject_BoxalinoRealTimeUserExperienceIntegration/js/rtuxApiTracker'
        }
    },
    deps: ["rtuxApiHelper"],
    config: {
        mixins: {
            'Magento_Search/js/form-mini': {
                'BoxalinoClientProject_BoxalinoRealTimeUserExperienceIntegration/js/rtuxApiAutocompleteMixin': true
            }
        }
    }
};
