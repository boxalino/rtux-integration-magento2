<?php
namespace BoxalinoClientProject\BoxalinoIntegration\Service\Api\Response\Accessor;

use BoxalinoClientProject\BoxalinoIntegration\Model\Api\Request\IntegrationContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor\Facet as RtuxFacet;

/**
 * Class Facet
 *
 * Project-specific facet accessor
 * Falls back to the integration`s configured range properties (min-price/max-price style URL params)
 * whenever the API response does not provide a rangeFromField / rangeToField for a range facet
 * (ex: missing configuration in IntelligenceAdmin)
 *
 * @package BoxalinoClientProject\BoxalinoIntegration\Service\Api\Response\Accessor
 */
class Facet extends RtuxFacet
{
	use IntegrationContextTrait;
	
	public function load(): void
	{
		parent::load();
		
		if($this->isRange() && (is_null($this->getRangeFromField()) || is_null($this->getRangeToField())))
		{
			$properties = $this->getRangeProperties()[$this->getField()] ?? null;
			if($properties)
			{
				$this->setRangeFromField($this->getRangeFromField() ?? $properties['from']);
				$this->setRangeToField($this->getRangeToField() ?? $properties['to']);
			}
		}
	}
	
	
}
