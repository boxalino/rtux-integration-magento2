<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Block\Api\Product;

use Boxalino\RealTimeUserExperience\Api\ApiBlockAccessorInterface;
use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperience\Model\Response\Content\ApiEntityCollection;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseViewRegistryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\BxAttributeList;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ApiResponseViewInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context\ItemContext;

/**
 * Catalog product related items block
 * It extends the original Related block for the fallback strategy (if chosen to)
 *
 * Due to the preference update, the default M2 template is used (Magento_Catalog::product/list/items.phtml)
 * Update the template to support the API JS tracker mark-ups
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
    protected function getContextItemId()
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
            if($this->currentApiResponseView->get() instanceof ApiResponseViewInterface)
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
        if ($this->currentApiResponseView->get() instanceof ApiResponseViewInterface && $this->getContextItemId())
        {
            /** @var ApiBlockAccessorInterface $apiBlock */
            foreach ($this->currentApiResponseView->get()->getBlocks() as $apiBlock) {
                /** upsell, crosssell, related, other */
                if ($apiBlock->getType() === $this->getType()) {
                    /** @var ApiEntityCollection $collectionModel */
                    $collectionModel = $apiBlock->getModel();
                    $this->_itemCollection = $collectionModel->getCollection();
                }
            }

            return $this;
        }

        return parent::_prepareData();
    }

    /**
     * Access the Boxalino response attributes for API JS tracker
     *
     * @return \ArrayIterator
     */
    public function getBxAttributes(): \ArrayIterator
    {
        if ($this->currentApiResponseView->get() instanceof ApiResponseViewInterface && $this->getContextItemId())
        {
            /** @var ApiBlockAccessorInterface $apiBlock */
            foreach ($this->currentApiResponseView->get()->getBlocks() as $apiBlock) {
                /** upsell, crosssell, related, other */
                if ($apiBlock->getType() === $this->getType()) {
                    return $apiBlock->getBxAttributes();
                }
            }
        }

        return new BxAttributeList();
    }

}
