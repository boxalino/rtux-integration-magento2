<?php
namespace Boxalino\RealTimeUserExperienceIntegration\Block\Api\Product;

use Boxalino\RealTimeUserExperience\Api\ApiBlockAccessorInterface;
use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperience\Model\Response\Content\ApiEntityCollection;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseViewRegistryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceIntegration\Model\Api\Request\Context\ItemContext;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Catalog product view items block
 */
class View extends \Magento\Framework\View\Element\Template
    implements ApiRendererInterface
{

    use ApiBlockTrait;

    /**
     * @var ApiPageLoader
     */
    protected $apiLoader;

    /**
     * @var ItemContext
     */
    protected $apiContext;

    /**
     * @var RequestInterface
     */
    protected $requestWrapper;

    /**
     * @var CurrentApiResponseViewRegistryInterface
     */
    protected $currentApiResponseView;

    /**
     * @var Collection
     */
    protected $itemsCollection;

    public function __construct(
        CurrentApiResponseViewRegistryInterface $currentApiResponseView,
        ApiPageLoader $apiPageLoader,
        ItemContext $apiContext,
        RequestInterface $requestWrapper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currentApiResponseView = $currentApiResponseView;
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
        $this->apiContext = $apiContext;
    }

    /**
     * The base widget recommendation as configured in the Boxalino Intelligence admin as the master request
     * Property either set as an argument on block, a configuration field or hardcoded
     * @required
     * @return string
     */
    public function getWidget() : string
    {
        return "pdp-widget";
    }

    /**
     * Property either set as an argument on block, a configuration field or hardcoded
     * @required
     * @return string
     */
    public function getHitCount() : string
    {
        return 7;
    }

    /**
     * It can either be used the (deprecated) registry to access the product ID
     * OR the request parameter
     * (it is expected for the block to be used for the catalog_product_view layout)
     * @required
     * @return string|null
     */
    protected function getContextItemId() : ?string
    {
        $id = (int) $this->_request->getParam('id', false);
        if($id)
        {
            return (string) $id;
        }

        return null;
    }

    /**
     * Makes the Boxalino API request
     *
     * @return View|void
     */
    protected function _prepareLayout()
    {
        try{
            if($this->currentApiResponseView->get())
            {
                return parent::_prepareLayout();
            }

            $this->apiContext->setProductId($this->getContextItemId())
                ->setWidget($this->getWidget())
                ->setHitCount($this->getHitCount());
            $this->apiLoader
                ->setRequest($this->requestWrapper->setRequest($this->_request))
                ->setApiContext($this->apiContext)
                ->load();
        } catch (\Throwable $exception)
        {
            $this->apiLoader->getApiResponsePage()->setFallback(true);

            $this->_logger->warning("BoxalinoAPI PDP Error: " . $exception->getMessage());
            $this->_logger->warning("BoxalinoAPI PDP Error: " . $exception->getTraceAsString());
        }

        parent::_prepareLayout();
    }

    /**
     * @return Collection
     */
    public function getItems() : ?Collection
    {
        if(!$this->currentApiResponseView->get())
        {
            return null;
        }

        if ($this->itemsCollection === null)
        {
            /** @var ApiBlockAccessorInterface $apiBlock */
            foreach ($this->currentApiResponseView->get()->getBlocks() as $apiBlock)
            {
                /** upsell, crosssell, related, other */
                if ($apiBlock->getType() === $this->getType())
                {
                    /** @var ApiEntityCollection $collectionModel */
                    $collectionModel = $apiBlock->getModel();
                    $this->itemsCollection = $collectionModel->getCollection();
                }
            }
        }

        return $this->itemsCollection;
    }

}
