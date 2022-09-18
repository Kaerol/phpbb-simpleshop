<?php

namespace kaerol\simpleshop\migrations;

class v_0_1_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['simpleshop_version']) && version_compare($this->config['simpleshop_version'], '0.1.0', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\dev');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'simpleshop_sale_offer'	=> array(
					'COLUMNS'				=> array(
						'id'				=> array('UINT', null, 'auto_increment'),
						'topic_id'			=> array('UINT', 0),
						'title'				=> array('VCHAR:255', ''),
						'end_date'			=> array('VCHAR:10', ''),
					),
					'PRIMARY_KEY'	=> 'id',
				),
				$this->table_prefix . 'simpleshop_sale_offer_item'	=> array(
					'COLUMNS'				=> array(
						'id'				=> array('UINT', null, 'auto_increment'),
						'sale_offer_id'		=> array('UINT', 0),
						'item_name'			=> array('VCHAR:255', ''),
					),
					'PRIMARY_KEY'	=> 'id',
				),
				$this->table_prefix . 'simpleshop_sale_offer_item_order'	=> array(
					'COLUMNS'				=> array(
						'id'				=> array('UINT', null, 'auto_increment'),
						'topic_id'			=> array('UINT', 0),
						'user_id'			=> array('UINT', 0),
						'sale_offer_id'		=> array('UINT', 0),
						'sale_offer_item_id'=> array('UINT', 0),
						'count'				=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'id',
				),
				$this->table_prefix . 'simpleshop_config'	=> array(
					'COLUMNS'	=> array(
						'id'				=> array('UINT', null, 'auto_increment'),
						'config_name'		=> array('VCHAR:255', ''),
						'config_value'		=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'id',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('easyeshop_version', '0.1.0')),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'simpleshop_sale_offer_item_order',
				$this->table_prefix . 'simpleshop_sale_offer_item',
				$this->table_prefix . 'simpleshop_sale_offer',
				$this->table_prefix . 'simpleshop_version',
			),
		);
	}
}
