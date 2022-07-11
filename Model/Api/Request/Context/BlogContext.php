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
 * Class BlogContext
 * Holds the request properties: widget, hitcount, returnfields, groupby, offset, etc
 *
 * NOTICE: Rewrite this function in order to add DEFAULT API filters for your blog content
 * protected function addFilters(RequestInterface $request) : void
 *
 * NOTICE: MUST EXTEND ArgumentInterface IF IT IS USED AS AN ARGUMENT IN BLOCK XML DEFINITION
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context
 */
class BlogContext extends ListingContextAbstract
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
     * Add the blog-specific filters per your project specifications
     * OR
     * Define the content-specific default filters in IA widget
     *
     * @param RequestInterface $request
     */
    protected function addFilters(RequestInterface $request) : void
    {

    }

    /**
     * Generic visibility
     * @return array
     */
    public function getContextVisibility() : array
    {
        return [];
    }

    /**
     * Set the category filter (if desired for blog reusability across e-shop)
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

        return [];
    }

    /**
     * Add the blog content fields available in your project data
     *
     * @return array
     */
    public function getReturnFields() : array
    {
        $configuredReturnFields = $this->getProperty("returnFields") ?? [];
        return array_merge(array_values($configuredReturnFields), ["id"]);
    }


}
