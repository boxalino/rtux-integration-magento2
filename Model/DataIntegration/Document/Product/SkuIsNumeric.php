<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Model\DataIntegration\Document\Product;

use Boxalino\DataIntegration\Model\DataProvider\Document\Product\ModeIntegrator;
use Boxalino\DataIntegration\Model\ResourceModel\Document\DiSchemaDataProviderResourceInterface;
use BoxalinoClientProject\BoxalinoIntegration\Model\ResourceModel\DataIntegration\Document\Product\SkuIsNumeric as DataProviderResourceModel;

/**
 * Custom localized string property "sku_is_numeric"
 */
class SkuIsNumeric extends ModeIntegrator
{

    /**
     * @param DataProviderResourceModel | DiSchemaDataProviderResourceInterface $resource
     */
    public function __construct(
        DataProviderResourceModel $resource
    ){
        $this->resourceModel = $resource;
    }

    /**
     * Must return columns: di_id, lang1, lang2, lang3
     * @return array
     */
    public function _getData(): array
    {
        return $this->getResourceModel()->getFetchAllByFieldsWebsite(
            $this->getFields(), $this->getSystemConfiguration()->getWebsiteId()
        );
    }

    /**
     * columns: di_id, sku_is_numeric as value
     * @return string[]
     */
    public function getFields(): array
    {
        return [
            $this->getDiIdField() => "c_p_e_s.entity_id",
            $this->getAttributeCode() => new \Zend_Db_Expr("IF(CONCAT('',c_p_e_s.sku * 1) = c_p_e_s.sku, 1, 0)")
        ];
    }


}
