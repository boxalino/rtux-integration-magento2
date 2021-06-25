<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context;

use Boxalino\RealTimeUserExperience\Service\Api\Util\ContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Util\RequestParametersTrait;
use Boxalino\RealTimeUserExperience\Helper\Configuration as StoreConfigurationHelper;
use Boxalino\RealTimeUserExperienceApi\Framework\Request\ContextAbstract;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Definition\ListingRequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\IntegrationContextTrait;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class CmsContext
 * Holds the request properties: widget, hitcount, returnfields, groupby, offset, etc
 *
 * Rewrite this function in order to set/add/remove default API request filters:
 * protected function addFilters(RequestInterface $request) : void
 *
 * NOTICE: THIS ContextInterface WILL ADD THE GENERIC PRODUCT FILTERS (status, visibility, category_id)
 * NOTICE: MUST EXTEND ArgumentInterface IF IT IS USED AS AN ARGUMENT IN BLOCK XML DEFINITION
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context
 */
class CmsContext extends ContextAbstract
    implements ContextInterface, ArgumentInterface
{
    use ContextTrait;
    use RequestParametersTrait;
    use IntegrationContextTrait;

    public function __construct(
        RequestTransformerInterface $requestTransformer,
        ParameterFactoryInterface $parameterFactory,
        ListingRequestDefinitionInterface $requestDefinition,
        StoreConfigurationHelper $storeConfigurationHelper
    ) {
        parent::__construct($requestTransformer, $parameterFactory);
        $this->storeConfigurationHelper = $storeConfigurationHelper;
        /** prepare context with configurations */
        $this->setRequestDefinition($requestDefinition);
    }

    /**
     * Product visibility on a CMS context
     * IF MIXED CONTENT IS EXPECTED, ADJUST THE INTEGRATION STRATEGY
     *
     * @return array
     */
    public function getContextVisibility() : array
    {
        return [Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_SEARCH, Visibility::VISIBILITY_IN_CATALOG];
    }

    /**
     * For the CMS context - generally the root category ID is the navigation filter (ex: homepage)
     * It can also be the category itself it is the widget/block set on (ex: top products on XX category)
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getContextNavigationId(RequestInterface $request): array
    {
        $categoryId = (int)$request->getParam('id', false);
        if($categoryId)
        {
            return [$categoryId];
        }

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
        $configuredReturnFields = $this->getProperty("returnFields") ?? [];
        return array_merge(array_values($configuredReturnFields), ["id", "products_group_id", "title"]);
    }


}
