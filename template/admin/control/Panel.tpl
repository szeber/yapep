{strip}
{if $valuesOnly}
<data>
	{include file="yapep:admin/control/Panel/data.tpl" values=$values}
</data>
{else}
<options>
{foreach from=$options item=value key=name}
	<option name="{$name|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" />
{/foreach}
</options>
<controls>
{foreach from=$controls item=value key=name}
	<control name="{$name|escape:'html':'UTF-8'}" type="{$CONTROL->getControlNameFromControl($value)|escape:'html':'UTF-8'}">{$value->getXml()}</control>
{/foreach}
</controls>
{/if}
{/strip}
