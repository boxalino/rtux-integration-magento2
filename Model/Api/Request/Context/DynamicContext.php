<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context;

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

/**
 * Boxalino Dynamic Request handler
 *
 * The list of filters applied on the context is part of the class function:
 * protected function addFilters(RequestInterface $request) : void
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context
 */
class DynamicContext extends ListingContextAbstract
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
     * Other fields can be: products_seo_url, products_image, discountedPrice, etc
     * If the products are loaded using the ApiEntityCollection - the generic Magento2 collection is used
     *
     * @return array
     */
    public function getReturnFields() : array
    {
        $returnFields = $this->getProperty("returnFields");
        if(is_array($returnFields))
        {
            return $returnFields;
        }

        return [];
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
