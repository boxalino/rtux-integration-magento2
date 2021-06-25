<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context;

use Boxalino\RealTimeUserExperience\Service\Api\Util\ContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Util\RequestParametersTrait;
use Boxalino\RealTimeUserExperience\Helper\Configuration as StoreConfigurationHelper;
use Boxalino\RealTimeUserExperienceApi\Framework\Request\SearchContextAbstract;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\SearchContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Definition\SearchRequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\IntegrationContextTrait;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Boxalino Search Request handler
 *
 * Allows to set the nr of subphrases and products returned on each subphrase hit
 *
 * Rewrite this function in order to set/add/remove default API request filters:
 * protected function addFilters(RequestInterface $request) : void
 *
 * NOTICE: THIS ContextInterface ADDS THE DEFAULT FILTERS ON THE REQUEST (status, visibility, root category id)
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context
 */
class SearchContext extends SearchContextAbstract
    implements SearchContextInterface
{
    use ContextTrait;
    use RequestParametersTrait;
    use IntegrationContextTrait;

    public function __construct(
        RequestTransformerInterface $requestTransformer,
        ParameterFactoryInterface $parameterFactory,
        SearchRequestDefinitionInterface $requestDefinition,
        StoreConfigurationHelper $storeConfigurationHelper
    ) {
        parent::__construct($requestTransformer, $parameterFactory);
        $this->storeConfigurationHelper = $storeConfigurationHelper;
        /** prepare context with configurations */
        $this->setRequestDefinition($requestDefinition);
        $this->setWidget("search");
        $this->setSubPhrasesCount(5);
        $this->setSubPhrasesProductsCount(5);
    }

    /**
     * Product visibility on a navigation context
     * (per Magento2 rules or project business rules)
     *
     * @return array
     */
    public function getContextVisibility() : array
    {
        return [Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_SEARCH];
    }

    /**
     * For the navigation context - the viewed category ID
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
        return ["id", "products_group_id", "title", "discountedPrice"];
    }


}
