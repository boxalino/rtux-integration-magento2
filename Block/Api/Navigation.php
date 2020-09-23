<?php
namespace BoxalinoClientProject\BoxalinoRealTimeUserExperienceIntegration\Block\Api;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Api\ApiResponseBlockInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseRegistryInterface;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseViewRegistryInterface;
use BoxalinoClientProject\BoxalinoRealTimeUserExperienceIntegration\Model\Api\Request\Context\NavigationContext;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Magento\Catalog\Block\Category\View;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer\Resolver;

/**
 * Class Navigation
 * Replaces the category-displayed products
 * The displayed API response is part of the "blocks" parameter (left/top/right/bottom are not part of it)
 *
 * Does not update the default Magento2 catalog_category_view template
 *
 * @package BoxalinoClientProject\BoxalinoRealTimeUserExperienceIntegration\Block
 */
class Navigation extends View
    implements ApiRendererInterface
{

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

    /**
     * @var CurrentApiResponseRegistryInterface
     */
    protected $currentApiResponse;

    /**
     * @var CurrentApiResponseViewRegistryInterface
     */
    protected $currentApiResponseView;

    public function __construct(
        CurrentApiResponseRegistryInterface$currentApiResponse,
        CurrentApiResponseViewRegistryInterface $currentApiResponseView,
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
        $this->currentApiResponse = $currentApiResponse;
        $this->currentApiResponseView = $currentApiResponseView;
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
        $this->apiContext = $apiContext;
    }

    /**
     * Makes the request to Boxalino API
     *
     * @return Navigation|void
     */
    protected function _prepareLayout()
    {
        try{
            if($this->currentApiResponse->get())
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

            $this->_logger->warning("BoxalinoAPI Navigation Error: " . $exception->getMessage());
            $this->_logger->warning("BoxalinoAPI Navigation Error: " . $exception->getTraceAsString());
        }

        parent::_prepareLayout();
    }

    public function getBlocks() : \ArrayIterator
    {
        if($this->currentApiResponseView->get())
        {
            return $this->currentApiResponseView->get()->getBlocks();
        }

        return new \ArrayIterator();
    }

    /**
     * Default: call parent
     * Boxalino API: display the Boxalino API response block
     * Optional: this function is only needed if the default Magento2 template is used
     *
     * @return string
     */
    public function getProductListHtml()
    {
        if($this->currentApiResponseView->get() && $this->currentApiResponseView->get()->isFallback() || !$this->currentApiResponseView->get())
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

}
