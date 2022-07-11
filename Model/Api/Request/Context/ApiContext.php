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
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class ApiContext
 * Use the appropriate ContextInterface (as parent and interface) based on your integration requirement
 *
 * NOTICE: MUST EXTEND ArgumentInterface IF IT IS USED AS AN ARGUMENT IN BLOCK XML DEFINITION
 * NOTICE: THIS ContextInterface DOES NOT ADD ANY DEFAULT FILTERS ON THE REQUEST
 * NOTICE: THIS ContextInterface WILL ADD FACETS AND SORTING/PAGINATION DETAILS TO API REQUEST (review other  ContextAbstract to avoid this)
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context
 */
class ApiContext extends ListingContextAbstract
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
     * Remove all filters
     *
     * @param RequestInterface $request
     */
    protected function addFilters(RequestInterface $request) : void
    {}

    /**
     * @return array
     */
    public function getContextVisibility() : array
    {
        return [];
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    public function getContextNavigationId(RequestInterface $request): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getReturnFields() : array
    {
        $configuredReturnFields = $this->getProperty("returnFields") ?? [];
        return array_merge(array_values($configuredReturnFields), ["id", "products_group_id"]);
    }


}
