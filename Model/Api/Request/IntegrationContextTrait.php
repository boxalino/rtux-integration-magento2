<?php
namespace Boxalino\RealTimeUserExperienceIntegration\Model\Api\Request;

/**
 * Trait IntegrationContextTrait
 * @package Boxalino\RealTimeUserExperienceIntegration\Model\Api\Request
 */
trait IntegrationContextTrait
{
    /**
     * Set the range properties following the presented structure
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
     * @return string
     */
    public function getFilterValuesSeparator(): string
    {
        return "|";
    }

}
