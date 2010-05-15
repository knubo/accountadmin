<!-- BEGIN PAGE FOOTER -->
<div id="footer">
<?php
	//page generation start
	global $tstart;
	echo $this->MakeMenu('options_menu');
	$wikka_patch_level = ($this->GetWikkaPatchLevel() == '0') ? '' : '-p'.$this->GetWikkaPatchLevel();
?>
</div>
<!-- END PAGE FOOTER -->
<!-- BEGIN SYSTEM INFO -->
<div id="smallprint">
<?php
echo $this->Link('http://validator.w3.org/check/referer', '', 'Valid XHTML 1.0 Transitional');
?> ::
<?php
echo $this->Link('http://jigsaw.w3.org/css-validator/check/referer', '', 'Valid CSS');
?> ::
Powered by <?php echo $this->Link('http://wikkawiki.org/', '', 'WikkaWiki ' . ($this->IsAdmin() ? $this->GetWakkaVersion() . $wikka_patch_level : "")); ?>
</div>
<!-- END SYSTEM INFO -->
<?php
if ($this->GetConfigValue('sql_debugging'))
{
	echo '<div class="smallprint"><strong>Query log:</strong><br />'."\n";
	foreach ($this->queryLog as $query)
	{
		echo $query['query'].' ('.$query['time'].')<br />'."\n";
	}
	echo '</div>'."\n";
}
echo '<!--'.sprintf(PAGE_GENERATION_TIME, $this->microTimeDiff($tstart)).'-->'."\n";
?>
</div>
<script type="text/javascript">
var c_name = "wikitoggle";
var toggled = 0;

var c_start=document.cookie.indexOf(c_name + "=");
  if (c_start!=-1) {
	  c_start=c_start + c_name.length+1;
	  var c_end=document.cookie.indexOf(";",c_start);
	  if (c_end==-1) c_end=document.cookie.length;

	  toggled = unescape(document.cookie.substring(c_start,c_end));
  }

  if(toggled == 0) {
	$('#main_menu').toggle();
	$('#options_menu').toggle();
  }
</script>

<!-- END PAGE WRAPPER -->
</body>
</html>
