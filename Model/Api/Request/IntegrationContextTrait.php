<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;

/**
 * Trait IntegrationContextTrait
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request
 */
trait IntegrationContextTrait
{
    /**
     * Set the range properties following the presented structure
     * These values have to be configured in IntelligenceAdmin as the rangeFromField and rangeToField
     *
     * @return array
     */
    public function getRangeProperties() : array
    {
        return [
            "discountedPrice" => ['from' => 'min-price', 'to' => 'max-price']
        ];
    }

    /**
     * Must match the value configured in the di.xml as an argument for the facet model in use
     * @return string
     */
    public function getFilterValuesDelimiter(): string
    {
        return "|";
    }

    /**
     * @return ParameterInterface
     */
    public function getVisibilityFilter(RequestInterface $request) : ParameterInterface
    {
        return $this->getParameterFactory()->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
            ->add("visibility" , $this->getContextVisibility());
    }

    /**
     * @return ParameterInterface
     */
    public function getActiveFilter(RequestInterface $request) : ParameterInterface
    {
        return $this->getParameterFactory()->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
            ->add("product_group_status", [\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]);
    }


}
