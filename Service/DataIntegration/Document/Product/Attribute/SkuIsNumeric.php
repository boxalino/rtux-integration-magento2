<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Service\DataIntegration\Document\Product\Attribute;


use Boxalino\DataIntegration\Service\Document\Product\Attribute\StringAttributeAbstract;

/**
 * Class SkuIsNumeric
 * 
 * @package BoxalinoClientProject\BoxalinoIntegration\Service\DataIntegration\Document\Product\Attribute;
 */
class SkuIsNumeric extends StringAttributeAbstract
{

    /**
     * The name of the mapping in the di.xml between doc handler - property handler - data provider
     * Also used as attribute name
     * 
     * @return string
     */
    public function getResolverType(): string
    {
        return "sku_is_numeric";
    }


}
