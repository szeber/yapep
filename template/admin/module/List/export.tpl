<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>{$folder.name}</title>
{literal}
<style type="text/css">
td, th {
	border: 1px solid #ccc;
}
</style>
{/literal}
</head>
<body>
<table>
	<tr>
{foreach from=$heads item=head name=head}
		<th>{t}{$head|strip_tags|escape:'html'}{/t}</th>
{/foreach}
	</tr>
{foreach from=$table item=row name=table}
	<tr>
	{foreach from=$heads item=head key=headKey}
		<td>{$row.data.$headKey|strip_tags|escape:'html'}</td>
	{/foreach}
	</tr>
{/foreach}
</table>
</body>
</html>