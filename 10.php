<?
$arParams["NAV_ON_PAGE"] = intval($arParams["NAV_ON_PAGE"]);
$arParams["NAV_ON_PAGE"] = 5;
$arParams["NAV_ON_PAGE"] = $arParams["NAV_ON_PAGE"] > 0 ? $arParams["NAV_ON_PAGE"] : 10;

$nav = new \Bitrix\Main\UI\PageNavigation("page");
$nav->allowAllRecords(true)
    ->setPageSize($arParams["NAV_ON_PAGE"])
    ->initFromUri();

$result = Finance\ReportDepartments::getElements(
    array(
        "select" => array("ID", "NAME", "SALE", "PURCHASE", "PROFIT", "IBLOCK_SECTION_ID"),
        "filter" => $arFilter,
        "count_total" => true,
        "offset" => $nav->getOffset(),
        "limit" => $nav->getLimit(),
    )
);

$nav->setRecordCount($result->getCount());

while($fields = $result->fetch())
    $arElements[] = $fields;

ob_start();
$APPLICATION->IncludeComponent(
    "bitrix:main.pagenavigation",
    "",
    array(
        "NAV_OBJECT" => $nav,
        //"SEF_MODE" => "Y",
    ),
    null,
    array('HIDE_ICONS' => 'Y')
);
$arResult["NAV_STRING"] = ob_get_contents();
ob_end_clean();
?>