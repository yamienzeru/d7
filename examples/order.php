<?php
namespace Yadadya\Shopmate\Cash;

use \Bitrix\Sale;

class OrderTable extends Sale\OrderTable
{
	public static function getMap()
	{
		global $DB;
		return array_merge(parent::getMap(), array(
			'ACCOUNT_NUMBER' => array(
				'data_type' => 'string'
			),
			'DATE_PAYED' => array(
				'data_type' => 'datetime'
			),
			'CURRENCY' => array(
				'data_type' => 'string'
			),
			'STORE_ID' => array(
				'data_type' => 'integer'
			),
			
		));
	}
}