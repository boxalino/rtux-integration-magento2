<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context;

use Boxalino\RealTimeUserExperience\Service\Api\Util\ContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Util\RequestParametersTrait;
use Boxalino\RealTimeUserExperience\Helper\Configuration as StoreConfigurationHelper;
use Boxalino\RealTimeUserExperienceApi\Framework\Request\ItemContextAbstract;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ItemContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Definition\ItemRequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\IntegrationContextTrait;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Class ItemContext
 * Holds the request properties: widget, hitcount, returnfields, groupby, offset, etc
 *
 * Rewrite this function in order to set/add/remove default API request filters:
 * protected function addFilters(RequestInterface $request) : void
 *
 * NOTICE: THIS ContextInterface ADDS THE DEFAULT FILTERS ON THE REQUEST (visibility, status, root category id)
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context
 */
class ItemContext extends ItemContextAbstract
    implements ItemContextInterface
{
    use ContextTrait;
    use RequestParametersTrait;

    public function __construct(
        RequestTransformerInterface $requestTransformer,
        ParameterFactoryInterface $parameterFactory,
        ItemRequestDefinitionInterface $requestDefinition,
        StoreConfigurationHelper $storeConfigurationHelper
    ) {
        parent::__construct($requestTransformer, $parameterFactory);
        $this->storeConfigurationHelper = $storeConfigurationHelper;
        /** prepare context with configurations */
        $this->setRequestDefinition($requestDefinition);
    }

    /**
     * Product visibility on a CMS context
     * @return array
     */
    public function getContextVisibility() : array
    {
        return [Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG];
    }

    /**
     * For the item context - generally the root category ID is used
     *
     * If it is desired to apply no category filter on returned products - redefine the class function:
     * addFilters(RequestInterface $request) : void
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getContextNavigationId(RequestInterface $request): array
    {
        return [$this->storeConfigurationHelper->getMagentoRootCategoryId()];
    }

    /**
     * Other fields can be: products_seo_url, products_image, discountedPrice, etc
     * If the products are loaded using the ApiEntityCollection - the generic Magento2 collection is used
     *
     * @return array
     */
    public function getReturnFields() : array
    {
        return ["id", "products_group_id", "title"];
    }


}
