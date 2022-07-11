<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context;

use Boxalino\RealTimeUserExperience\Api\ApiFilterablePropertiesProviderInterface;
use Boxalino\RealTimeUserExperience\Model\Api\Context\ListingContextFilterablePropertiesTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Util\ContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Util\RequestParametersTrait;
use Boxalino\RealTimeUserExperience\Helper\Configuration as StoreConfigurationHelper;
use Boxalino\RealTimeUserExperienceApi\Framework\Request\ListingContextAbstract;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ListingContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Definition\ListingRequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\IntegrationContextTrait;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Boxalino Dynamic Request handler
 *
 * Rewrite this function in order to set/add/remove default API request filters:
 * protected function addFilters(RequestInterface $request) : void
 *
 * NOTICE: THIS ContextInterface DOES NOT ADD ANY DEFAULT FILTERS ON THE REQUEST
 * NOTICE: MUST EXTEND ArgumentInterface IF IT IS USED AS AN ARGUMENT IN BLOCK XML DEFINITION
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context
 */
class DynamicContext extends ListingContextAbstract
    implements ListingContextInterface, ArgumentInterface
{
    use ContextTrait;
    use RequestParametersTrait;
    use IntegrationContextTrait;
    use ListingContextFilterablePropertiesTrait;

    public function __construct(
        RequestTransformerInterface $requestTransformer,
        ParameterFactoryInterface $parameterFactory,
        ListingRequestDefinitionInterface $requestDefinition,
        StoreConfigurationHelper $storeConfigurationHelper,
        ApiFilterablePropertiesProviderInterface $apiFilterablePropertiesList
    ) {
        parent::__construct($requestTransformer, $parameterFactory);
        $this->storeConfigurationHelper = $storeConfigurationHelper;
        $this->filterablePropertyProvider = $apiFilterablePropertiesList;

        /** prepare context with configurations */
        $this->setRequestDefinition($requestDefinition);
    }

    /**
     * For a dynamic context, it can be opted to have no default filters
     * Filters can be set directly in the main requested TPO
     *
     * If default options are desired, do not declare this function
     *
     * @param RequestInterface $request
     */
    protected function addFilters(RequestInterface $request): void
    {
    }

    /**
     * Other fields can be: link, image, discountedPrice, etc
     * If the products are loaded using the ApiEntityCollection - the generic Magento2 collection is used
     *
     * @return array
     */
    public function getReturnFields() : array
    {
        $configuredReturnFields = $this->getProperty("returnFields") ?? [];
        return array_merge(array_values($configuredReturnFields), ["id", "products_group_id"]);
    }

    /**
     * Product visibility on a listing context
     * Because the "addFilters" is left void - this filter is not set
     *
     * @return array
     */
    public function getContextVisibility() : array
    {
        return [Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG];
    }

    /**
     * For the dynamic context - the root category ID
     * Because the "addFilters" is left void - this filter is not set
     *
     * @param RequestInterface $request
     * @return array
     */
    public function getContextNavigationId(RequestInterface $request): array
    {
        return [$this->storeConfigurationHelper->getMagentoRootCategoryId()];
    }

}
