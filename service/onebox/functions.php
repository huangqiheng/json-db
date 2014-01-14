<?php
require_once '../../admin/functions.php';

function onebox_output($res)
{
	$html  = "<div class=\"onebox-result\">";
	$html .=   "<div class=\"source\">";
	$html .=     "<div class=\"info\">";
	$html .=       "<a href=\"{$res['provider_url']}\" target=\"_blank\">";
	$html .=         "<img class=\"favicon\" src={$res['favicon_url']}>{$res['provider_name']}";
	$html .=       "</a>";
	$html .=     "</div>";
	$html .=   "</div>";
	$html .=   "<div class=\"onebox-result-body\">"; if ($res['image']!='') {
	$html .=     "<a href={$res['ori_url']} target=\"_blank\"><img src={$res['image']} class=\"thumbnail\"></a>";}
	$html .=     "<a href={$res['ori_url']} target=\"_blank\" class=\"onebox-title\">{$res['title']}</a>";
	$html .=     "<div>{$res['description']}</div>";
	$html .=   "</div>";
	$html .=   "<div class=\"clearfix\"></div>";
	$html .= "</div>";
	return $html;
}

