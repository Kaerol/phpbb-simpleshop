<?php

namespace kaerol\simpleshop\controller;

interface report_interface
{

	public function items_report($post_id, $type_id);
	public function person_report($post_id, $type_id);
}
