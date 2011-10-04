<?xml version="1.0" encoding="UTF-8"?>
{strip}
<admin{foreach from=$options key=optionname item=optionvalue} {$optionname|escape:'html':'UTF-8'}="{$optionvalue|escape:'html':'UTF-8'}"{/foreach}>
{if count($warnings)}
	<warnings>
	{foreach from=$warnings item=warning}
		<warning>{$warning|escape:'html'}</warning>
	{/foreach}
	</warnings>
{/if}
	{$adminContent}
</admin>
{/strip}