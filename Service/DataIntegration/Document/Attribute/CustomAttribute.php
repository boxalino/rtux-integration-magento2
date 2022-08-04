<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Service\DataIntegration\Document\Attribute;

use Boxalino\DataIntegration\Service\Document\Attribute\CustomAttributeAbstract;
use BoxalinoClientProject\BoxalinoIntegration\Model\DataIntegration\Document\Attribute\SkuIsNumeric;

/**
 * Class CustomAttribute
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Service\DataIntegration\Document\Attribute;
 */
class CustomAttribute extends CustomAttributeAbstract
{
    /**
     * Add here all custom attribute definitions
     * extend with other properties as defined in the doc_attribute schema
     * https://boxalino.atlassian.net/wiki/spaces/BPKB/pages/252280945/doc_attribute
     *
     * @return array
     */
    public function getCustomAttributesDefinition() : array
    {
        return [
            new SkuIsNumeric()
        ];
    }


}
