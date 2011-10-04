{strip}
<options>
{foreach from=$options item=value key=name}
	<option name="{$name|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" />
{/foreach}
</options>
<boxes>
{foreach from=$boxes item=box}
	<box name="{$box.name|escape:'html':'UTF-8'}" module="{$box.module|escape:'html':'UTF-8'}" id="{$box.id|escape:'html':'UTF-8'}" active="{$box.active|escape:'html':'UTF-8'}" inherited="{$box.inherited|escape:'html':'UTF-8'}" />
{/foreach}
</boxes>
{/strip}