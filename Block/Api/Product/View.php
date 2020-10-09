<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Block\Api\Product;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Block\Catalog\Product\View as ApiView;

/**
 * Catalog product view recommendations block
 *
 * Can be used as the preference for:
 * Magento\Catalog\Block\Product\ProductList\Related
 * Magento\Catalog\Block\Product\ProductList\Crosssell
 * Magento\Catalog\Block\Product\ProductList\Upsell
 *
 * Can inherit directly from the replaced blocks (for a fallback strategy)
 */
class View extends ApiView
    implements ApiRendererInterface
{

    /**
     * The base widget recommendation as configured in the Boxalino Intelligence admin as the master request
     * Property either set as an argument on block, a configuration field or hardcoded
     * @required
     * @return string
     */
    public function getWidget() : string
    {
        return "related";
    }

    /**
     * Property either set as an argument on block, a configuration field or hardcoded
     * @required
     * @return string
     */
    public function getHitCount() : string
    {
        return 6;
    }


}
