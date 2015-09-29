<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Lead for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Lead\Controller;
use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use Doctrine\ORM\QueryBuilder as Builder;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use LosBase\ORM\Tools\Pagination\Paginator as LosPaginator;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Application\Hydrator\Strategy\DateTimeStrategy;
use Zend\Stdlib\ResponseInterface as Response;
use Lead\Entity\Lead;
use Account\Entity\Account;

class LeadController extends AbstractCrudController
{

	protected $defaultSort = 'id';

	protected $defaultOrder = 'desc';

	protected $defaultPageSize = 10;

	protected $paginatorRange = 5;

	protected $uniqueField = null;

	protected $uniqueEntityMessage = null;

	protected $successAddMessage = 'The Lead(s) were successfully added.';

	protected $successEditMessage = 'The Lead(s) were successfully edited.';

	protected $successAssignMessage = 'The Lead(s) were successfully assigned.';

	protected $successSubmitMessage = 'The Lead(s) were successfully submitted.';

	protected $successDeleteMessage = 'The Lead(s) were successfully deleted.';

	protected $errorEditMessage = 'There was a problem assigning your Lead(s).';

	protected $errorAssignMessage = 'There was a problem assigning your Lead(s).';

	protected $errorSubmitMessage = 'There was a problem submitting your Lead(s).';

	protected $errorDeleteMessage = 'There was a problem deleting your Lead(s).';

	var $exportHeadings;

	public function listAction ()
	{
		$pagerAction = $this->handlePager();
		$limit = $this->getLimit($this->defaultPageSize);
		// $limit = $this->getRequest()->getQuery('limit',
		// $this->defaultPageSize);
		
		$page = $this->getRequest()->getQuery('page', 0);
		$sort = $this->getRequest()->getQuery('sort', $this->defaultSort);
		$order = $this->getRequest()->getQuery('order', $this->defaultOrder);
		
		if (empty($sort)) {
			$sort = $this->defaultSort;
		}
		
		$offset = $limit * $page - $limit;
		if ($offset < 0) {
			$offset = 0;
		}
		
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $this->getEntityClass() . ' e')
			->orderBy('e.' . $sort, $order)
			->setFirstResult($offset)
			->setMaxResults($limit);
		
		$qb = $this->handleSearch($qb);
		
		$pager = $this->getPagerForm($limit);
		
		$paginator = new Paginator(
				new DoctrinePaginator(new LosPaginator($qb, false)));
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
		$ui = [
				'table' => [
						"description" => [
								"col" => 3,
								"label" => "Lead",
								"sort" => false
						],
						"account" => [
								"col" => 2,
								"label" => "Account",
								"sort" => false
						],
						"referrer" => [
								"col" => 3,
								"label" => "Source",
								"sort" => true
						],
						"timecreated" => [
								"col" => 2,
								"label" => "Date",
								"sort" => true
						]
				]
		];
		
		$filters = $this->getFilterForm($this->params()
			->fromQuery());
		$post = $this->params()->fromPost();
		
		$redirectUrl = $this->url()->fromRoute($this->getActionRoute(), [], true);
		
		if (! $pagerAction) {
			$prg = $this->prg($redirectUrl, true);
		} else {
			$prg = false;
		}
		
