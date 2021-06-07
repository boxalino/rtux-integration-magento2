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

/**
 * Class BlogContext
 * Holds the request properties: widget, hitcount, returnfields, groupby, offset, etc
 * The list of filters applied on the context is part of the class function (which can be rewritten)
 * protected function addFilters(RequestInterface $request) : void
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context
 */
class BlogContext extends ContextAbstract
    implements ContextInterface
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
     * Remove all filters (or add the blog-specific filters per your project specifications)
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
        return ["id", "title", "products_group_id"];
    }


}
