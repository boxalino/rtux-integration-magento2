<?php
namespace BoxalinoClientProject\BoxalinoRealTimeUserExperienceIntegration\Api;


/**
 * The interface is mandatory
 * It is mainly used throughout your own integration in order to navigate through different response segments
 *
 * @package Boxalino\RealTimeUserExperience\Api
 */
interface ApiLayoutBlockNameInterface
{

    const RTUX_API_SEARCH_PRODUCT_LIST_BLOCK = 'rtux_api_product_list';

    const RTUX_API_DEFAULT_SEARCH_TITLE_BLOCK = 'rtux_api_search_title';

    const RTUX_API_SUBPHRASES_SEARCH_TITLE_BLOCK = 'rtux_api_title_h1';

    const RTUX_API_NORESULTS_SEARCH_TITLE_BLOCK = 'rtux_api_title_h1';

    const RTUX_API_NAVIGATION_PRODUCT_LIST_BLOCK = 'rtux_api_product_list';

    const RTUX_API_CMS_PRODUCT_LIST_BLOCK = 'rtux_api_product_list';

    const RTUX_API_TOOLBAR = 'rtux_api_toolbar';

    const RTUX_API_PAGINATION = 'rtux_api_pagination';

    const RTUX_API_SORTING = 'rtux_api_sorting';

}
