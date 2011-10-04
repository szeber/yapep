{strip}
{include file="yapep:admin/control/Table.tpl"}
<filters>
{foreach from=$filters item=value key=name}
	<filter name="{$name|escape:'html':'UTF-8'}">{$value|escape:'html':'UTF-8'}</filter>
{/foreach}
</filters>
<allowedTypes>
{foreach from=$allowedTypes item=label key=name}
	<type value="{$name|escape:'html':'UTF-8'}">{$label|escape:'html':'UTF-8'}</type>
{/foreach}
</allowedTypes>
{/strip}