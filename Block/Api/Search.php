<?php
namespace Boxalino\RealTimeUserExperienceIntegration\Block\Api;

use Boxalino\RealTimeUserExperience\Api\ApiBlockAccessorInterface;
use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Api\ApiResponseBlockInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Block\Catalog\Product\ListProduct;
use Boxalino\RealTimeUserExperience\Model\ApiLoaderTrait;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperienceIntegration\Api\ApiLayoutBlockNameInterface;
use Boxalino\RealTimeUserExperienceIntegration\Model\Api\Request\Context\SearchContext;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Magento\CatalogSearch\Helper\Data;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Search\Model\QueryFactory;

/**
 * Class Search
 * Replaces the search-displayed products/result
 * Extends from the original block in order to ensure a fallback strategy
 * Does not replace the default template
 *
 * @package Boxalino\RealTimeUserExperienceIntegration\Block
 */
class Search extends \Magento\CatalogSearch\Block\Result
    implements ApiRendererInterface
{

    use ApiLoaderTrait;
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
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
        $this->apiContext = $apiContext;
    }

    /**
     * Default: use default template in case of fallback
     * Boxalino API: custom template
     *
     * @return string
     */
    public function getTemplate()
    {
        if($this->apiLoader->getApiResponsePage()->isFallback())
        {
            return parent::getTemplate();
        }

        return ApiResponseBlockInterface::BOXALINO_RTUX_API_BLOCK_TEMPLATE_DEFAULT;
    }

    /**
     * Makes the API requests
     * Processes default action
     *
     * @return Search|void
     */
    protected function _prepareLayout()
    {
        try{
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
     * Boxalino API: regardless of the search scenario (default, search query correction, sub-phrases or noresults),
     * there is a search title element in the configured narrative layout
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
        if($this->apiLoader->getApiResponsePage()->isFallback())
        {
            return parent::getSearchQueryText();
        }

        /** @var ApiBlockAccessorInterface $apiBlock */
        foreach($this->getBlocks() as $index=>$apiBlock)
        {
            if(in_array(
                $apiBlock->getName(),
                [
                    ApiLayoutBlockNameInterface::RTUX_API_DEFAULT_SEARCH_TITLE_BLOCK,
                    ApiLayoutBlockNameInterface::RTUX_API_NORESULTS_SEARCH_TITLE_BLOCK
                ]))
            {
                $title = $apiBlock->getTitle()[0];
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
        if($this->apiLoader->getApiResponsePage()->isFallback())
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
        if($this->apiLoader->getApiResponsePage()->isFallback())
        {
            return parent::setListOrders();
        }

        return $this;
    }

    public function getBlocks() : \ArrayIterator
    {
        return $this->apiLoader->getApiResponsePage()->getBlocks();
    }

    /**
     * Optional functions rewritten from parent block
     * (to be used if the default Magento2 template is used)
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
        if($this->apiLoader->getApiResponsePage()->isFallback())
        {
            return parent::getListBlock();
        }

        /** @var ApiResponseBlockInterface $apiBlock */
        $apiBlock = $this->getDefaultResponseBlock();
        $apiBlock->setRtuxGroupBy($this->getRtuxGroupBy())
            ->setRtuxVariantUuid($this->getRtuxVariantUuid())
            ->setApiResponsePage($this->apiLoader->getApiResponsePage());

        return $apiBlock;
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
        if($this->apiLoader->getApiResponsePage()->isFallback())
        {
            return parent::getProductListHtml();
        }

        return $this->getListBlock()->toHtml();
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
        if($this->apiLoader->getApiResponsePage()->isFallback())
        {
            return parent::_getProductCollection();
        }

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

    /**
     * Default: Retrieve search result count
     * Boxalino API: it is possible to access the products count (except for subphrases result and noresults scenario)
     * Optional: this function is only needed if the default Magento2 template is used
     *
     * @return string
     */
    public function getResultCount()
    {
        if($this->apiLoader->getApiResponsePage()->isFallback())
        {
            return parent::getResultCount();
        }

        if (!$this->getData('result_count')) {
            /** @var int $size */
            $size = $this->apiLoader->getApiResponsePage()->getTotalHitCount();
            $this->setResultCount($size);
        }

        return $this->getData('result_count');
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
        if($this->apiLoader->getApiResponsePage()->isFallback())
        {
            return parent::getNoteMessages();
        }

        return [];
    }

}
