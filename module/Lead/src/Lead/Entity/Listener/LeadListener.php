<?php
namespace Lead\Entity\Listener;
use Doctrine\Common\Collections\ArrayCollection;
use Lead\Entity\Lead;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @author arstropica
 *        
 */
class LeadListener
{

	private $id;

	/**
	 * Constructor
	 */
	public function __construct ()
	{}

	/**
	 * @ORM\PostLoad
	 *
	 * @param Lead $lead        	
	 * @param LifecycleEventArgs $event        	
	 */
	public function postLoad (Lead $lead, LifecycleEventArgs $event)
	{
		// Get the values for the ArrayCollection and sort it using the function
		$attributes = $lead->getAttributes();
		
		// sort as you like
		usort($attributes, 
				function  ($a, $b)
				{
					$aOrder = $bOrder = 0;
					$aAttribute = $a->getAttribute();
					if ($aAttribute) {
						$aOrder = $aAttribute->getAttributeOrder();
					}
					$bAttribute = $b->getAttribute();
					if ($bAttribute) {
						$bOrder = $bAttribute->getAttributeOrder();
					}
					return $aOrder - $bOrder;
				});
		
		// Clear the current collection values and reintroduce in new order.
		$lead->setAttributes(new ArrayCollection($attributes));
	}
}

?>