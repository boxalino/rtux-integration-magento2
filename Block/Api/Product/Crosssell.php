<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Block\Api\Product;

use Boxalino\RealTimeUserExperience\Api\ApiBlockAccessorInterface;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseViewRegistryInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperience\Model\Response\Content\ApiEntityCollection;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ApiResponseViewInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context\ItemContext;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Checkout\Block\Cart\Crosssell as MagentoCrosssell;
use Magento\CatalogInventory\Helper\Stock as StockHelper;

/**
 * Class Crosssell
 * Used for the cart recommendations
 * Sets the last added product IDs as the main ID; in lack of this - it will use the product ID with the highest price
 * The list of products in cart must be set as sub-products
 *
 * A much more simple version can be used (ex: a virtual type -- scenario #2C)
 *
 * Keep in mind to add the required JS API tracker HTML-mark-ups to Magento_Catalog::product/list/items.phtml
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Block\Api\Product
 */
class Crosssell extends MagentoCrosssell
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
     * @var int
     */
    protected $mainItemId = 0;

    public function __construct(
        CurrentApiResponseViewRegistryInterface $currentApiResponseView,
        ApiPageLoader $apiPageLoader,
        ItemContext $apiContext,
        RequestInterface $requestWrapper,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\LinkFactory $productLinkFactory,
        \Magento\Quote\Model\Quote\Item\RelatedProducts $itemRelationsList,
        StockHelper $stockHelper,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $productVisibility, $productLinkFactory, $itemRelationsList, $stockHelper, $data);
        $this->currentApiResponseView = $currentApiResponseView;
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
        $this->apiContext = $apiContext;
    }

    /**
     * Property either set as an argument on block, a configuration field or statically defined
     * Represents the basket widget assigned to the narrative
     *
     * @required
     * @return string
     */
    public function getWidget() : string
    {
        return "basket";
    }

    /**
     * Property either set as an argument on block, a configuration field or statically defined
     * Represents the size of the returned collection
     *
     * @required
     * @return string
     */
    public function getHitCount() : string
    {
        return $this->_maxItemCount;
    }

    /**
     * Makes the Boxalino API request
     *
     * @return Crosssell|void
     */
    protected function _prepareLayout()
    {
        try{
            if($this->currentApiResponseView->get() instanceof ApiResponseViewInterface)
            {
                return parent::_prepareLayout();
            }

            $productId = $this->getMainItemId();
            if($productId)
            {
                $this->apiContext->setProductId($this->getMainItemId())
                    ->setSubProductIds($this->getApiRequestSubproductIds())
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

            $this->_logger->warning("BoxalinoAPI PDP Error on {$this->getType()}: " . $exception->getMessage());
            $this->_logger->warning("BoxalinoAPI PDP Error on {$this->getType()}: " . $exception->getTraceAsString());
        }

        parent::_prepareLayout();
    }

    /**
     * @return array|\ArrayIterator|Collection|mixed|null
     */
    public function getItems()
    {
        if(!$this->currentApiResponseView->get() || !$this->getMainItemId())
        {
            return parent::getItems();
        }

        $items = $this->getData('items');
        if ($items === null)
        {
            /** @var ApiBlockAccessorInterface $apiBlock */
            foreach($this->currentApiResponseView->get()->getBlocks() as $apiBlock)
            {
                /** upsell, crosssell, related, other */
                if($apiBlock->getType() === $this->getType())
                {
                    /** @var ApiEntityCollection $collectionModel */
                    $collectionModel = $apiBlock->getModel();
                    $items = $collectionModel->getCollection()->getItems();
                    break;
                }
            }

            $this->setData('items', $items);
        }

        return $items;
    }

    /**
     * The main product is the last added product to cart
     * Or the product with the highest price
     *
     * @required
     * @return string|null
     */
    protected function getMainItemId()
    {
        if($this->mainItemId > 0)
        {
            return $this->mainItemId;
        }
        $this->mainItemId = (int) $this->_getLastAddedProductId();
        if($this->mainItemId)
        {
            return (string) $this->mainItemId;
        }

        $price = 0;
        foreach ($this->getQuote()->getAllItems() as $item)
        {
            $productPrice = $item->getProduct()->getPrice();
            if($productPrice > $price)
            {
                $price = $productPrice;
                $this->mainItemId = $item->getProduct()->getId();
            }
        }

        if($this->mainItemId > 0)
        {
            return $this->mainItemId;
        }

        return null;
    }

    /**
     * The rest of the cart product IDs are set as subproducts in the request
     *
     * @return array
     */
    protected function getApiRequestSubproductIds() : array
    {
        return array_diff($this->_getCartProductIds(), [$this->getMainItemId()]);
    }

}
