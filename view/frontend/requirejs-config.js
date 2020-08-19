var config = {
    map: {
        '*': {
            nouislider: 'Boxalino_RealTimeUserExperienceIntegration/js/nouislider'
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
