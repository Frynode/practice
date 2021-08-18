<?
function ShowCondTitle()
{
	global $APPLICATION;
	$title = $APPLICATION->titleNew;
	$APPLICATION->SetTitle($title);

	$APPLICATION->SetPageProperty('title', $title);
	return $title;
}
