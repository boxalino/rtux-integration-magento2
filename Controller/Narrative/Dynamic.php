<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Controller\Narrative;

use Boxalino\RealTimeUserExperience\Api\CurrentApiResponseViewRegistryInterface;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

/**
 * Sample controller for dynamic narrative pages
 * check: view/frontend/layout/rtux_narrative_landing.xml for more insight into page layout
 *
 * The controller will set the SEO properties on the page
 * as they have been configured in Boxalino Intelligence Admin, Narrative view
 */
class Dynamic extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CurrentApiResponseViewRegistryInterface
     */
    protected $currentApiResponseView;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CurrentApiResponseViewRegistryInterface $currentApiResponseView
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CurrentApiResponseViewRegistryInterface $currentApiResponseView
    ){
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->currentApiResponseView = $currentApiResponseView;
    }


    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        // set page heading & build the page
        $resultPage->getConfig()->getTitle()->set(
            $pageTitle = $this->currentApiResponseView->get()->getSeoPageTitle()
                ?: __("Our product selection")
        );
        $resultPage->getLayout()->getBlock('page.main.title')->setPageTitle($pageTitle);

        // set page title
        $resultPage->getConfig()->setMetaTitle(
            $this->currentApiResponseView->get()->getSeoPageMetaTitle()
                ?: __('Our product selection')
        );

        // meta description (if configured)
        $resultPage->getConfig()->setDescription(
            $this->getSeoPropertyByName("description")
                ?: __('Our products selected personally for you')
        );

        // keywords
        if ($keywords = $this->getSeoPropertyByName("keywords")) {
            $resultPage->getConfig()->setKeywords($keywords);
        }

        /* @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbs */
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_url->getUrl('')
            ]
        );

        // insert intermediate breadcrumbs if any
        // the configured JSON per store-view look like [{"label":"Campaigns","link":"/campaign/"}]
        $intermediateBreadcrumbs = $this->currentApiResponseView->get()->getSeoBreadcrumbs();
        foreach ($intermediateBreadcrumbs as $breadcrumbData) {
            if (isset($breadcrumbData['label']) && isset($breadcrumbData['link'])) {
                $label = $breadcrumbData['label'];
                $breadcrumbs->addCrumb($label,
                    [
                        'label' => $label,
                        'title' => $label,
                        'link' => $breadcrumbData['link']
                    ]
                );
            }
        }

        $breadcrumbs->addCrumb('rtux',
            [
                'label' => __($pageTitle),
                'title' => __($pageTitle),
            ]
        );

        return $resultPage;
    }

    /**
     * Access the seo properties by name (meta-tags or seo)
     * Priority can be altered
     *
     * @param string $name
     * @return array | null
     */
    protected function getSeoPropertyByName(string $name) : ?string
    {
        $seoMetaProperties = $this->currentApiResponseView->get()->getSeoMetaTagsProperties();
        if($seoMetaProperties->offsetExists($name))
        {
            return $seoMetaProperties->offsetGet($name);
        }

        $seoProperties = $this->currentApiResponseView->get()->getSeoProperties();
        if($seoProperties->offsetExists($name))
        {
            return $seoProperties->offsetGet($name);
        }

        return null;
    }

}
