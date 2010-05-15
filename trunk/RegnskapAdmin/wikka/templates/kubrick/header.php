<?php
//init
$message = $this->GetRedirectMessage();
$user = $this->GetUser();
$site_base = $this->GetConfigValue("base_url");
//UniversalEditButton (http://universaleditbutton.org/Universal_Edit_Button) #779
$ueb = '';
if ($this->GetMethod() != 'edit' && $this->HasAccess("write", $this->page["tag"])) 
{
	$ueb .= '	<link rel="alternate" type="application/x-wiki" title="'.sprintf('Click to edit %s', $this->page["tag"]).'" href="'.$this->Href('edit', $this->page["tag"]).'"/>'."\n"; 
}
if ( substr_count($site_base, 'wikka.php?wakka=') > 0 ) $site_base = substr($site_base,0,-16);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $this->GetWakkaName().": ".$this->PageTitle(); ?></title>
	<base href="<?php echo $site_base ?>" />
<?php if ($this->GetMethod() != 'show' || $this->page["latest"] == 'N' || $this->page["tag"] == 'SandBox') echo "<meta name=\"robots\" content=\"noindex, nofollow, noarchive\" />\n"; ?>
	<meta name="generator" content="WikkaWiki" />
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="keywords" content="<?php echo $this->htmlspecialchars_ent($this->GetConfigValue("meta_keywords")) ?>" />
	<meta name="description" content="<?php echo $this->htmlspecialchars_ent($this->GetConfigValue("meta_description")) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->GetThemePath('/') ?>/css/kubrick.css?<?php echo $this->htmlspecialchars_ent($this->GetConfigValue('stylesheet_hash')) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->GetThemePath('/') ?>/css/print.css" media="print" /> 
	<link rel="icon" href="<?php echo $this->GetThemePath('/') ?>/images/favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo $this->GetThemePath('/') ?>/images/favicon.ico" type="image/x-icon" />
	<script type="text/javascript" src="templates/jquery-1.4.2.min.js"></script>
<?php
if ($this->GetMethod() != 'edit')
{
	$rsslink  = '	<link rel="alternate" type="application/rss+xml" title="'.$this->GetWakkaName().': revisions for '.$this->tag.' (RSS)" href="'.$this->Href('revisions.xml', $this->tag).'" />'."\n";
	$rsslink .= '	<link rel="alternate" type="application/rss+xml" title="'.$this->GetWakkaName().': recently edited pages (RSS)" href="'.$this->Href('recentchanges.xml', $this->tag).'" />'."\n";
	echo $rsslink;	
}
if (isset($this->additional_headers) && is_array($this->additional_headers) && count($this->additional_headers)) 
{ 
		foreach ($this->additional_headers as $additional_headers) 
		{ 
				echo $additional_headers; 
		} 
}
//UniversalEditButton
echo $ueb;
?>
</head>
<body>
<!-- BEGIN PAGE WRAPPER -->
<div id="page">
<?php
//display system messages
if (isset($message) && strlen($message)>0)
{
	echo '<div class="success">'.$message.'</div>';
}
?>

<script type="text/javascript">
function toggleWiki(speed) {
	$('#main_menu').toggle(speed);
	$('#options_menu').toggle(speed);

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


<!-- BEGIN PAGE HEADER -->
<div id="header">

<h2><a id="homepage_link" href="<?php echo $this->href('', $this->config['root_page'], ''); ?>"><?php echo $this->config['wakka_name'];?></a><a id="pagetitle" href="<?php echo $this->href('backlinks', '', ''); ?>" title="Display a list of pages linking to <?php echo $this->tag ?>"><?php echo $this->GetPageTag(); ?></a></h2>
</div>
<!-- END PAGE HEADER -->
<!-- BEGIN SIDEBAR -->
<div id="sidebar">
<div style="float:right;font-size:smaller;margin-right:30px;"><a onclick="toggleWiki('slow');">toggle wiki</a></div>
<?php
        //page generation start
        global $tstart;
        echo $this->MakeMenu('main_menu');
        echo $this->MakeMenu('options_menu');
?>
</div>
<!-- END SIDEBAR -->
