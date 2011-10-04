{strip}
<options>
{foreach from=$options item=item key=key}
	<option name="{$key|escape:'html':'UTF-8'}" value="{$item|escape:'html':'UTF-8'}" />
{/foreach}
</options>
<label>{$label}</label>
<description>{$description}</description>
{/strip}
