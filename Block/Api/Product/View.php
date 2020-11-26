<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Block\Api\Product;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use BoxalinoClientProject\BoxalinoIntegration\Block\Api\Product;

/**
 * Catalog product view recommendations block
 *
 * Can be used as the preference for:
 * Magento\Catalog\Block\Product\ProductList\Related
 * Magento\Catalog\Block\Product\ProductList\Crosssell
 * Magento\Catalog\Block\Product\ProductList\Upsell
 *
 * Can inherit directly from the replaced blocks (for a fallback strategy)
 *
 * Update the template to support the API JS tracker mark-ups
 */
class View extends Product
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
