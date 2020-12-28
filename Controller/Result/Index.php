<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Controller\Result;

use Boxalino\RealTimeUserExperience\Model\Request\ApiPageLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\Context\SearchContext;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Tests\NamingConvention\true\string;

/**
 * Class Index
 * Replace the default Magento2 search action (see di.xml preference declaration)
 * Redirect if value exists in the API response
 *
 * Can be extended with custom logic (project-based)
 * @package BoxalinoClientProject\BoxalinoIntegration\Controller\Result
 */
class Index extends \Magento\CatalogSearch\Controller\Result\Index
{
    /**
     * @var QueryFactory
     */
    protected $_queryFactory;


    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var ApiPageLoader
     */
    protected $apiLoader;

    /**
     * @var SearchContext
     */
    protected $apiContext;

    /**
     * @var RequestInterface
     */
    protected $requestWrapper;

    /**
     * Index constructor.
     *
     * @param ApiPageLoader $apiPageLoader
     * @param SearchContext $apiContext
     * @param RequestInterface $requestWrapper
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ApiPageLoader $apiPageLoader,
        SearchContext $apiContext,
        RequestInterface $requestWrapper,
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context,$catalogSession,$storeManager,$queryFactory,$layerResolver);
        $this->_logger = $logger;
        $this->requestWrapper = $requestWrapper;
        $this->apiLoader = $apiPageLoader;
        $this->apiContext = $apiContext;
    }

    /**
     * Display search result
     *
     * @return void
     */
    public function execute()
    {
        try{
            $this->apiLoader
                ->setRequest($this->requestWrapper->setRequest($this->_request))
                ->setApiContext($this->apiContext)
                ->load();

            $responseRedirectLink = $this->apiLoader->getApiResponsePage()->getRedirectUrl();
            if($responseRedirectLink)
            {
                $this->getResponse()->setRedirect($this->getRedirectLink($responseRedirectLink));
                return;
            }
        }catch(\Exception $exception){
            $this->apiLoader->getApiResponsePage()->setFallback(true);

            $this->_logger->warning("BoxalinoAPI Search Controller Error: " . $exception->getMessage());
            $this->_logger->critical($exception);
        }

        parent::execute();
    }

    /**
     * This is project specific,
     * depending on what variations of the redirect is being set in Intelligence Admin
     *
     * Sample: generic rules
     *
     * @param string $responseRedirectLink
     * @return string
     */
    protected function getRedirectLink(string $responseRedirectLink) : string
    {
        if(substr($responseRedirectLink, 0, 1 ) === "/"
            || substr($responseRedirectLink, 0, 4 ) === "http"
            || substr($responseRedirectLink, 0, 3 ) === "www"
        )
        {
            $storeUrl = $this->_storeManager->getStore()->getUrl();
            $responseRedirectLink = $storeUrl . $responseRedirectLink;
        }

        return $responseRedirectLink;
    }

}
