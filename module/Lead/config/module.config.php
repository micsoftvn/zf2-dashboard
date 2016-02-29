<?php
return array (
		'controllers' => array (
				'invokables' => array (
						'Lead\Controller\Lead' => 'Lead\Controller\LeadController',
						'Lead\Controller\TenStreet' => 'Lead\Controller\TenStreetController',
						'Lead\Controller\Email' => 'Lead\Controller\EmailController',
						'Lead\Controller\Import' => 'Lead\Controller\ImportController',
						'Lead\Controller\Services' => 'Lead\Controller\ServicesController',
						'Lead\Controller\Attribute' => 'Lead\Controller\AttributeController',
						'Lead\Controller\Ajax' => 'Lead\Controller\AjaxController',
						'Lead\Controller\Report' => 'Lead\Controller\ReportController' 
				),
				'factories' => array (
						'Lead\Controller\Rest' => 'Lead\Controller\Factory\RestControllerFactory' 
				) 
		),
		'doctrine' => array (
				'driver' => array (
						'Lead_driver' => array (
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'redis',
								'paths' => array (
										__DIR__ . '/../src/Lead/Entity/' 
								) 
						),
						'orm_default' => array (
								'drivers' => array (
										'Lead\Entity' => 'Lead_driver' 
								) 
						) 
				) 
		),
		'router' => array (
				'routes' => array (
						'lead' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/lead',
										'defaults' => array (
												'controller' => 'Lead\Controller\Lead',
												'action' => 'list' 
										) 
								),
								'may_terminate' => true,
								'child_routes' => array (
										'list' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/list',
														'defaults' => array (
																'controller' => 'Lead\Controller\Lead',
																'action' => 'list' 
														) 
												) 
										),
										'add' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/add',
														'defaults' => array (
																'controller' => 'Lead\Controller\Lead',
																'action' => 'add' 
														) 
												) 
										),
										'edit' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/edit[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Lead',
																'action' => 'edit',
																'id' => 0 
														) 
												) 
										),
										'view' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/view[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Lead',
																'action' => 'view',
																'id' => 0 
														) 
												) 
										),
										'delete' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/delete[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Lead',
																'action' => 'delete',
																'id' => 0 
														) 
												) 
										),
										'submit' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/submit[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Lead',
																'action' => 'submit',
																'id' => 0 
														) 
												) 
										),
										'export' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/export',
														'defaults' => array (
																'controller' => 'Lead\Controller\Lead',
																'action' => 'export' 
														) 
												) 
										),
										'ajax' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/ajax',
														'defaults' => array (
																'controller' => 'Lead\Controller\Ajax',
																'action' => 'list' 
														) 
												),
												'may_terminate' => true,
												'child_routes' => array (
														'tenstreet' => array (
																'type' => 'Literal',
																'options' => array (
																		'route' => '/name',
																		'defaults' => array (
																				'controller' => 'Lead\Controller\Ajax',
																				'action' => 'name' 
																		) 
																) 
														) 
												) 
										),
										'search' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/search[/:id]',
														'constraints' => array (
																'id' => '[a-zA-Z0-9_-]*' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Report',
																'action' => 'search',
																'id' => 0 
														) 
												),
												'may_terminate' => true,
												'child_routes' => array (
														'export' => array (
																'type' => 'Literal',
																'options' => array (
																		'route' => '/export',
																		'defaults' => array (
																				'controller' => 'Lead\Controller\Report',
																				'action' => 'export' 
																		) 
																) 
														),
														'result' => array (
																'type' => 'Segment',
																'options' => array (
																		'route' => '/result[/:lead]',
																		'constraints' => array (
																				'lead' => '[0-9]+' 
																		),
																		'defaults' => array (
																				'controller' => 'Lead\Controller\Report',
																				'action' => 'result',
																				'lead' => 0 
																		) 
																) 
														) 
												) 
										) 
								) 
						),
						'services' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/services',
										'defaults' => array (
												'controller' => 'Lead\Controller\Lead',
												'action' => 'list' 
										) 
								),
								'may_terminate' => true,
								'child_routes' => array (
										'tenstreet' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/tenstreet',
														'defaults' => array (
																'controller' => 'Lead\Controller\TenStreet',
																'action' => 'list' 
														) 
												),
												'may_terminate' => true,
												'child_routes' => array (
														'list' => array (
																'type' => 'Literal',
																'options' => array (
																		'route' => '/list',
																		'defaults' => array (
																				'controller' => 'Lead\Controller\TenStreet',
																				'action' => 'list' 
																		) 
																) 
														),
														'view' => array (
																'type' => 'Segment',
																'options' => array (
																		'route' => '/view[/:id]',
																		'constraints' => array (
																				'id' => '[0-9]+' 
																		),
																		'defaults' => array (
																				'controller' => 'Lead\Controller\TenStreet',
																				'action' => 'view',
																				'id' => 0 
																		) 
																) 
														),
														'edit' => array (
																'type' => 'Segment',
																'options' => array (
																		'route' => '/edit[/:id]',
																		'constraints' => array (
																				'id' => '[0-9]+' 
																		),
																		'defaults' => array (
																				'controller' => 'Lead\Controller\TenStreet',
																				'action' => 'edit',
																				'id' => 0 
																		) 
																) 
														),
														'submit' => array (
																'type' => 'Segment',
																'options' => array (
																		'route' => '/submit[/:id]',
																		'defaults' => array (
																				'controller' => 'Lead\Controller\TenStreet',
																				'action' => 'submit',
																				'id' => 0 
																		) 
																) 
														),
														'export' => array (
																'type' => 'Literal',
																'options' => array (
																		'route' => '/export',
																		'defaults' => array (
																				'controller' => 'Lead\Controller\TenStreet',
																				'action' => 'export' 
																		) 
																) 
														) 
												) 
										),
										'email' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/email',
														'defaults' => array (
																'controller' => 'Lead\Controller\Email',
																'action' => 'list' 
														) 
												),
												'may_terminate' => true,
												'child_routes' => array (
														'list' => array (
																'type' => 'Literal',
																'options' => array (
																		'route' => '/list',
																		'defaults' => array (
																				'controller' => 'Lead\Controller\Email',
																				'action' => 'list' 
																		) 
																) 
														),
														'view' => array (
																'type' => 'Segment',
																'options' => array (
																		'route' => '/view[/:id]',
																		'constraints' => array (
																				'id' => '[0-9]+' 
																		),
																		'defaults' => array (
																				'controller' => 'Lead\Controller\Email',
																				'action' => 'view',
																				'id' => 0 
																		) 
																) 
														),
														'edit' => array (
																'type' => 'Segment',
																'options' => array (
																		'route' => '/edit[/:id]',
																		'constraints' => array (
																				'id' => '[0-9]+' 
																		),
																		'defaults' => array (
																				'controller' => 'Lead\Controller\Email',
																				'action' => 'edit',
																				'id' => 0 
																		) 
																) 
														),
														'submit' => array (
																'type' => 'Segment',
																'options' => array (
																		'route' => '/submit[/:id]',
																		'defaults' => array (
																				'controller' => 'Lead\Controller\Email',
																				'action' => 'submit',
																				'id' => 0 
																		) 
																) 
														),
														'export' => array (
																'type' => 'Literal',
																'options' => array (
																		'route' => '/export',
																		'defaults' => array (
																				'controller' => 'Lead\Controller\Email',
																				'action' => 'export' 
																		) 
																) 
														) 
												) 
										),
										'process' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/process[/:id]',
														'constraints' => array (
																'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Services',
																'action' => 'process',
																'id' => 0 
														) 
												) 
										) 
								) 
						),
						'import' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/import',
										'defaults' => array (
												'controller' => 'Lead\Controller\Import',
												'action' => 'import' 
										) 
								) 
						),
						'rest-api' => array (
								'type' => 'Segment',
								'options' => array (
										'route' => '/rest-api[/:action][/:id]',
										'constraints' => array (
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'id' => '[0-9]+' 
										),
										'defaults' => array (
												'controller' => 'Lead\Controller\Rest',
												'action' => 'index' 
										) 
								) 
						),
						'attribute' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/attribute',
										'defaults' => array (
												'controller' => 'Lead\Controller\Attribute',
												'action' => 'list' 
										) 
								),
								'may_terminate' => true,
								'child_routes' => array (
										'list' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/list',
														'defaults' => array (
																'controller' => 'Lead\Controller\Attribute',
																'action' => 'list' 
														) 
												) 
										),
										'add' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/add',
														'defaults' => array (
																'controller' => 'Lead\Controller\Attribute',
																'action' => 'add' 
														) 
												) 
										),
										'edit' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/edit[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Attribute',
																'action' => 'edit',
																'id' => 0 
														) 
												) 
										),
										'delete' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/delete[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Attribute',
																'action' => 'delete',
																'id' => 0 
														) 
												) 
										),
										'merge' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/merge[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Attribute',
																'action' => 'merge',
																'id' => 0 
														) 
												) 
										),
										'sort' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/sort[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Lead\Controller\Attribute',
																'action' => 'sort',
																'id' => 0 
														) 
												) 
										) 
								) 
						) 
				) 
		),
		'view_manager' => array (
				'template_path_stack' => array (
						'Lead' => __DIR__ . '/../view' 
				),
				'strategies' => array (
						'ViewJsonStrategy' 
				) 
		),
		'service_manager' => array (
				'invokables' => array (
						'Lead\Entity\Listener\LeadListener' => 'Lead\Entity\Listener\LeadListener',
						'Lead\Entity\Listener\SearchableListener' => 'Lead\Entity\Listener\SearchableListener' 
				),
				'factories' => array (
						'Lead\Form\AddFormFactory' => 'Lead\Form\Factory\AddFormFactory',
						'Lead\Form\EditFormFactory' => 'Lead\Form\Factory\EditFormFactory',
						'Lead\Form\Attribute\AddFormFactory' => 'Lead\Form\Factory\Attribute\AddFormFactory',
						'Lead\Form\Attribute\MergeFormFactory' => 'Lead\Form\Factory\Attribute\MergeFormFactory',
						'Lead\Model\LeadAttribute' => 'Lead\Model\Factory\LeadAttributeModelFactory' 
				) 
		),
		'form_elements' => array (
				'invokables' => array (
						'Lead\Form\AddForm' => 'Lead\Form\AddForm',
						'Lead\Form\EditForm' => 'Lead\Form\EditForm',
						'Lead\Form\Attribute\AddForm' => 'Lead\Form\Attribute\AddForm',
						'Lead\Form\Attribute\MergeForm' => 'Lead\Form\Attribute\MergeForm',
						'Lead\Form\SearchForm' => 'Lead\Form\SearchForm' 
				),
				'factories' => array (
						'Lead\Form\FilterForm' => 'Lead\Form\Factory\FilterFormFactory',
						'Lead\Form\HiddenFilterForm' => 'Lead\Form\Factory\HiddenFilterFormFactory',
						'Lead\Form\Attribute\FilterForm' => 'Lead\Form\Factory\Attribute\FilterFormFactory',
						'Lead\Form\ListForm' => 'Lead\Form\Factory\ListFormFactory',
						'Lead\Form\ImportForm' => 'Lead\Form\Factory\ImportFormFactory',
						'Lead\Form\SearchFormFactory' => 'Lead\Form\Factory\SearchFormFactory' 
				) 
		) 
);
