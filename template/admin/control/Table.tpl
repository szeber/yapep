{strip}
<options>
{foreach from=$options item=value key=name}
	<option name="{$name|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" />
{/foreach}
</options>
<headers>
{foreach from=$headers item=label key=name}
	<header name="{$name|escape:'html':'UTF-8'}">{$label|escape:'html':'UTF-8'}</header>
{/foreach}
</headers>
<table>
{foreach from=$tableData item=row name=row}
	<row number="{$smarty.foreach.row.index}" {foreach from=$row.attrs key=name item=value} {$name|escape:'html':'UTF-8'}="{$value|escape:'html':'UTF-8'}"{/foreach}>
	{foreach from=$row.data key=name item=value}
		<cell column="{$name|escape:'html':'UTF-8'}">{$value|escape:'html':'UTF-8'}</cell>
	{/foreach}
	</row>
{/foreach}
</table>
{/strip}