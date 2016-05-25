<?php
namespace Yadadya\Shopmate\Finance;

use \Bitrix\Iblock;
use \Bitrix\Sale;

use Bitrix\Main\Entity;
use \Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ReportDepartments
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
						$parameters["runtime"][$key] = new Entity\ExpressionField(
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

						$parameters["runtime"][$key] = new Entity\ExpressionField(
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
						$parameters["runtime"][$key] = new Entity\ExpressionField(
							$field, 
							"%s - %s",
							array("SALE", "PURCHASE")
						);
						break;
				}

		if(!empty($parameters["filter"]))
			foreach($parameters["filter"] as $field => $value)
				if($field === "SECTION_ID" && !empty($value)) 
				{
					if($parameters["filter"]["INCLUDE_SUBSECTIONS"] == "Y")
					{
						$parameters["filter"]["@IBLOCK_SECTION_ID"] = new \Bitrix\Main\DB\SqlExpression("(SELECT BSE.IBLOCK_SECTION_ID
							FROM b_iblock_section_element BSE
							INNER JOIN b_iblock_section BSubS ON BSE.IBLOCK_SECTION_ID = BSubS.ID
							INNER JOIN b_iblock_section BS ON (BSubS.IBLOCK_ID=BS.IBLOCK_ID
								AND BSubS.LEFT_MARGIN>=BS.LEFT_MARGIN
								AND BSubS.RIGHT_MARGIN<=BS.RIGHT_MARGIN)
							WHERE ((BS.ID IN (".(is_array($value) ? implode($value, ",") : $value)."))))");
					}
					else
					{
						$parameters["filter"]["IBLOCK_SECTION_ID"] = $value;
					}
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

		$parameters["runtime"][] = new Entity\ReferenceField(
			'BSTEMP',
			'Bitrix\Iblock\Section',
			array('=ref.IBLOCK_ID' => 'this.IBLOCK_ID'),
			array('join_type' => 'INNER')
		);//INNER JOIN b_iblock_section BSTEMP ON BSTEMP.IBLOCK_ID = b_iblock_section.IBLOCK_ID

		$parameters["runtime"][] = new Entity\ReferenceField(
			'BSE',
			'Bitrix\Iblock\SectionElementTable',
			array('=ref.IBLOCK_SECTION_ID' => new \Bitrix\Main\DB\SqlExpression('`iblock_section_bstemp`.`ID`')),
			array('join_type' => 'LEFT')
		);//LEFT JOIN b_iblock_section_element BSE ON BSE.IBLOCK_SECTION_ID=BSTEMP.ID 

		$parameters["runtime"][] = new Entity\ReferenceField(
			'BE',
			'Bitrix\Iblock\ElementTable',
			array(
				'=ref.ID' => new \Bitrix\Main\DB\SqlExpression('`iblock_section_bse`.`IBLOCK_ELEMENT_ID`'),
				'=ref.IBLOCK_ID' => new \Bitrix\Main\DB\SqlExpression('`iblock_section`.`IBLOCK_ID`'),
			),
			array('join_type' => 'LEFT')
		);//LEFT JOIN b_iblock_element BE ON (BSE.IBLOCK_ELEMENT_ID=BE.ID
			//AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
			//AND BE.IBLOCK_ID = b_iblock_section.IBLOCK_ID

		$parameters["filter"][] = array(
			"LOGIC" => "AND",
			"IBLOCK_ID" => new \Bitrix\Main\DB\SqlExpression('`iblock_section_bstemp`.`IBLOCK_ID`'),
			"<=LEFT_MARGIN" => new \Bitrix\Main\DB\SqlExpression('`iblock_section_bstemp`.`LEFT_MARGIN`'),
			">=RIGHT_MARGIN" => new \Bitrix\Main\DB\SqlExpression('`iblock_section_bstemp`.`RIGHT_MARGIN`'),
		);//BSTEMP.IBLOCK_ID = iblock_section.IBLOCK_ID
			//AND BSTEMP.LEFT_MARGIN >= iblock_section.LEFT_MARGIN
			//AND BSTEMP.RIGHT_MARGIN <= iblock_section.RIGHT_MARGIN

		return Iblock\SectionTable::getList($parameters);
	}
}
