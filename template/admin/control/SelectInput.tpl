{strip}
<options>
{foreach from=$options item=item key=key}
	<option name="{$key|escape:'html':'UTF-8'}" value="{$item|escape:'html':'UTF-8'}" />
{/foreach}
</options>
<label>{$label}</label>
<description>{$description}</description>
<value>{$value}</value>
<valueOptions>
{foreach from=$valueOptions item=value key=name}
	<option value="{$name|escape:'html':'UTF-8'}">{$value|escape:'html':'UTF-8'}</option>
{/foreach}
</valueOptions>
{/strip}
