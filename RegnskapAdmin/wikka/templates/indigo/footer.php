
		</div>

		<div class="sidenav">
<!-- BEGIN PAGE HEADER -->

<script type="text/javascript">
function toggleWiki(speed) {
	$('#main_menu').toggle(speed);
	$('#options_menu').toggle(speed);
	$('#homepageheader').toggle(speed);

	var c_name = "wikitoggle";
	var toggled = 0;

	var c_start=document.cookie.indexOf(c_name + "=");
	  if (c_start!=-1) {
		  c_start=c_start + c_name.length+1;
		  var c_end=document.cookie.indexOf(";",c_start);
		  if (c_end==-1) c_end=document.cookie.length;

		  toggled = unescape(document.cookie.substring(c_start,c_end));
	  }


	  expiration_date = new Date();
	  expiration_date.setYear(1900 + expiration_date.getYear () + 1);

	  document.cookie=c_name+'='+ (toggled == 0 ? "1" : "0") +'; expires=' + expiration_date.toGMTString();
}
</script>

<div style="float:right;font-size:smaller;"><a onclick="toggleWiki('slow');">toggle wiki</a></div>

<h2 id="homepageheader"><a id="homepage_link" href="<?php echo $this->href('', $this->config['root_page'], ''); ?>"><?php echo $this->config['wakka_name'];?></a> : <a href="<?php echo $this->href('backlinks', '', ''); ?>" title="Display a list of pages linking to <?php echo $this->tag ?>"><?php echo $this->GetPageTag(); ?></a></h2>
<?php echo $this->MakeMenu('main_menu'); ?>

		</div>
	
		<div class="clearer"><span></span></div>

	</div>

</div>

<!-- BEGIN PAGE FOOTER -->
<div id="footer" class="footer">
<?php
	$wikka_patch_level = ($this->GetWikkaPatchLevel() == '0') ? '' : '-p'.$this->GetWikkaPatchLevel();
?>
<!-- END PAGE FOOTER -->
<!-- BEGIN SYSTEM INFO -->
<?php
echo $this->Link('http://validator.w3.org/check/referer', '', 'Valid XHTML 1.0 Transitional');
?> ::
<?php
echo $this->Link('http://jigsaw.w3.org/css-validator/check/referer', '', 'Valid CSS');
?> ::
Powered by <?php echo $this->Link('http://wikkawiki.org/', '', 'WikkaWiki ' . ($this->IsAdmin() ? $this->GetWakkaVersion() . $wikka_patch_level : "")); ?>

 - Template design (modified) by <a href="http://arcsin.se">Arcsin</a>
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
