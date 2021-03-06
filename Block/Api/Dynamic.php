<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Block\Api;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Api\ApiResponseBlockInterface;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseRegistryInterface;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseViewRegistryInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockLoaderTrait;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ResponseDefinitionInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context\DynamicContext;
use Magento\Framework\View\Element\Template;

/**
 * Class Dynamic
 *
 * AS A RECOMMENDATION, There should be a single API request on the page
 * USE THE CurrentApiResponseRegistryInterface / CurrentApiResponseViewRegistryInterface FOR RESPONSE ACCESS THROUGHOUT BLOCKS
 * (similar use-case: search, navigation, PDP reco)
 *
 * NOTICE: USE THE APPROPRIATE ContextInterface FOR THE BLOCK (added as ARGUMENT in layout XML)
 *
 * If you expect to display content that requires DIFFERENT FILTERS (ex: products and blogs, etc)
 * Make sure to:
 * a) either use Request Rules in IA (for ex: remove all product-related filters from blogs)
 * b) either set Filter rules on the widget (for ex: keep the blog filters in the blog/read widget)
 * c) either use a ContextInterface without any filters set (for ex: Model\Api\Request\Context\BlogContext sample) and declare the filters in the widgets
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
            $this->_logger->warning("BoxalinoAPI Dynamic {$this->getData("widget")} Error: " . $exception->getMessage());
            $this->_logger->debug("BoxalinoAPI Dynamic {$this->getData("widget")} Error: " . $exception->getTraceAsString());

            $this->apiLoader->getApiResponsePage()->setFallback(true);
        }
    }

    public function getBlocks() : \ArrayIterator
    {
        return $this->apiLoader->getApiResponsePage()->getBlocks();
    }

    /**
     * Boxalino API: use the generic template to render blocks and children
     *
     * @return string
     */
    public function getTemplate()
    {
        return ApiResponseBlockInterface::BOXALINO_RTUX_API_BLOCK_TEMPLATE_DEFAULT;
    }


}
