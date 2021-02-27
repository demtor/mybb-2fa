<html>
<head>
	<title>{$mybb->settings['bbname']} - {$lang->my2fa_title}</title>
	{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
	<tr>
		{$usercpnav}
		<td valign="top">
            {$forcedGroupNotice}
			{$setupContent}
		</td>
	</tr>
	</table>
	{$footer}
</body>
</html>