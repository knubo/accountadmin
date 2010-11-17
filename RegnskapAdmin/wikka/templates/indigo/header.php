<?php


//init
$message = $this->GetRedirectMessage();
$user = $this->GetUser();
$site_base = $this->GetConfigValue("base_url");
//UniversalEditButton (http://universaleditbutton.org/Universal_Edit_Button) #779
$ueb = '';
if ($this->GetMethod() != 'edit' && $this->HasAccess("write", $this->page["tag"])) {
	$ueb .= '	<link rel="alternate" type="application/x-wiki" title="' . sprintf('Click to edit %s', $this->page["tag"]) . '" href="' . $this->Href('edit', $this->page["tag"]) . '"/>' . "\n";
}
if (substr_count($site_base, 'wikka.php?wakka=') > 0)
	$site_base = substr($site_base, 0, -16);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $this->GetWakkaName().": ".$this->PageTitle(); ?></title>
	<base href="<?php echo $site_base ?>" />
<?php if ($this->GetMethod() != 'show' || $this->page["latest"] == 'N' || $this->page["tag"] == 'SandBox') echo "<meta name=\"robots\" content=\"noindex, nofollow, noarchive\" />\n"; ?>
	<meta name="generator" content="WikkaWiki" />
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="keywords" content="fritt gratis regnskapssystem regnskapsprogram nettbasert" />
	<meta name="google-site-verification" content="uXtHi61lVDRPWyyQ6AoCD8p5CRc_-F1QklvYTsntCC8" />
	<meta name="description" content="<?php echo $this->htmlspecialchars_ent($this->GetConfigValue("meta_description")) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->GetThemePath('/') ?>/css/kubrick.css?<?php echo $this->htmlspecialchars_ent($this->GetConfigValue('stylesheet_hash')) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->GetThemePath('/') ?>/css/indigo.css?<?php echo $this->htmlspecialchars_ent($this->GetConfigValue('stylesheet_hash')) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->GetThemePath('/') ?>/css/print.css" media="print" /> 
	<link rel="icon" href="<?php echo $this->GetThemePath('/') ?>/images/favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo $this->GetThemePath('/') ?>/images/favicon.ico" type="image/x-icon" />
	<script type="text/javascript" src="templates/jquery-1.4.2.min.js"></script>
<?php


if ($this->GetMethod() != 'edit') {
	$rsslink = '	<link rel="alternate" type="application/rss+xml" title="' . $this->GetWakkaName() . ': revisions for ' . $this->tag . ' (RSS)" href="' . $this->Href('revisions.xml', $this->tag) . '" />' . "\n";
	$rsslink .= '	<link rel="alternate" type="application/rss+xml" title="' . $this->GetWakkaName() . ': recently edited pages (RSS)" href="' . $this->Href('recentchanges.xml', $this->tag) . '" />' . "\n";
	echo $rsslink;
}
if (isset ($this->additional_headers) && is_array($this->additional_headers) && count($this->additional_headers)) {
	foreach ($this->additional_headers as $additional_headers) {
		echo $additional_headers;
	}
}
//UniversalEditButton
echo $ueb;
?>
</head>
<body>

<!-- BEGIN PAGE WRAPPER -->
<div id="page" class="container">
<?php


//display system messages
if (isset ($message) && strlen($message) > 0) {
	echo '<div class="success">' . $message . '</div>';
}
?>

	<div class="header">
		
		<div class="title">
			<h1>Fritt Regnskap</h1>
		</div>

		<div class="navigation">
			<a href="<?=$this->href('',  $this->config['root_page'], '')?>">Hjem</a>
<?php
$installs = array();
if ($user) {
    $installs = $this->LoadAll("select * from installations where wikilogin = '" . mysql_real_escape_string($user["name"]) . "'");
	$c = count($installs);

	if ($c == 1) {
		echo '<a id="mittregnskap">Mitt Regnskap</a>';
	} else if($c > 1) {
		echo '<a id="mittregnskap">Mine Regnskap</a>';		
	}
}
?>
			<a href="<?=$this->href('',  'DokumentasjonFrittRegnskap', '')?>">Dokumentasjon</a>
			<a href="<?=$this->href('',  'SporsmalOgSvar', '')?>">Sp&oslash;rsm&aring;l og svar</a>
			<a href="<?=$this->href('',  'RegnskapTips', '')?>">Regnskapstips</a>
			<a href="<?=$this->href('',  'UserSettings', '')?>"><?= $this->getUser() ? "Min Profil" : "Logg inn"?></a>
			<div class="clearer"><span></span></div>
		</div>

  		<div id="installs" class="installs" style="display:none;">
           <ul>
           <?php
           	foreach($installs as $one) {
           		echo '<li><a class="whiteglow" id="'.$one["hostprefix"].'" target="regnskap" href="http://'.$one["hostprefix"].'.frittregnskap.no/prg/AccountingGWT.html">'.$one["hostprefix"].'.frittregnskap.no</a>';
           	}
           	?>
           </ul>
        </div>

        <script type="text/javascript" src="<?php echo $this->GetThemePath('/') ?>/js/installs.js"></script>

	</div>

	<div class="main">
		
		<div class="content">
