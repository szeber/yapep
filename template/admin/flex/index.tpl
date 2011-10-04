<!-- saved from url=(0014)about:internet -->

<html>
<head>
<title>YAPEP - Admin</title>
<script type="text/javascript" src="/js/swfobject.js"></script>
{literal}<style type="text/css">
	body { margin:0px; padding:0px; height:100%; width:100%; font-family:Tahoma; font-size:10px; color:#666666; }
	a { text-decoration:none; color:#666666; font-weight:bold; }
	h1 { margin:0px; padding:0px; }
	h2 { margin:0px; padding:0px; }
	div#ImageDiv img { width:100%; }
</style>
{/literal}
</head>
<body>
<iframe id="hiframe" name="hiframe" style="visibility:hidden;display:none"></iframe> 
<form method="post" action="" target="hiframe" id="export_form" style="visibility:hidden;display:none"> 
	<input type="hidden" name="admin_xml" id="export_admin_xml">
</form>
<div id="FlexDiv" style="width:100%; height:100%; clear:none; float:left">Az oldal megtekintéséhez 9.0.28-as Flash Player szükséges</div>

<script type="text/javascript">
	// <![CDATA[

	var RichTextString = '';
	var PlainTextString = '';
	var so = new SWFObject("/swf/YapepAdmin.swf", "adminswf", "100%", "100%", "9.0.28");
{if $smarty.const.DEBUGGING}
	so.addParam("flashvars", "debugMode=1");
{/if}
	so.write("FlexDiv");
{literal}
	function exportList(xml,postUrl){
		document.getElementById('export_admin_xml').value=xml;
		document.getElementById('export_form').action=postUrl;	
		document.getElementById('export_form').submit();
	}
	function ShowEditor(richstr, name)
	{
		RichTextString = richstr;
		RichTextName = name;
		window.open("/js/WYSIWYG.html", "WYSIWYGEditor_"+name, "width=960,height=704,toolbars=yes,resizeable=yes,resize=yes")
	}
	
	function SaveWYSIWYG(richstr, name) 
	{
		RichTextString = richstr;
		PlainTextString = richstr.replace(/\n/g, "").replace(/<br[^>]*>/g, "\n").replace(/<[^>]*>/g, "");
        var x = adminswf.saveWYSIWYG(RichTextString, PlainTextString, name);
    }
{/literal}
	
</script>	
</body>
</html>