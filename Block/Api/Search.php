<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Block\Api;

use Boxalino\RealTimeUserExperience\Api\ApiBlockAccessorInterface;
use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Api\ApiResponseBlockInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Block\Catalog\Product\ListProduct;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseRegistryInterface;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseViewRegistryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ResponseDefinitionInterface;
use BoxalinoClientProject\BoxalinoIntegration\Api\ApiLayoutBlockNameInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context\SearchContext;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Magento\CatalogSearch\Helper\Data;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Search\Model\QueryFactory;

/**
 * Class Search
 * Replaces the search-displayed products/result
 *
 * The default Boxalino Narrative API search layout includes 3 scenarios:
 * 1. default search (matches for the search term)
 * 2. search sub-phrases (multiple groups of search results for parts of the search term)
 * 3. no results (product recommendations)
 *
 * Extends from the original block in order to ensure a fallback strategy
 * Does not replace the default template (Magento_CatalogSearch::result.phtml)
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Block
 */
class Search extends \Magento\CatalogSearch\Block\Result
    implements ApiRendererInterface
{

    use ApiBlockTrait;

    /**
     * @var ApiPageLoader
     */
    protected $apiLoader;

    /**
     * @var SearchContext
     */
    protected $apiContext;

    /**
     * @var RequestInterface
     */
    protected $requestWrapper;

    public function __construct(
        CurrentApiResponseRegistryInterface $currentApiResponse,
        CurrentApiResponseViewRegistryInterface $currentApiResponseView,
        ApiPageLoader $apiPageLoader,
        SearchContext $apiContext,
        RequestInterface $requestWrapper,
        Context $context,
        Resolver $layerResolver,
        Data $catalogSearchData,
        QueryFactory $queryFactory,
        array $data = []
    ){
        parent::__construct($context, $layerResolver, $catalogSearchData, $queryFactory, $data);
        $this->currentApiResponse = $currentApiResponse;
        $this->currentApiResponseView = $currentApiResponseView;
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
        $this->apiContext = $apiContext;
    }

    /**
     * Default: use default template in case of fallback
     * Boxalino API: use the generic template to render blocks and children
     *
     * @return string
     */
    public function getTemplate()
    {
        if($this->isApiFallback())
        {
            return parent::getTemplate();
        }

        return ApiResponseBlockInterface::BOXALINO_RTUX_API_BLOCK_TEMPLATE_DEFAULT;
    }

    /**
     * Makes the API requests (all parameters are defined in the api context)
     * Processes default action
     *
     * @return Search|void
     */
    protected function _prepareLayout()
    {
        try{
            if($this->currentApiResponse->get() instanceof ResponseDefinitionInterface)
            {
                return parent::_prepareLayout();
            }

            $this->apiLoader
                ->setRequest($this->requestWrapper->setRequest($this->_request))
                ->setApiContext($this->apiContext)
                ->load();

        } catch (\Throwable $exception)
        {
            $this->apiLoader->getApiResponsePage()->setFallback(true);

            $this->_logger->warning("BoxalinoAPI Search Error: " . $exception->getMessage());
            $this->_logger->warning("BoxalinoAPI Search Error: " . $exception->getTraceAsString());
        }

        parent::_prepareLayout();
    }

    /**
     * Default: Get search query text
     * Boxalino API: regardless of the search scenario (default/search query correction, sub-phrases or noresults),
     * there is a search title element in the configured narrative layout
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
        if($this->isApiFallback())
        {
            return parent::getSearchQueryText();
        }

        /** @var ApiBlockAccessorInterface $apiBlock */
        foreach($this->getBlocks() as $index => $apiBlock)
        {
            if(in_array(
                $apiBlock->getName(),
                [
                    ApiLayoutBlockNameInterface::RTUX_API_DEFAULT_SEARCH_TITLE_BLOCK,
                    ApiLayoutBlockNameInterface::RTUX_API_NORESULTS_SEARCH_TITLE_BLOCK
                ]))
            {
                $title = $apiBlock->getTitle();
                if($title)
                {
                    /** once the title is accessed, the title block can be removed from layout, otherwise it will duplicate on view */
                    $this->getBlocks()->offsetUnset($index);
                    return $title;
                }
            }
        }

        return __("Search results for: '%1'", $this->catalogSearchData->getEscapedQueryText());
    }

    /**
     * Default: sets view mode (list, grill) on toolbar
     * Boxalino API: It is not needed as list mode is a different layout block segment
     *
     * @return $this|Search
     */
    public function setListModes()
    {
        if($this->isApiFallback())
        {
            return parent::setListModes();
        }

        return $this;
    }

    /**
     * Default: sets available orders on the toolbar
     * Boxalino API: It is not needed as toolbar is a different layout block segment
     *
     * @return $this|Search
     */
    public function setListOrders()
    {
        if($this->currentApiResponse->get() instanceof ResponseDefinitionInterface)
        {
            return $this;
        }

        return parent::setListOrders();
    }

    public function getBlocks() : \ArrayIterator
    {
        if($this->currentApiResponse->get() instanceof ResponseDefinitionInterface)
        {
            return $this->currentApiResponseView->get()->getBlocks();
        }

        return new \ArrayIterator();
    }

    /**
     * Optional functions rewritten from parent block
     * (required for when the default Magento2 template is used)
     */

    /**
     * Default: return default Magento2 products
     * Boxalino API: display the Boxalino API response page elements
     * Optional: this function is only needed if the default Magento2 template is used
     *
     * @return string
     */
    public function getListBlock()
    {
        if($this->currentApiResponse->get() instanceof ResponseDefinitionInterface)
        {
            /** @var ApiResponseBlockInterface $apiBlock */
            $apiBlock = $this->getDefaultResponseBlock();
            $apiBlock->setRtuxGroupBy($this->getRtuxGroupBy())
                ->setRtuxVariantUuid($this->getRtuxVariantUuid())
                ->setApiResponsePage($this->apiLoader->getApiResponsePage());

            return $apiBlock;
        }

        return parent::getListBlock();
    }

    /**
     * Default: Retrieve Search result list HTML output
     * Boxalino API: return the API response page HTML
     * Optional: this function is only needed if the default Magento2 template is used
     *
     * @return string
     */
    public function getProductListHtml()
    {
        if($this->currentApiResponse->get() instanceof ResponseDefinitionInterface)
        {
            return $this->getListBlock()->toHtml();
        }

        return parent::getProductListHtml();
    }

    /**
     * Default: Retrieve loaded category collection
     * Boxalino API: It is expected to find the product collection in the product-list Layout Block with the name as defined in integration
     * Optional: this function is only needed if the default Magento2 template is used
     *
     * @return Collection
     */
    protected function _getProductCollection()
    {
        if($this->currentApiResponse->get() instanceof ResponseDefinitionInterface)
        {
            if (null === $this->productCollection) {
                /** @var ApiBlockAccessorInterface $apiBlock */
                foreach($this->getBlocks() as $apiBlock)
                {
                    /** @var ListProduct $apiBlock (type for guidelines sample) */
                    if($apiBlock->getName() === ApiLayoutBlockNameInterface::RTUX_API_SEARCH_PRODUCT_LIST_BLOCK)
                    {
                        $this->productCollection = $apiBlock->getLoadedProductCollection();
                    }
                }
            }

            return $this->productCollection;
        }

        return parent::_getProductCollection();
    }

    /**
     * Default: Retrieve search result count
     * Boxalino API: it is possible to access the products count (except for subphrases result and noresults scenario)
     * Optional: this function is only needed if the default Magento2 template is used
     *
     * @return string
     */
    public function getResultCount()
    {
        if($this->currentApiResponse->get() instanceof ResponseDefinitionInterface)
        {
            if (!$this->getData('result_count')) {
                /** @var int $size */
                $size = $this->apiLoader->getApiResponsePage()->getTotalHitCount();
                $this->setResultCount($size);
            }

            return $this->getData('result_count');
        }

        return parent::getResultCount();
    }

    /**
     * Default: Retrieve Note messages
     * Boxalino API: can access search messages and other configured Response elements
     * Optional: this function is only needed if the default Magento2 template is used
     *
     * @return array
     */
    public function getNoteMessages()
    {
        if(!$this->currentApiResponseView->get())
        {
            return parent::getNoteMessages();
        }

        return [];
    }

}
