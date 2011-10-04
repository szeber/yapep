{strip}
{foreach from=$tree item=treeItem}
<node>
	<name>{$treeItem.name}</name>
	<isOpen>{if $treeItem.isOpen}1{else}0{/if}</isOpen>
	<link>{$treeItem.link}</link>
	<icon>{$treeItem.icon}</icon>
	<iconAct>{$treeItem.iconAct}</iconAct>
{if $treeItem.type}
	<type>{$treeItem.type}</type>
{/if}
	<subTree>{include file="yapep:admin/control/TreeView/subtree.tpl" tree=$treeItem.subTree}</subTree>
</node>
{/foreach}
{/strip}