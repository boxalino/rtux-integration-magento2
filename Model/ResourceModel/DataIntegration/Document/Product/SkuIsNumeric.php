<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Model\ResourceModel\DataIntegration\Document\Product;

use Boxalino\DataIntegration\Model\ResourceModel\Document\Product\ModeIntegrator;

/**
 * Class SkuIsNumeric
 * SQL logic for setting the "sku_is_numeric" property
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Model\ResourceModel\DataIntegration\Document\Product
 */
class SkuIsNumeric extends ModeIntegrator
{

    /**
     * @param array $fields
     * @param string $websiteId
     * @return array
     */
    public function getFetchAllByFieldsWebsite(array $fields, string $websiteId) : array
    {
        $mainEntitySelect = $this->getEntityByWebsiteIdSelect($websiteId);
        $select = $this->adapter->select()
            ->from(
                ['c_p_e_s' => new \Zend_Db_Expr("( ". $mainEntitySelect->__toString() . ' )')],
                $fields
            );

        return $this->adapter->fetchAll($select);
    }



}