		if ($pagerAction) {
			$prg = false;
		}
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$form = $this->getListForm($paginator);
			return [
					'paginator' => $paginator,
					'sort' => $sort,
					'order' => $order,
					'page' => $page,
					'pager' => $pager,
					'query' => $this->params()->fromQuery(),
					'form' => $form,
					'filters' => $filters,
					'ui' => $ui
			];
		}
		
		$form = $this->getListForm($paginator, $prg);
		
		$action = $prg['bulk_action'] ?  : false;
		$sel = isset($prg['sel']) ? array_filter($prg['sel']) : false;
		$account_id = $prg['account'] ?  : false;
		if ($action && $prg && $sel && ($account_id || in_array($action, 
				[
						'unassign',
						'delete',
						'submit'
				]))) {
			$res = true;
			$count = 0;
			foreach (array_filter($prg['sel']) as $lead_id => $one) {
				if ($one) {
					$res = $this->editLead($lead_id, $account_id, $action) ? $res : false;
					$count = $res ? $count + 1 : $count;
				}
			}
			$message = $this->successEditMessage;
			if ($res) {
				
				switch ($action) {
					case 'delete':
						$message = str_replace("The", $count, 
								$this->successDeleteMessage);
						break;
					case 'unassign':
					case 'assign':
						$message = str_replace("The", $count, 
								$this->successAssignMessage);
						break;
					case 'submit':
						$message = str_replace("The", $count, 
								$this->successSubmitMessage);
						break;
					case 'assignSubmit':
						$message = str_replace("The", $count, 
								$this->successSubmitMessage);
						break;
				}
				$this->flashMessenger()->addSuccessMessage(
						$this->getServiceLocator()
							->get('translator')
							->translate($message));
			} else {
				$message = $this->errorSubmitMessage;
				
				switch ($action) {
					case 'delete':
						$message = $this->errorDeleteMessage;
						break;
					case 'unassign':
					case 'assign':
						$message = $this->errorAssignMessage;
						break;
					case 'submit':
						$message = $this->errorSubmitMessage;
						break;
					case 'assignSubmit':
						$message = $this->errorSubmitMessage;
						break;
				}
				$this->flashMessenger()->addErrorMessage(
						$this->getServiceLocator()
							->get('translator')
							->translate($message));
			}
		} else {
			$message = "One or more required fields are missing.";
			$this->flashMessenger()->addErrorMessage(
					$this->getServiceLocator()
						->get('translator')
						->translate($message));
		}
		
		return $this->redirect()->toRoute($this->getActionRoute('list'), [], 
				true);
	}

	public function viewAction ()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$entity = $objRepository->find($id);
		
		return [
				'entity' => $entity
		];
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::addAction()
	 */
	public function addAction ()
	{
		if (method_exists($this, 'getAddForm')) {
			$form = $this->getAddForm();
		} else {
			$form = $this->getForm();
		}
		
		$classe = $this->getEntityClass();
		$entity = new $classe();
		
		$this->getEventManager()->trigger('getForm', $this, 
				[
						'form' => $form,
						'entityClass' => $this->getEntityClass(),
						'id' => 0,
						'entity' => $entity
				]);
		
		$form->bind($entity);
		
		$redirectUrl = $this->url()->fromRoute($this->getActionRoute(), [], true);
		$prg = $this->fileprg($form, $redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$this->getEventManager()->trigger('getForm', $this, 
					[
							'form' => $form,
							'entityClass' => $this->getEntityClass(),
							'entity' => $entity
					]);
			
			return [
					'entityForm' => $form,
					'entity' => $entity
			];
		}
		
		$this->createServiceEvent()
			->setEntityClass($this->getEntityClass())
			->setDescription("Lead Created");
		
		$this->getEventManager()->trigger('add', $this, 
				[
						'form' => $form,
						'entityClass' => $this->getEntityClass(),
						'entity' => $entity
				]);
		
		$savedEntity = $this->getEntityService()->save($form, $entity);
		
		if ($savedEntity && $savedEntity instanceof Lead) {
			$id = $savedEntity->getId();
			$this->getServiceEvent()
				->setEntityId($id)
				->setMessage("Lead #{$id} was created.");
			
			$this->logEvent("AddAction.post");
		} else {
			return [
					'entityForm' => $form,
					'entity' => $entity
			];
		}
		
		$this->flashMessenger()->addSuccessMessage(
				$this->getServiceLocator()
					->get('translator')
					->translate($this->successAddMessage));
		
		if ($this->needAddOther($form)) {
			$action = 'add';
		} else {
			$action = 'list';
		}
		
		return $this->redirect()->toRoute($this->getActionRoute($action), [], 
				true);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::editAction()
	 */
	public function editAction ()
	{
		if (method_exists($this, 'getEditForm')) {
			$form = $this->getEditForm();
		} else {
			$form = $this->getForm();
		}
		
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$form->add(
				[
						'type' => 'Zend\Form\Element\Hidden',
						'name' => 'id',
						'attributes' => [
								'id' => 'id',
								'value' => $id
						],
						'filters' => [
								[
										'name' => 'Int'
								]
						],
						'validators' => [
								[
										'name' => 'Digits'
								]
						]
				]);
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$entity = $objRepository->find($id);
		
		$this->getEventManager()->trigger('getForm', $this, 
				[
						'form' => $form,
						'entityClass' => $this->getEntityClass(),
						'id' => $id,
						'entity' => $entity
				]);
		
		$form->bind($entity);
		
		$redirectUrl = $this->url()->fromRoute($this->getActionRoute(), [], true);
		$prg = $this->fileprg($form, $redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$this->getEventManager()->trigger('getForm', $this, 
					[
							'form' => $form,
							'entityClass' => $this->getEntityClass(),
							'id' => $id,
							'entity' => $entity
					]);
			
			return [
					'entityForm' => $form,
					'entity' => $entity
			];
		}
		
		$this->createServiceEvent()
			->setEntityId($id)
			->setEntityClass($this->getEntityClass())
			->setDescription("Lead Edited");
		
		$this->getEventManager()->trigger('edit', $this, 
				[
						'form' => $form,
						'entityClass' => $this->getEntityClass(),
						'id' => $id,
						'entity' => $entity
				]);
		
		$savedEntity = $this->getEntityService()->save($form, $entity);
		
		if ($savedEntity && $savedEntity instanceof Lead) {
			$account = $savedEntity->getAccount();
			$this->getServiceEvent()->setMessage(
					"Lead #{$id} was " . ($account ? "assigned to " .
							 $account->getName() : "unassigned") . ".");
			
			$this->logEvent("EditAction.post");
		} else {
			return [
					'entityForm' => $form,
					'entity' => $entity
			];
		}
		
		$this->flashMessenger()->addSuccessMessage(
				$this->getServiceLocator()
					->get('translator')
					->translate($this->successEditMessage));
		
		return $this->redirect()->toRoute($this->getActionRoute('list'), [], 
				true);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::deleteAction()
	 */
	public function deleteAction ()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$redirectUrl = $this->url()->fromRoute($this->getActionRoute(), [], true);
		$prg = $this->prg($redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$em = $this->getEntityManager();
			$objRepository = $em->getRepository($this->getEntityClass());
			$entity = $objRepository->find($id);
			
			return [
					'entity' => $entity
			];
		}
		
		$post = $prg;
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$entity = $objRepository->find($id);
		
		if ($this->validateDelete($post)) {
			$this->createServiceEvent()
				->setEntityId($id)
				->setEntityClass($this->getEntityClass())
				->setDescription("Lead Deleted")
				->setMessage("Lead #{$id} was deleted.");
			
			$this->logEvent("EditAction.post");
			
			if ($this->getEntityService()->delete($entity)) {
				$this->flashMessenger()->addSuccessMessage(
						$this->getServiceLocator()
							->get('translator')
							->translate($this->successDeleteMessage));
				
				return $this->redirect()->toRoute($this->getActionRoute('list'), 
						[], true);
			}
		}
		
		$this->flashMessenger()->addErrorMessage(
				$this->getServiceLocator()
					->get('translator')
					->translate($this->errorDeleteMessage));
		
		return [
				'entity' => $entity
		];
	}

	public function exportAction ()
	{
		$results = array();
		$labels = array();
		$headings = array();
		
		$sort = $this->getRequest()->getQuery('sort', $this->defaultSort);
		$order = $this->getRequest()->getQuery('order', $this->defaultOrder);
		
		if (empty($sort)) {
			$sort = $this->defaultSort;
		}
		
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $this->getEntityClass() . ' e')
			->orderBy('e.' . $sort, $order);
		$qb = $this->handleSearch($qb);
		
		$entityManager = $this->getEntityManager();
		$hydrator = new DoctrineHydrator($entityManager);
		$leadPrototype = new Lead();
		$leadArray = $hydrator->extract($leadPrototype);
		
		$results = $qb->getQuery()->getResult();
		
		$em = $this->getEntityManager();
		$attributeRepository = $em->getRepository("Lead\\Entity\\LeadAttribute");
		
		$attributes = $attributeRepository->getUniqueArray();
		
		$headings = [
				'lead' => [
						'account' => [
								'name' => 'Account'
						],
						'timecreated' => 'Time Created',
						'referrer' => 'Referrer',
						'ipaddress' => 'IP Address',
						'attributes' => $attributes
				]
		];
		
		foreach ($headings['lead'] as $property => $field) {
			$key = $property;
			if (is_array($field)) {
				foreach ($field as $externalKey => $value) {
					$key = $property . "[{$externalKey}]";
					$labels[$key] = $value;
				}
			} else {
				$labels[$key] = $field;
			}
		}
		
		$this->exportHeadings = array_values($labels);
		
		return $this->csvExport('Lead Report (' . date('Y-m-d') . ').csv', 
				$this->exportHeadings, $results, 
				array(
						$this,
						'extractLead'
				));
	}

	public function getForm ($entityClass = null)
	{
		$form = parent::getForm($entityClass);
		
		if ($form) {
			$entityClass = $entityClass ?  : $this->getEntityClass();
			$hydrator = new DoctrineHydrator($this->getEntityManager(), 
					$entityClass);
			$hydrator->addStrategy('timecreated', new DateTimeStrategy());
			$form->setHydrator($hydrator);
			if ($form->has('submit')) {
				$form->get('submit')->setLabel('Save');
			}
			if ($form->has('cancelar')) {
				$form->get('cancelar')
					->setLabel('Cancel')
					->setName('cancel');
			}
		}
		return $form;
	}

	protected function getListForm (Paginator $paginator, $data = [])
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('FormElementManager')->get('Lead\Form\ListForm');
		$form->setName('leadbatchform');
		
		if ($paginator->count() > 0) {
			// Batch Form
			
			foreach ($paginator as $entity) {
				$cbx = new \Zend\Form\Element\Checkbox(
						"sel[" . $entity->getId() . "]");
				$form->add($cbx);
			}
		}
		
		if ($data) {
			$form->setData($data);
			if (! $form->isValid()) {
				$form->setData(array());
			}
		}
		return $form;
	}

	protected function getFilterForm ($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('FormElementManager')->get('Lead\Form\FilterForm');
		$form->setInputFilter($form->getInputFilter());
		if ($data) {
			$form->setData($data);
			if (! $form->isValid()) {
				$form->setData(array());
			}
		}
		return $form;
	}

	protected function getAddForm ($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('Lead\Form\AddFormFactory');
		$form->get('cancel')->setAttributes(
				[
						'onclick' => 'top.location=\'' . $this->url()
							->fromRoute($this->getActionRoute('list')) . '\''
				]);
		$form->setInputFilter($form->getInputFilter());
		if ($data) {
			$form->setData($data);
			if (! $form->isValid()) {
				$form->setData(array());
			}
		}
		
		return $form;
	}

	public function handleSearch (Builder $qb)
	{
		$query = $this->getRequest()->getQuery();
		$filters = [
				'daterange',
				'account',
				'referrer'
		];
		if ($query) {
			$where = [];
			$params = [];
			foreach ($filters as $condition) {
				if (isset($query[$condition]) && "" !== $query[$condition]) {
					switch ($condition) {
						case 'daterange':
							list ($from, $to) = array_map(
									function  ($d)
									{
										return date('Y-m-d', strtotime($d));
									}, explode("-", $query[$condition]));
							$where['from'] = $from . ' 00:00:00';
							$where['to'] = $to . ' 23:59:59';
							$qb->andWhere(
									$qb->expr()
										->between("e.timecreated", ":from", 
											":to"));
							break;
						case 'account':
							switch ($query[$condition]) {
								case 'none':
									$qb->leftJoin('e.account', 'a');
									$qb->andWhere("a.id IS NULL");
									break;
								default:
									$where['id'] = $query[$condition];
									$qb->leftJoin('e.account', 'a');
									$qb->andWhere("a.id = :id");
									break;
							}
							break;
						case 'referrer':
							$where['referrer'] = "%://{$query[$condition]}%";
							$qb->andWhere("e.referrer LIKE :referrer");
							break;
					}
				}
			}
			if ($where) {
				foreach ($where as $key => $value) {
					$qb->setParameter($key, $value);
				}
			}
		}
		return $qb;
	}

	protected function editLead ($lead_id, $account_id = false, $action = 'assign')
	{
		$result = true;
		$account = false;
		$actions = [];
		
		$em = $this->getEntityManager();
		$leadRepository = $em->getRepository($this->getEntityClass());
		$lead = $leadRepository->find($lead_id);
		
		if ($account_id) {
			$accountRepository = $em->getRepository('Account\Entity\Account');
			$account = $accountRepository->find($account_id);
		}
		
		switch ($action) {
			case 'assign':
				$actions[] = 'assign';
				break;
			case 'unassign':
				$actions[] = 'unassign';
				break;
			case 'delete':
				$actions[] = 'delete';
				break;
			case 'submit':
				$actions[] = 'submit';
				break;
			case 'assignSubmit':
				$actions[] = 'assign';
				$actions[] = 'submit';
				break;
		}
		
		if ($actions && $lead instanceof Lead) {
			foreach ($actions as $action) {
				$shouldLog = false;
				$this->createServiceEvent()
					->setEntityId($lead_id)
					->setEntityClass($this->getEntityClass())
					->setDescription("Lead Edited");
				switch ($action) {
					
					case 'assign':
						
						if ($lead instanceof Lead && $account instanceof Account) {
							$lead->setAccount($account);
							
							try {
								$em->persist($lead);
								$em->flush();
								$this->getServiceEvent()->setMessage(
										"Lead #{$lead_id} was assigned to " .
												 $account->getName());
								$shouldLog = true;
							} catch (\Exception $e) {
								$this->logError($e);
								return false;
							}
						}
						break;
					
					case 'unassign':
						
						if ($lead instanceof Lead) {
							$lead->setAccount(null);
							
							try {
								$em->persist($lead);
								$em->flush();
								$this->getServiceEvent()->setMessage(
										"Lead #{$lead_id} was unassigned");
								$shouldLog = true;
							} catch (\Exception $e) {
								$this->logError($e);
								return false;
							}
						}
						break;
					
					case 'submit':
						
						try {
							$response = $this->forward()->dispatch(
									'Lead\Controller\Services', 
									array(
											'action' => 'process',
											'id' => $lead_id
									));
							$shouldLog = false;
						} catch (\Exception $e) {
							$this->logError($e);
							return false;
						}
						break;
					
					case 'delete':
						$this->getServiceEvent()->setMessage(
								"Lead #{$lead_id} was deleted.");
						$this->getServiceEvent()->setDescription("Lead Deleted");
						$this->logEvent("DeleteAction.post");
						$this->createServiceEvent();
						if ($this->getEntityService()->delete($lead)) {
							$shouldLog = true;
						} else {
							$this->logError(
									new \Exception(
											"Lead #{$lead_id} could not be deleted.", 
											400));
							return false;
						}
						break;
				}
				if ($shouldLog && $this->getServiceEvent()->getDescription()) {
					$this->logEvent("EditAction.post");
				}
			}
		}
		return $result ? true : false;
	}

	public function extractLead (Lead $lead)
	{
		$headings = $this->exportHeadings;
		
		$entityManager = $this->getEntityManager();
		$hydrator = new DoctrineHydrator($entityManager);
		$leadArray = $hydrator->extract($lead);
		$output = array_combine($headings, array_pad([], count($headings), ""));
		
		foreach ($headings as $heading) {
			switch ($heading) {
				case "Account":
					$account = $lead->getAccount();
					$output[$heading] = $account ? $account->getName() : "N/A";
					break;
				case "Time Created":
					$time = $lead->getTimecreated();
					if ($time instanceof \DateTime) {
						$time = date_format($time, 'Y-m-d H:i:s');
					}
					$output[$heading] = $time;
					break;
				case "Referrer":
					$output[$heading] = $lead->getReferrer();
					break;
				case "IP Address":
					$output[$heading] = $lead->getIpaddress();
					break;
				default:
					$attribute = $lead->findAttribute($heading);
					if (! $attribute) {
						$attributes = $lead->getAttributes(true)->filter(
								function  ($attribute) use( $heading)
								{
									return $attribute->getAttribute()
										->getAttributeDesc() == $heading;
								});
						if ($attributes->count() > 0) {
							$attribute = $attributes->first();
						}
					}
					if ($attribute) {
						$output[$heading] = $attribute->getValue();
					}
					break;
			}
		}
		return $output;
	}

	protected function logEvent ($event)
	{
		$this->getEventManager()->trigger($event, $this->getServiceEvent());
	}

	protected function logError (\Exception $e)
	{
		$this->getServiceEvent()->setIsError(true);
		$this->getServiceEvent()->setMessage($e->getMessage());
		$this->getServiceEvent()->setResult($e->getTraceAsString());
		$this->logEvent('RuntimeError');
	}
}