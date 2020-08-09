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

/**
 * Catalog product related items block
 * It extends the original Related block for the fallback strategy (if chosen to)
 *
 * Using this block as a preference for the default Magento2 block does not require any template update
 * (the generic Magento2 Magento_Catalog::product/list/items.phtml is to be used)
 *
 * The product ID (context item ID) it is required due to the nature of the scenario
 *
 * @api
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Related extends \Magento\Catalog\Block\Product\ProductList\Related
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

    public function __construct(
        CurrentApiResponseViewRegistryInterface $currentApiResponseView,
        ApiPageLoader $apiPageLoader,
        ItemContext $apiContext,
        RequestInterface $requestWrapper,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $checkoutCart, $catalogProductVisibility, $checkoutSession, $moduleManager, $data);
        $this->currentApiResponseView = $currentApiResponseView;
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
        $this->apiContext = $apiContext;
    }

    /**
     * Property either set as an argument on block, a configuration field or statically defined
     * Represents the PDP widget assigned to the narrative
     *
     * @required
     * @return string
     */
    public function getWidget() : string
    {
        return "related";
    }

    /**
     * Property either set as an argument on block, a configuration field or statically defined
     * Represents the size of the returned collection
     * @required
     * @return string
     */
    public function getHitCount() : string
    {
        return 8;
    }

    /**
     * It can either be used the (deprecated) registry to access the product ID
     * OR the request parameter
     * (it is expected for the block to be used for the catalog_product_view layout)
     *
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
     * Makes the request to Boxalino narrative API
     *
     * Optional: the existing related product IDs can be set as context items for the request
     * if additional logic (optimization) is required based on the business logic
     *
     * @return Related|void
     */
    protected function _prepareLayout()
    {
        try{
            if($this->currentApiResponseView->get())
            {
                return parent::_prepareLayout();
            }

            if($this->getContextItemId())
            {
                $this->apiContext->setProductId($this->getContextItemId())
                    ->setWidget($this->getWidget())
                    ->setHitCount($this->getHitCount());

                $this->apiLoader
                    ->setRequest($this->requestWrapper->setRequest($this->_request))
                    ->setApiContext($this->apiContext)
                    ->load();
            }
        } catch (\Throwable $exception)
        {
            $this->apiLoader->getApiResponsePage()->setFallback(true);

            $this->_logger->warning("BoxalinoAPI Related PDP Error: " . $exception->getMessage());
            $this->_logger->warning("BoxalinoAPI Related PDP Error: " . $exception->getTraceAsString());
        }

        parent::_prepareLayout();
    }

    /**
     * If there was an issue during the request - load default Magento2 related products
     * Loads the product collection from the API response block matching the type of the recommendation block
     *
     * @return $this
     */
    protected function _prepareData()
    {
        if(!$this->currentApiResponseView->get() || !$this->getContextItemId())
        {
            return parent::_prepareData();
        }

        /** @var ApiBlockAccessorInterface $apiBlock */
        foreach($this->currentApiResponseView->get()->getBlocks() as $apiBlock)
        {
            /** upsell, crosssell, related, other */
            if($apiBlock->getType() === $this->getType())
            {
                /** @var ApiEntityCollection $collectionModel */
                $collectionModel = $apiBlock->getModel();
                $this->_itemCollection = $collectionModel->getCollection();
            }
        }

        return $this;
    }

}
