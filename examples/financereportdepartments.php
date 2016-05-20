<?php
namespace Yadadya\Shopmate\Finance;

use \Bitrix\Iblock;
use \Bitrix\Sale;

use Bitrix\Main\Entity;
use \Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ReportDepartments extends Entity\Query
{
	function getElements(array $parameters = array())
	{
		if(!empty($parameters["select"]))
			foreach($parameters["select"] as $key => $field)
				switch ($field) 
				{
					case "SALE":
						$filter = "";
						if(!empty($parameters["filter"]["DATE_FROM"]))
						{
							$date = date("Y-m-d 00:00:00", strtotime($parameters["filter"]["DATE_FROM"]));
							$filter .= " AND b_sale_order.DATE_PAYED >= '".$date."'";
						}
						if(!empty($parameters["filter"]["DATE_TO"]))
						{
							$date = date("Y-m-d 59:59:59", strtotime($parameters["filter"]["DATE_TO"]));
							$filter .= " AND b_sale_order.DATE_PAYED <= '".$date."'";
						}
						$parameters["select"][$key] = new Entity\ExpressionField(
							$field, 
							"(SELECT SUM(b_sale_basket.QUANTITY * b_sale_basket.PRICE)
								FROM b_sale_basket, b_sale_order
								WHERE 
									b_sale_basket.ORDER_ID = b_sale_order.ID AND
									b_sale_order.PAYED = 'Y' AND
									b_sale_order.CANCELED = 'N' AND
									b_sale_basket.PRODUCT_ID = %s".$filter.")",
							"ID"
						);
						break;

					case "PURCHASE":
						$filter = "";
						if(!empty($parameters["filter"]["DATE_FROM"]))
						{
							$date = date("Y-m-d 00:00:00", strtotime($parameters["filter"]["DATE_FROM"]));
							$filter .= " AND b_catalog_store_docs.DATE_DOCUMENT >= '".$date."'";
						}
						if(!empty($parameters["filter"]["DATE_TO"]))
						{
							$date = date("Y-m-d 59:59:59", strtotime($parameters["filter"]["DATE_TO"]));
							$filter .= " AND b_catalog_store_docs.DATE_DOCUMENT <= '".$date."'";
						}

						$parameters["select"][$key] = new Entity\ExpressionField(
							$field, 
							"(SELECT SUM(b_catalog_docs_element.AMOUNT * b_catalog_docs_element.PURCHASING_PRICE)
								FROM b_catalog_docs_element, b_catalog_store_docs
								WHERE 
									b_catalog_docs_element.DOC_ID = b_catalog_store_docs.ID AND
									b_catalog_store_docs.DOC_TYPE = 'A' AND
									b_catalog_store_docs.STATUS = 'Y' AND
									b_catalog_docs_element.ELEMENT_ID = %s".$filter.")",
							"ID"
						);
						break;

					case "PROFIT":
						$parameters["select"][$key] = new Entity\ExpressionField(
							$field, 
							"%s - %s",
							array("SALE", "PURCHASE")
						);
						break;
				}

		if(!empty($parameters["filter"]))
			foreach($parameters["filter"] as $field => $value)
				if(!empty($value))
					switch ($field) 
					{
						case "SECTION_ID":
							if($parameters["filter"]["INCLUDE_SUBSECTIONS"] == "Y")
							{
								$parameters["filter"]["@IBLOCK_SECTION_ID"] = new \Bitrix\Main\DB\SqlExpression("(SELECT BSE.IBLOCK_SECTION_ID
									FROM b_iblock_section_element BSE
									INNER JOIN b_iblock_section BSubS ON BSE.IBLOCK_SECTION_ID = BSubS.ID
									INNER JOIN b_iblock_section BS ON (BSubS.IBLOCK_ID=BS.IBLOCK_ID
										AND BSubS.LEFT_MARGIN>=BS.LEFT_MARGIN
										AND BSubS.RIGHT_MARGIN<=BS.RIGHT_MARGIN)
									WHERE ((BS.ID IN (".(is_array($parameters["filter"]["SECTION_ID"]) ? implode($parameters["filter"]["SECTION_ID"], ",") : $parameters["filter"]["SECTION_ID"])."))))");
							}
							else
							{
								$parameters["filter"]["IBLOCK_SECTION_ID"] = $value;
							}
							break;
					}

		foreach(array("DATE_FROM", "DATE_TO", "SECTION_ID", "INCLUDE_SUBSECTIONS") as $field) 
			unset($parameters["filter"][$field]);

		return Iblock\ElementTable::getList($parameters);
	}

	function getSections(array $parameters = array())
	{
		if(!empty($parameters["select"]))
			foreach($parameters["select"] as $key => $field)
				switch ($field) 
				{
					case "SALE":
						$filter = "";
						if(!empty($parameters["filter"]["DATE_FROM"]))
						{
							$date = date("Y-m-d 00:00:00", strtotime($parameters["filter"]["DATE_FROM"]));
							$filter .= " AND b_sale_order.DATE_PAYED >= '".$date."'";
						}
						if(!empty($parameters["filter"]["DATE_TO"]))
						{
							$date = date("Y-m-d 59:59:59", strtotime($parameters["filter"]["DATE_TO"]));
							$filter .= " AND b_sale_order.DATE_PAYED <= '".$date."'";
						}
						$parameters["runtime"][$key] = new Entity\ExpressionField(
							$field, 
							"SUM((SELECT SUM(b_sale_basket.QUANTITY * b_sale_basket.PRICE)
								FROM b_sale_basket, b_sale_order
								WHERE 
									b_sale_basket.ORDER_ID = b_sale_order.ID AND
									b_sale_order.PAYED = 'Y' AND
									b_sale_order.CANCELED = 'N' AND
									b_sale_basket.PRODUCT_ID = iblock_section_be.ID".$filter."))"
						);
						break;

					case "PURCHASE":
						$filter = "";
						if(!empty($parameters["filter"]["DATE_FROM"]))
						{
							$date = date("Y-m-d 00:00:00", strtotime($parameters["filter"]["DATE_FROM"]));
							$filter .= " AND b_catalog_store_docs.DATE_DOCUMENT >= '".$date."'";
						}
						if(!empty($parameters["filter"]["DATE_TO"]))
						{
							$date = date("Y-m-d 59:59:59", strtotime($parameters["filter"]["DATE_TO"]));
							$filter .= " AND b_catalog_store_docs.DATE_DOCUMENT <= '".$date."'";
						}

						$parameters["runtime"][$key] = new Entity\ExpressionField(
							$field, 
							"SUM((SELECT SUM(b_catalog_docs_element.AMOUNT * b_catalog_docs_element.PURCHASING_PRICE)
								FROM b_catalog_docs_element, b_catalog_store_docs
								WHERE 
									b_catalog_docs_element.DOC_ID = b_catalog_store_docs.ID AND
									b_catalog_store_docs.DOC_TYPE = 'A' AND
									b_catalog_store_docs.STATUS = 'Y' AND
									b_catalog_docs_element.ELEMENT_ID = iblock_section_be.ID".$filter."))"
						);
						break;

					case "PROFIT":
						$parameters["runtime"][$key] = new Entity\ExpressionField(
							$field, 
							"%s - %s",
							array("SALE", "PURCHASE")
						);
						break;
				}

		foreach(array("DATE_FROM", "DATE_TO", "SECTION_ID", "INCLUDE_SUBSECTIONS") as $field) 
			unset($parameters["filter"][$field]);

		/*$parameters["runtime"][] = new Entity\ReferenceField(
			'BSTEMP',
			'Bitrix\Iblock\Section',
			array('=this.IBLOCK_ID' => 'ref.IBLOCK_ID'),
			array('join_type' => 'INNER')
		);//INNER JOIN b_iblock_section BSTEMP ON BSTEMP.IBLOCK_ID = b_iblock_section.IBLOCK_ID

		$parameters["runtime"][] = new Entity\ReferenceField(
			'BSE',
			'Bitrix\Iblock\SectionElementTable',
			array('=this.IBLOCK_SECTION_ID' => new \Bitrix\Main\DB\SqlExpression('`iblock_section_bstemp`.`ID`')),
			array('join_type' => 'LEFT')
		);//LEFT JOIN b_iblock_section_element BSE ON BSE.IBLOCK_SECTION_ID=BSTEMP.ID 

		$parameters["runtime"][] = new Entity\ReferenceField(
			'BE',
			'Bitrix\Iblock\ElementTable',
			array(
				'=this.ID' => new \Bitrix\Main\DB\SqlExpression('`iblock_section_bse`.`IBLOCK_ELEMENT_ID`'),
				'=this.IBLOCK_ID' => new \Bitrix\Main\DB\SqlExpression('`iblock_section`.`IBLOCK_ID`'),
			),
			array('join_type' => 'LEFT')
		);//LEFT JOIN b_iblock_element BE ON (BSE.IBLOCK_ELEMENT_ID=BE.ID
			//AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
			//AND BE.IBLOCK_ID = b_iblock_section.IBLOCK_ID*/

		$query = new ReportDepartments(Iblock\SectionTable::getEntity());

		if(!isset($parameters['select']))
		{
			$query->setSelect(array('*'));
		}

		foreach($parameters as $param => $value)
		{
			switch($param)
			{
				case 'select':
					$query->setSelect($value);
					break;
				case 'filter':
					$query->setFilter($value);
					break;
				case 'group':
					$query->setGroup($value);
					break;
				case 'order';
					$query->setOrder($value);
					break;
				case 'limit':
					$query->setLimit($value);
					break;
				case 'offset':
					$query->setOffset($value);
					break;
				case 'count_total':
					$query->countTotal($value);
					break;
				case 'runtime':
					foreach ($value as $name => $fieldInfo)
					{
						$query->registerRuntimeField($name, $fieldInfo);
					}
					break;
				case 'data_doubling':
					if($value)
						$query->enableDataDoubling();
					else
						$query->disableDataDoubling();
					break;
				default:
					throw new Main\ArgumentException("Unknown parameter: ".$param, $param);
			}
		}

		/*$sql = $query->getQuery();
		print_p($parameters["filter"]);
		print_p($sql);*/

		return $query->exec();
	}

	protected function buildJoin()
	{
		return "
INNER JOIN `b_iblock_section` BSTEMP ON BSTEMP.IBLOCK_ID = `iblock_section`.IBLOCK_ID
						LEFT JOIN b_iblock_section_element BSE ON BSE.IBLOCK_SECTION_ID=BSTEMP.ID 
					LEFT JOIN b_iblock_element iblock_section_be ON (BSE.IBLOCK_ELEMENT_ID=iblock_section_be.ID
						AND ((iblock_section_be.WF_STATUS_ID=1 AND iblock_section_be.WF_PARENT_ELEMENT_ID IS NULL )
						AND iblock_section_be.IBLOCK_ID = `iblock_section`.IBLOCK_ID
				)
				) 
".parent::buildJoin();
	}

	protected function buildWhere()
	{
		$where = parent::buildWhere();
		return "(1=1 AND BSTEMP.IBLOCK_ID = `iblock_section`.IBLOCK_ID
						AND BSTEMP.LEFT_MARGIN >= `iblock_section`.LEFT_MARGIN
						AND BSTEMP.RIGHT_MARGIN <= `iblock_section`.RIGHT_MARGIN)".(!empty($where) ? " AND ".$where : "");
	}
}
