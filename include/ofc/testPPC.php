<?

$a = '3d-bar-chart.php';
echo '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
				codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0"
				width="300" height="300" id="chart" align="middle">
			<param name="allowScriptAccess" value="sameDomain"/>
			<param name="movie" value="http://monitor.by/include/ofc/open-flash-chart.swf"/>
			<param name="FlashVars" value="data-file=http://monitor.by/include/ofc/' . $a .  '"/>
			<param name="quality" value="high"/>
			<param name="bgcolor" value="#FFFFFF"/>
			<embed src="http://monitor.by/include/ofc/open-flash-chart.swf" FlashVars="data-file=http://monitor.by/include/ofc/' . $a .  '" quality="high" bgcolor="#FFFFFF"
				   width=300s height=300s name="chart" align="middle" allowScriptAccess="sameDomain"
				   type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"/>
		</object>';
		
//echo urlencode("http://monitor.by/include/ofc2/test.php");		

?>
