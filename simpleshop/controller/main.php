<?php

namespace kaerol\simpleshop\controller;

use Symfony\Component\DependencyInjection\Container;

class main
{
	/* @var Container */
	protected $phpbb_container;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string php file extension */
	protected $php_ext;

	/** @var string phpbb root path */
	protected $phpbb_root_path;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	public function __construct(Container $phpbb_container,
								\phpbb\config\config $config,
								\phpbb\db\driver\driver_interface $db,
								\phpbb\controller\helper $helper,
								\phpbb\auth\auth $auth,
								\phpbb\template\template $template,
								\phpbb\user $user,
								\phpbb\language\language $language,
								\phpbb\request\request $request,
								\phpbb\event\dispatcher_interface $phpbb_dispatcher,
								$table_prefix, $phpbb_root_path, $php_ext)
	{
		print_r('controller - __construct');
		
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->user->add_lang_ext('kaerol/simpleshop', 'simpleshop');
		$this->language = $language;
		$this->auth = $auth;
		$this->request = $request;
		$this->php_ext = $php_ext;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->ext_root_path = $phpbb_root_path . 'ext/kaerol/simpleshop/';
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->table_prefix = $table_prefix;

		$this->simpleshop_config = $phpbb_container->getParameter('tables.simpleshop_config');
		$this->simpleshop_sale_offer = $phpbb_container->getParameter('tables.simpleshop_sale_offer');
		$this->simpleshop_sale_offer_item = $phpbb_container->getParameter('tables.simpleshop_sale_offer_item');
		$this->simpleshop_sale_offer_item_order = $phpbb_container->getParameter('tables.simpleshop_sale_offer_item_order');	
	}

	public function display()
	{	
		print_r('controller - display');
		
		$this->user->add_lang_ext('kaerol/simpleshop', 'simpleshop');

		return $this->helper->render('simpleshop_form.html', $this->language->lang('KAEROL_SIMPLESHOP'));
	}
}