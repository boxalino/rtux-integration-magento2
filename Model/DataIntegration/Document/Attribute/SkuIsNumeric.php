<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Model\DataIntegration\Document\Attribute;

use Boxalino\DataIntegration\Model\DataProvider\Document\Attribute\CustomAttributeAbstract;

/**
 * Extend with other properties as defined in the doc_attribute schema
 * https://boxalino.atlassian.net/wiki/spaces/BPKB/pages/252280945/doc_attribute
 */
class SkuIsNumeric extends CustomAttributeAbstract
{

    public function getCode(): string
    {
        return "sku_is_numeric";
    }

    public function isLocalized(): bool
    {
        return false;
    }
  
    public function getLabel() : array
    {
        return [
            "en" => "SKU is Numeric",
            "fr" => "SKU est numérique",
            "de" => "SKU ist numerisch",
            "it" => "SKU è numerico"
        ];
    }
    
    
}
