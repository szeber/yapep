{strip}
	{foreach from=$values key=inputName item=input}
	{if $nonObject}
		{assign var='inputValue' value=$input}
	{else}
		{assign var='inputValue' value=$input->getValue()}
	{/if}
	<value name="{$inputName|escape:'html':'UTF-8'}"{if $inputErrors.$inputName} error="{$inputErrors.$inputName|escape:'html':'UTF-8'}"{/if}{if !$nonObject && $input->getBoxMode()} isInherited="{$input->getBoxIsInherited()|string_format:'%d'}" useInherited="{$input->getBoxUseInherited()|string_format:'%d'}" allowVariable="{$input->getBoxAllowVariable()|string_format:'%d'}" isVariable="{$input->getBoxIsVariable()|string_format:'%d'}"{/if}>
		{if is_array($inputValue)}
			{include file="yapep:admin/control/Panel/data.tpl" values=$inputValue nonObject='1'}
		{else}
			{$inputValue|escape:'html':'UTF-8'}
		{/if}
	</value>
	{/foreach}
{/strip}