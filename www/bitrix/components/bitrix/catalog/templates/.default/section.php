<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

$this->setFrameMode(true);
$this->addExternalCss("/bitrix/css/main/bootstrap.css");

if (!isset($arParams['FILTER_VIEW_MODE']) || (string)$arParams['FILTER_VIEW_MODE'] == '')
	$arParams['FILTER_VIEW_MODE'] = 'VERTICAL';
$arParams['USE_FILTER'] = (isset($arParams['USE_FILTER']) && $arParams['USE_FILTER'] == 'Y' ? 'Y' : 'N');

$isVerticalFilter = ('Y' == $arParams['USE_FILTER'] && $arParams["FILTER_VIEW_MODE"] == "VERTICAL");
$isSidebar = ($arParams["SIDEBAR_SECTION_SHOW"] == "Y" && isset($arParams["SIDEBAR_PATH"]) && !empty($arParams["SIDEBAR_PATH"]));
$isFilter = ($arParams['USE_FILTER'] == 'Y');

if ($isFilter)
{
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
	);
	if (0 < intval($arResult["VARIABLES"]["SECTION_ID"]))
		$arFilter["ID"] = $arResult["VARIABLES"]["SECTION_ID"];
	elseif ('' != $arResult["VARIABLES"]["SECTION_CODE"])
		$arFilter["=CODE"] = $arResult["VARIABLES"]["SECTION_CODE"];

	$obCache = new CPHPCache();
	if ($obCache->InitCache(36000, serialize($arFilter), "/iblock/catalog"))
	{
		$arCurSection = $obCache->GetVars();
	}
	elseif ($obCache->StartDataCache())
	{
		$arCurSection = array();
		if (Loader::includeModule("iblock"))
		{
			$dbRes = CIBlockSection::GetList(array(), $arFilter, false, array("ID"));

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				global $CACHE_MANAGER;
				$CACHE_MANAGER->StartTagCache("/iblock/catalog");

				if ($arCurSection = $dbRes->Fetch())
					$CACHE_MANAGER->RegisterTag("iblock_id_".$arParams["IBLOCK_ID"]);

				$CACHE_MANAGER->EndTagCache();
			}
			else
			{
				if(!$arCurSection = $dbRes->Fetch())
					$arCurSection = array();
			}
		}
		$obCache->EndDataCache($arCurSection);
	}
	if (!isset($arCurSection))
		$arCurSection = array();
}

?>


<?$productQuery=CIBlockElement::getList(
	Array('id' => 'asc'),
	Array
	(
		'IBLOCK_CODE'=>'tagged_sect',
		"SECTION_CODE" => $arResult['VARIABLES']['SECTION_CODE']
	),
	false,
	false,
	Array
	(
		'ID',
		'IBLOCK_ID',
		'NAME',
		'PROPERTY_TAGGED_FILTER_URL',
		'PROPERTY_TAGGED_H1',
		'PROPERTY_TAGGED_TITLE',
		'PROPERTY_TAGGED_DESCRIPTION',
		'PROPERTY_TAGGED_TEXT'
	)
);
$protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
while($product=$productQuery->fetch()){
	if (strpos($_SERVER['REQUEST_URI'], $product['PROPERTY_TAGGED_FILTER_URL_VALUE'])){
		$metaTitle = $product['PROPERTY_TAGGED_TITLE_VALUE'];
		$metaDescr = $product['PROPERTY_TAGGED_DESCRIPTION_VALUE'];
		$title = $product['PROPERTY_TAGGED_H1_VALUE'];
		$text = $product['PROPERTY_TAGGED_TEXT_VALUE'];
		$APPLICATION->newTitle = $metaTitle;
		$APPLICATION->newDescr = $metaDescr;

	}
	$product['PROPERTY_TAGGED_FILTER_URL_VALUE'] =
		$protocol.
		'://'.
		$_SERVER['HTTP_HOST'].
		'/catalog/'.
		$arResult['VARIABLES']['SECTION_CODE'].
		'/filter/'.
		$product['PROPERTY_TAGGED_FILTER_URL_VALUE'].
		'/apply/';
	?>
	<a href="<?=$product['PROPERTY_TAGGED_FILTER_URL_VALUE']?>"><?=$product['NAME']?></a><br>
	<?
}
?>
<div class="row">
	<div class="col-lg-12" id="navigation">
		<?$APPLICATION->IncludeComponent("bitrix:breadcrumb", "", array(
				"START_FROM" => "0",
				"PATH" => "",
				"SITE_ID" => "-"
			),
			false,
			Array('HIDE_ICONS' => 'Y')
		);?>
	</div>
</div>
<h1 class="bx-title dbg_title" id="pagetitle"><?echo (!$title) ? $APPLICATION->ShowTitle(false) : $title;?></h1>

<div class="row">
<?
if ($isVerticalFilter)
	include($_SERVER["DOCUMENT_ROOT"]."/".$this->GetFolder()."/section_vertical.php");
else
	include($_SERVER["DOCUMENT_ROOT"]."/".$this->GetFolder()."/section_horizontal.php");
?>
</div>
<?
if ($text)
	echo $text;
?>
