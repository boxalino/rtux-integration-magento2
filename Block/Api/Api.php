<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Block\Api;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Api\ApiResponseBlockInterface;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseRegistryInterface;
use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseViewRegistryInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockLoaderTrait;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ResponseDefinitionInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Api
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
class Api extends \Magento\Framework\View\Element\Template
    implements ApiRendererInterface
{

    use ApiBlockLoaderTrait;

    public function __construct(
        CurrentApiResponseRegistryInterface $currentApiResponse,
        CurrentApiResponseViewRegistryInterface $currentApiResponseView,
        ApiPageLoader $apiPageLoader,
        RequestInterface $requestWrapper,
        Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->currentApiResponse = $currentApiResponse;
        $this->currentApiResponseView = $currentApiResponseView;
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
    }

    protected function _prepareLayout()
    {
        try{
            if($this->currentApiResponse->getByWidget($this->getData("widget")) instanceof ResponseDefinitionInterface)
            {
                return parent::_prepareLayout();
            }

            if($this->isApiFallback())
            {
                return $this;
            }

            /** If there is no apiContext - it is not the main block that makes the page request */
            if(!$this->getData("apiContext"))
            {
                return parent::_prepareLayout();
            }

            $this->_prepareApiContext();
            $this->_prepareApiLoader();

            $this->getApiLoader()->load();
            $this->addResponseToRegistry();

        } catch (\Throwable $exception)
        {
            $this->_logger->warning("BoxalinoAPI API {$this->getData("widget")} Block Error: " . $exception->getMessage());
            $this->_logger->debug("BoxalinoAPI API {$this->getData("widget")} Block Error: " . $exception->getTraceAsString());

            $this->getApiLoader()->getApiResponsePage()->setFallback(true);
        }

        return parent::_prepareLayout();
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
