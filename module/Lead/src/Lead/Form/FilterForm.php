<?php

namespace Lead\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterAwareInterface;
use Doctrine\ORM\EntityManager;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorInterface;

class FilterForm extends Form implements InputFilterAwareInterface {
	
	/**
	 *
	 * @var EntityManager
	 */
	protected $entityManager;
	
	/**
	 *
	 * @var InputFilter
	 */
	protected $inputFilter;
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	
	protected $url;

	public function __construct(EntityManager $entityManager)
	{
		parent::__construct();
		
		$this->entityManager = $entityManager;
	}

	public function init()
	{
		$this->serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$this->url = $this->serviceLocator->get('viewhelpermanager')
			->get('url');
		
		$r = $this->serviceLocator->get('Application')
			->getMvcEvent()
			->getRouteMatch();
		
		$rq = $this->serviceLocator->get('Request');
		
		$this->setAttribute('action', $this->url->__invoke($r->getMatchedRouteName(), [ ], [ 
				'query' => $rq->getQuery()
					->toArray() 
		], true));
		
		$this->setAttribute('method', 'GET');
		
		$this->add(array (
				'name' => 'description',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'type' => 'text',
				'options' => array (
						'label' => 'Name',
						'label_attributes' => array (
								'class' => 'sr-only' 
						) 
				),
				'attributes' => array (
						'id' => 'descriptionfilter',
						'placeholder' => 'Filter Name(s)',
						'class' => 'has-chosen description typeahead' 
				) 
		));
		
		$this->add(array (
				'name' => 'account',
				'type' => 'DoctrineModule\Form\Element\ObjectSelect',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'options' => array (
						'label' => 'Accounts',
						'label_attributes' => array (
								'class' => 'sr-only' 
						),
						'empty_option' => 'All Accounts',
						'object_manager' => $this->entityManager,
						'target_class' => 'Account\Entity\Account',
						'property' => 'name',
						'is_method' => true,
						'find_method' => array (
								'name' => 'getNames' 
						) 
				),
				'attributes' => array (
						'id' => 'accountfilter' 
				) 
		));
		
		$repository = $this->getEntityManager()
			->getRepository('Lead\Entity\Lead');
		$referrers = $repository->getReferrers(0);
		
		$this->add(array (
				'name' => 'referrer',
				'type' => 'Zend\Form\Element\Select',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'options' => array (
						'label' => 'Sources',
						'label_attributes' => array (
								'class' => 'sr-only' 
						),
						'value_options' => $referrers,
						'empty_option' => 'All Sources' 
				),
				'attributes' => array (
						'id' => 'sourcefilter' 
				) 
		));
		
		$this->add(array (
				'name' => 'lastsubmitted',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'type' => 'Application\Form\Element\DateRange',
				'options' => array (
						'label' => 'Last Submitted',
						'label_attributes' => array (
								'class' => 'sr-only' 
						) 
				),
				'attributes' => array (
						'id' => 'lastsubmitted',
						'placeholder' => 'Choose Date(s)' 
				) 
		));
		
		$this->add(array (
				'name' => 'timecreated',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'type' => 'Application\Form\Element\DateRange',
				'options' => array (
						'label' => 'Time Created',
						'label_attributes' => array (
								'class' => 'sr-only' 
						) 
				),
				'attributes' => array (
						'id' => 'timecreated',
						'placeholder' => 'Choose Date(s)' 
				) 
		));
		
		$this->add(array (
				'name' => 'submit',
				'type' => 'Zend\Form\Element\Submit',
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Filter',
						'id' => 'filtersubmit',
						'class' => 'btn btn-secondary' 
				) 
		));
	}

	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

	/**
	 *
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		if (!$this->entityManager) {
			$entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
			$this->setEntityManager($entityManager);
		}
		
		return $this->entityManager;
	}

	public function setEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		return $this;
	}

	public function __sleep()
	{
		return array ();
	}
}
