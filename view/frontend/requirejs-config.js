var config = {
    map: {
        '*': {
            nouislider: 'BoxalinoClientProject_BoxalinoIntegration/js/nouislider',
            rtuxApiTracker: 'BoxalinoClientProject_BoxalinoIntegration/js/rtuxApiTracker'
        }
    },
    deps: ["rtuxApiHelper"],
    config: {
        mixins: {
            'Magento_Search/js/form-mini': {
                'BoxalinoClientProject_BoxalinoIntegration/js/rtuxApiAutocompleteMixin': true
            }
        }
    }
};
