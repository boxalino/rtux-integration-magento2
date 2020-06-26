<?php
namespace Boxalino\RealTimeUserExperienceIntegration\Block\Api;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Api\ApiResponseBlockInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Model\ApiLoaderTrait;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperienceIntegration\Model\Api\Request\Context\NavigationContext;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\Category\View;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer\Resolver;
/**
 * Class Navigation
 * Replaces the category-displayed products
 *
 * @package Boxalino\RealTimeUserExperience\Block
 */
class Navigation extends View
    implements ApiRendererInterface
{

    use ApiLoaderTrait;
    use ApiBlockTrait;

    /**
     * @var ApiPageLoader
     */
    protected $apiLoader;

    /**
     * @var NavigationContext
     */
    protected $apiContext;

    /**
     * @var RequestInterface
     */
    protected $requestWrapper;

    public function __construct(
        ApiPageLoader $apiPageLoader,
        NavigationContext $apiContext,
        RequestInterface $requestWrapper,
        Context $context,
        Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = []
    ){
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
        $this->apiContext = $apiContext;
    }

    protected function _prepareLayout()
    {
        try{
            $this->apiLoader
                ->setRequest($this->requestWrapper->setRequest($this->_request))
                ->setApiContext($this->apiContext->setWidget("navigation"))
                ->load();
        } catch (\Throwable $exception)
        {
            $this->apiLoader->getApiResponsePage()->setFallback(true);

            $this->_logger->warning("BoxalinoAPI Navigation Error: " . $exception->getMessage());
            $this->_logger->warning("BoxalinoAPI Navigation Error: " . $exception->getTraceAsString());
        }

        parent::_prepareLayout();
    }

    /**
     * If it`s fallback - return default Magento2 products
     * If it`s not fallback - display the Boxalino API response
     *
     * @return string
     */
    public function getProductListHtml()
    {
        if($this->apiLoader->getApiResponsePage()->isFallback())
        {
            return $this->getChildHtml('product_list');
        }

        /** @var ApiResponseBlockInterface $apiBlock */
        $apiBlock = $this->getDefaultResponseBlock();
        $apiBlock->setRtuxGroupBy($this->getRtuxGroupBy())
            ->setRtuxVariantUuid($this->getRtuxVariantUuid())
            ->setApiResponsePage($this->apiLoader->getApiResponsePage());

        return $apiBlock->toHtml();
    }

    public function getBlocks() : \ArrayIterator
    {
        return $this->apiLoader->getApiResponsePage()->getBlocks();
    }

    public function getRtuxVariantUuid() : string
    {
        return $this->apiLoader->getApiResponsePage()->getVariantUuid();
    }

    public function getRtuxGroupBy() : string
    {
        return $this->apiLoader->getApiResponsePage()->getGroupBy();
    }

}
