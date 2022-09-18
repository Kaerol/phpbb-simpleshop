<?php

namespace kaerol\simpleshop\event;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return [];
/*		array(
			'core.user_setup'								=> 'load_language_on_setup',
			'core.posting_modify_template_vars'				=> 'calendar',
			'core.page_header'								=> 'calendar_on_header',
			'core.viewonline_overwrite_location'			=> 'viewonline_page',
			'core.submit_post_end'							=> 'send_data_to_table',
			'core.viewtopic_assign_template_vars_before'	=> 'modify_participants_list',
			'core.viewtopic_modify_post_row'				=> 'display_participants_list',
		);
*/		
	}

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

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string php file extension */
	protected $php_ext;

	/** @var string phpbb root path */
	protected $phpbb_root_path;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/**
	* Constructor
	*
	* @param \phpbb\config\config		$config
	* @param \phpbb\controller\helper	$helper
	* @param \phpbb\template\template	$template
	* @param \phpbb\user				$this->user
	*/

	public function __construct(Container $phpbb_container, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db,
								\phpbb\controller\helper $helper, \phpbb\auth\auth $auth, \phpbb\template\template $template,
								\phpbb\user $user, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\event\dispatcher_interface $phpbb_dispatcher,
								$table_prefix, $phpbb_root_path, $php_ext )
	{
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
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

	private function get_config ($config_name)
	{
		return null;
		/*
		$sql = 'SELECT config_value
				FROM ' . $this->calendar_config_table . '
					WHERE config_name = "' . (string) $config_name . '"';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		return $row['config_value'];
		*/
	}

	private function set_config ($config_name, $config_value)
	{
		/*
		$sql_ary = array(
			'CONFIG_VALUE'	=> $config_value,
		);
		$sql = 'UPDATE ' . $this->calendar_config_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE config_name = "' . (string) $config_name . '"';
		$this->db->sql_query($sql);
		*/
	}
}
