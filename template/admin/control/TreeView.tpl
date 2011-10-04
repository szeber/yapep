{strip}
<options>
{foreach from=$options item=item key=key}
	<option name="{$key|escape:'html':'UTF-8'}" value="{$item|escape:'html':'UTF-8'}" />
{/foreach}
</options>
<tree>
	<node>
		<name>{t}Root directory{/t}</name>
		<link />
		<isOpen>1</isOpen>
		<icon>/images/admin/rootdir.gif</icon>
		<iconAct>/images/admin/rootdir.gif</iconAct>
		<subTree>{include file="yapep:admin/control/TreeView/subtree.tpl" subtree=$tree}</subTree>
	</node>
</tree>
<listeners>
{foreach from=$listeners item=item}
	<listener>{$item}</listener>
{/foreach}
</listeners>
{/strip}
