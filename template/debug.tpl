<script type="text/javascript">
//<![CDATA[
{literal}
function toggleDiv(divName, documentElement, containerElement) {
	var divElement=documentElement.getElementById(divName);
	if (divElement.style.display=='') {
		divElement.style.display='none';
		containerElement.getElementsByTagName('div')[0].innerHTML='+';
	} else {
		divElement.style.display='';
		containerElement.getElementsByTagName('div')[0].innerHTML='-';
	}
}
{/literal}

debugWindow = window.open('', 'debugWindow', 'width=800, height=600, resizable=yes, scrollbars=yes');
var debugString='';
debugWindow.document.write('<html><head><title>DEBUG INFORMATION</title><link rel="stylesheet" type="text/css" media="screen" href="/debug.css" /></head><body><h1>Debug information</h1>');
debugWindow.document.write({strip}'
<div id="debugInfo">
	<h2 onclick="toggleDiv(\'errors\', document, this)"><div class="toggler">{if count($errors)}-{else}+{/if}</div>Errors</h2>
	<div id="errors" class="section" {if !count($errors)} style="display: none;"{/if}>{foreach from=$errors item=item}<p>{$item|escape:'javascript'}</p>{/foreach}</div>
	<h2 onclick="toggleDiv(\'queries\', document, this)"><div class="toggler">+</div>Framework queries</h2>
	<div id="queries" class="section" style="display: none;">'{/strip});
	{foreach from=$systemQueries item=query}
debugWindow.document.write({strip}'
		{if $query->getCacheHit()}
		<p><strong class="cachedQuery">Cached query:</strong><br />
		{else}
		<p><strong>Query:</strong><br />
		{/if}
		{$query->getFormattedQuery(true)|escape:'javascript'}</p>
		{if count($query->getParams())}
		<p><strong>Parameters:</strong>{$query->getParams()|@debug_print_var|escape:'javascript'}</p>
		{/if}
		<p><strong>Result:</strong> {$query->getSuccess()}</p>
		<p><strong>Execution time: </strong>{$query->getTime()} ms</p>
		{if $query->getSuccess(true)}
			{if $query->getRows() gt -1} 
		<p><strong>Rows:</strong> {$query->getRows()|escape:'javascript'}</p>
			{/if}
			{if $query->getErrorMessage()}
		<p><strong>Info:</strong> {$query->getErrorMessage()|escape:'javascript'}</p>
			{/if}
		{else}
		<p style="color: #f00;"><strong>Error message: </strong> {$query->getErrorMessage()|escape:'javascript'}</p>
		{/if}'{/strip});
	{/foreach}
debugWindow.document.write({strip}'
	</div>
	<h2 onclick="toggleDiv(\'includes\', document, this)"><div class="toggler">+</div>Included files</h2>
	<div id="includes" class="section" style="display: none;">
	{foreach from=$includedFiles item=file}
		<p>{$file}</p>
	{/foreach}
	</div>
	<h2 onclick="toggleDiv(\'modules\', document, this)"><div class="toggler">+</div>Module information</h2>
	<div id="modules" class="section" style="display: none;">'{/strip});
	{foreach from=$moduleDebug item=item name=moduleDebug}
debugWindow.document.write({strip}'
	<h3 onclick="toggleDiv(\'module_{$smarty.foreach.moduleDebug.index}\', document, this)"><div class="toggler">+</div><div class="moduleTitle">{$item.info.name|escape:'javascript'} ({$item.info.description|escape:'javascript'}){if $item.cached}<span class="cachedModule">Cached</span>{/if}</div><br clear="all" /></h3>
	<div class="moduleInfo" id="module_{$smarty.foreach.moduleDebug.index}" style="display:none;">
	<h4>Module info</h4>
	<div class="section">
		<table>
		{foreach from=$item.info key=infoKey item=info}
		<tr>
			<td>{$infoKey|escape:'javascript'|escape:'html'}</td>
			<td>{$info|@debug_print_var|escape:'javascript'}</td>
		</tr>
		{/foreach}
		</table>
	</div>
	<h4>Module args</h4>
	<div class="section">
		<table>
		{foreach from=$item.args key=argKey item=arg}
		<tr>
			<td>{$argKey|escape:'javascript'|escape:'html'}</td>
			<td>{$arg|@debug_print_var|escape:'javascript'}</td>
		</tr>
		{/foreach}
		</table>
	</div>
	<h4>Smarty variables</h4>
	<div class="section">
		<table>
		{foreach from=$item.smarty key=smartyvarKey item=smartyvar}
		<tr>
			<td>{$smartyvarKey|escape:'javascript'|escape:'html'}</td>
			<td>{$smartyvar|@debug_print_var|escape:'javascript'}</td>
		</tr>
		{/foreach}
		</table>
	</div>
	<h4>Queries</h4>
	<div class="section">
		{foreach from=$item.queries item=query}
			{if $query->getCacheHit()}
			<p><strong class="cachedQuery">Cached query:</strong><br />
			{else}
			<p><strong>Query:</strong><br />
			{/if}
			{$query->getFormattedQuery(true)|escape:'javascript'}</p>
			{if count($query->getParams())}
			<p><strong>Parameters:</strong>
				{foreach from=$query->getParams() item=queryParam key=paramKey}
			<br />&quot;{$paramKey|escape:'javascript'}&quot; =&gt; &quot;{$queryParam|escape:'javascript'}&quot;
				{/foreach}
			</p>
			{/if}
			<p><strong>Result:</strong> {$query->getSuccess()}</p>
			<p><strong>Execution time: </strong>{$query->getTime()} ms</p>
			{if $query->getSuccess(true)}
				{if $query->getRows() gt -1}
			<p><strong>Rows:</strong> {$query->getRows()|escape:'javascript'}</p>
				{/if}
				{if $query->getErrorMessage()}
			<p><strong>Info:</strong> {$query->getErrorMessage()|escape:'javascript'}</p>
				{/if}
			{else}
			<p style="color: #f00;"><strong>Error message: </strong> {$query->getErrorMessage()|escape:'javascript'}</p>
			{/if}
		{/foreach}
	</div>
	</div>'{/strip});
	{/foreach}
debugWindow.document.write({strip}'
</div>
<p>Page loaded in {$loadTime*1000|string_format:"%d"} ms.</p>
<p>{$queryInfo.count} queries run ({$queryInfo.cacheCount} cached) in {$queryInfo.timeFormat} ms.</p>
<p>Average query execution time: {$queryInfo.avgTimeFormat} ms.</p>
<p>IP: {$ipAddr}</p>
<p>Peak memory usage: {$peakMem/1024} KiB</p>
'{/strip});
debugWindow.document.write('</body>');
debugWindow.document.close();
debugWindow.toggleDiv=toggleDiv;
//]]>
</script>
