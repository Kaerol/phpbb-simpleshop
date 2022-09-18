<?php

namespace kaerol\simpleshop\controller;

use Symfony\Component\DependencyInjection\Container;

class order_an_item implements add_interface
{
	/* @var Container */
	protected $phpbb_container;
	
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\controller\helper $controller_helper */
	protected $helper;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 */
	public function __construct(
		Container $phpbb_container, 
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\notification\manager $notification_manager,
		\phpbb\request\request $request,
		\phpbb\user $user)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->db = $db;
		$this->notification_manager = $notification_manager;
		$this->request = $request;
		$this->user = $user;
		
		$this->offer_item_order = $phpbb_container->getParameter('tables.simpleshop_sale_offer_item_order');
	}

	public function add($post_id, $sale_id)
	{		
		//if ($this->request->is_ajax() && $order_an_item)
		if ($this->request->is_ajax())
		{
			$user_id = $this->request->variable('user_id', '');			
			$items = $this->request->variable('items', [0 => ['id' => '', 'value' => '']], true);
			$sql = '';

			for($i = 0; $i < count($items); $i++)	
			{
				$item_id = $items[$i]['id'];
				$item_value = $items[$i]['value'];
				
				if ($item_value == 0 || $item_value == '0')
				{
					$sql = 'DELETE FROM ' . $this->offer_item_order . ' WHERE SALE_OFFER_ITEM_ID = '.$sale_id.' AND USER_ID = '.$user_id.' AND ITEM_ID = '.$item_id;
			/*
					return $sql;
					$this->db->sql_query($sql);						
				}else {				
					$offer_item_id = $this->_checkOrderItemExist($sale_id, $user_id, $items[$i]['id']);
				
					if (offer_item_id == -1){
						$offer_item_order = array(
							'SALE_OFFER_ITEM_ID'	=>	$sale_id,
							'POST_ID'				=>	$post_id,
							'USER_ID'				=>	$user_id,
							'ITEM_ID'				=>	$item_id,
							'NUMBER'				=>	$item_value,
						);				
						$sql = 'INSERT INTO ' . $this->offer_item_order . ' ' . $this->db->sql_build_array('INSERT', $offer_item_order);
						$this->db->sql_query($sql);				
					}else{
						$sql = 'UPDATE ' . $this->offer_item_order . ' SET NUMBER = '.$item_value.' WHERE 
										SALE_OFFER_ITEM_ID = '.$sale_id.' AND USER_ID = '.$user_id.' AND ITEM_ID = '.$item_id;
						$this->db->sql_query($sql);						
					}
			*/
				}
			}
			
			$json_response = new \phpbb\json_response;
			$data_send = array(
				'success' 			=> true,
				'POST_ID' 			=> $post_id,
				'sale_id'			=> $sale_id,
				'sql'				=> $sql,
			);
			
			return $json_response->send($data_send);
		} 
		throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
	}
	
	private function  _checkOrderItemExist($sale_id, $user_id, $item_id)
	{		
		$sql = 'SELECT ID FROM ' . $this->offer_item_order . ' WHERE SALE_OFFER_ITEM_ID = '.$sale_id.' AND USER_ID = '.$user_id.' AND ITEM_ID = '.$item_id;
		
		$result = $this->db->sql_query($sql);
		$id = -1;

		if ($result->num_rows != 0){
			$row = $this->db->sql_fetchrow($result);
			$id = $row['ID'];
		}
		$this->db->sql_freeresult($result);
		
		return $id;
	}
}
