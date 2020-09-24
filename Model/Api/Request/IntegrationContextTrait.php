<?php
namespace BoxalinoClientProject\BoxalinoRealTimeUserExperienceIntegration\Model\Api\Request;

/**
 * Trait IntegrationContextTrait
 * @package BoxalinoClientProject\BoxalinoRealTimeUserExperienceIntegration\Model\Api\Request
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

}
