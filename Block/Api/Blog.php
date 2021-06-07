<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Block\Api;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context\BlogContext;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Blog
 * Adds a blog recommendation block/context on the page
 * (as seen in vendor/boxalino/rtux-integration-magento2/view/frontend/layout/customer_account_index.xml)
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Block
 */
class Blog extends \Magento\Framework\View\Element\Template
    implements ApiRendererInterface
{

    use ApiBlockTrait;

    /**
     * @var ApiPageLoader
     */
    protected $apiLoader;

    /**
     * @var BlogContext
     */
    protected $apiContext;

    /**
     * @var RequestInterface
     */
    protected $requestWrapper;

    public function __construct(
        ApiPageLoader $apiPageLoader,
        BlogContext $apiContext,
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
                ->setGroupBy("id");

            $this->apiLoader
                ->setRequest($this->requestWrapper->setRequest($this->_request))
                ->setApiContext($this->apiContext)
                ->load();
        } catch (\Throwable $exception)
        {
            $this->apiLoader->getApiResponsePage()->setFallback(true);
            $this->_logger->warning("BoxalinoAPI Blog Error: " . $exception->getMessage());
        }
    }

    public function getBlocks() : \ArrayIterator
    {
        return $this->apiLoader->getApiResponsePage()->getBlocks();
    }


}
