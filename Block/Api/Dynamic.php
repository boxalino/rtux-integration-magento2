<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Block\Api;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context\DynamicContext;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Dynamic
 * Adds block/context on a dynamic router page
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Block
 */
class Dynamic extends \Magento\Framework\View\Element\Template
    implements ApiRendererInterface
{

    use ApiBlockTrait;

    /**
     * @var ApiPageLoader
     */
    protected $apiLoader;

    /**
     * @var DynamicContext
     */
    protected $apiContext;

    /**
     * @var RequestInterface
     */
    protected $requestWrapper;

    public function __construct(
        ApiPageLoader $apiPageLoader,
        DynamicContext $apiContext,
        RequestInterface $requestWrapper,
        Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
        $this->apiContext = $apiContext;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        try{
            /** the configurations for the context can be defined via XML or directly in the $apiContext model */
            $this->apiContext
                ->setWidget($this->getData("widget"))
                ->setHitCount($this->getData("hitCount"))
                ->setGroupBy($this->getData("groupBy"))
                ->set("returnFields", array_values($this->getData("returnFields")));

            $this->apiLoader
                ->setRequest($this->requestWrapper->setRequest($this->_request))
                ->setApiContext($this->apiContext)
                ->load();
        } catch (\Throwable $exception)
        {
            $this->apiLoader->getApiResponsePage()->setFallback(true);
            $this->_logger->warning("BoxalinoAPI Dynamic Error: " . $exception->getMessage());
            $this->_logger->warning("BoxalinoAPI Dynamic Error: " . $exception->getTraceAsString());
        }
    }

    public function getBlocks() : \ArrayIterator
    {
        return $this->apiLoader->getApiResponsePage()->getBlocks();
    }

}
