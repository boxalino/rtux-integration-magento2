<?php
namespace Boxalino\RealTimeUserExperienceIntegration\Block\Api;

use Boxalino\RealTimeUserExperience\Api\ApiRendererInterface;
use Boxalino\RealTimeUserExperience\Block\ApiBlockTrait;
use Boxalino\RealTimeUserExperience\Model\ApiLoaderTrait;
use Boxalino\RealTimeUserExperienceIntegration\Model\Api\Request\Context\CmsContext;
use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Cms
 * Adds a CMS block/context on the page
 * (as seen in vendor/boxalino/rtux-integration-magento2/view/frontend/layout/cms_page_view_selectable_home_RTUX.xml)
 *
 * @package Boxalino\RealTimeUserExperience\Block
 */
class Cms extends \Magento\Framework\View\Element\Template
    implements ApiRendererInterface
{

    use ApiLoaderTrait;
    use ApiBlockTrait;

    /**
     * @var ApiPageLoader
     */
    protected $apiLoader;

    /**
     * @var CmsContext
     */
    protected $apiContext;

    /**
     * @var RequestInterface
     */
    protected $requestWrapper;

    public function __construct(
        ApiPageLoader $apiPageLoader,
        CmsContext $apiContext,
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
            $this->apiContext
                ->setWidget($this->getData("widget"))
                ->setHitCount($this->getData("hitCount"))
                ->setGroupBy($this->getData("groupBy"));

            $this->apiLoader
                ->setRequest($this->requestWrapper->setRequest($this->_request))
                ->setApiContext($this->apiContext)
                ->load();
        } catch (\Throwable $exception)
        {
            $this->apiLoader->getApiResponsePage()->setFallback(true);
            $this->_logger->warning("BoxalinoAPI CMS Error: " . $exception->getMessage());
            $this->_logger->warning("BoxalinoAPI CMS Error: " . $exception->getTraceAsString());
        }
    }

    public function getBlocks() : \ArrayIterator
    {
        return $this->apiLoader->getApiResponsePage()->getBlocks();
    }

    public function getRtuxVariantUuid() : string
    {
        return $this->apiLoader->getApiResponsePage()->getVariantUuid();
    }

    public function getRtuxGroupBy() : string
    {
        return $this->apiLoader->getApiResponsePage()->getGroupBy();
    }

}
