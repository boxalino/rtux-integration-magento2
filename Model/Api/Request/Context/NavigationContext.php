<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoRealTimeUserExperienceIntegration\Model\Api\Request\Context;

use Boxalino\RealTimeUserExperience\Service\Api\Util\ContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Util\RequestParametersTrait;
use Boxalino\RealTimeUserExperience\Helper\Configuration as StoreConfigurationHelper;
use Boxalino\RealTimeUserExperienceApi\Framework\Request\ListingContextAbstract;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ListingContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Definition\ListingRequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use BoxalinoClientProject\BoxalinoRealTimeUserExperienceIntegration\Model\Api\Request\IntegrationContextTrait;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Boxalino Navigation Request handler
 *
 * The list of filters applied on the context is part of the class function:
 * protected function addFilters(RequestInterface $request) : void
 *
 * @package BoxalinoClientProject\BoxalinoRealTimeUserExperienceIntegration\Model\Api\Request\Context
 */
class NavigationContext extends ListingContextAbstract
    implements ListingContextInterface
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
        $this->setWidget("navigation");
    }

    /**
     * Product visibility on a navigation context
     * (per Magento2 rules or project business rules)
     *
     * @return array
     */
    public function getContextVisibility() : array
    {
        return [Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG];
    }

    /**
     * For the navigation context - the viewed category ID
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
     * @return array
     */
    public function getReturnFields() : array
    {
        return ["id", "products_group_id", "title"];
    }

}
