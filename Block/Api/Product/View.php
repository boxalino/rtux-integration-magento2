<?php
namespace Boxalino\RealTimeUserExperienceIntegration\Block\Api\Product;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceIntegration\Model\Api\Request\Context\ItemContext;
use Magento\Framework\View\Element\Template;

/**
 * Catalog product related items block
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

    public function __construct(
        ApiPageLoader $apiPageLoader,
        ItemContext $apiContext,
        RequestInterface $requestWrapper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
        return $this->getData("widget") ?? "related";
    }

    /**
     * Property either set as an argument on block, a configuration field or hardcoded
     * @required
     * @return string
     */
    public function getHitCount() : string
    {
        return $this->getData("hitCount") ?? 7;
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

}
