<?php
/**
 * This file is part of Wikka, a PHP wiki engine.
 *
 * It includes the Wakka class, which provides the core functions
 * to run Wikka.
 *
 * @package Wikka
 * @subpackage Libs
 * @version $Id$
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @filesource
 *
 * @author	{@link http://www.mornography.de/ Hendrik Mans}
 * @author	{@link http://wikkawiki.org/JsnX Jason Tourtelotte}
 * @author	{@link http://wikkawiki.org/JavaWoman Marjolein Katsma}
 * @author	{@link http://wikkawiki.org/NilsLindenberg Nils Lindenberg}
 * @author	{@link http://wikkawiki.org/DotMG Mahefa Randimbisoa}
 * @author	{@link http://wikkawiki.org/DarTar Dario Taraborelli}
 * @author	{@link http://wikkawiki.org/BrianKoontz Brian Koontz}
 *
 * @copyright Copyright 2002-2003, Hendrik Mans <hendrik@mans.de>
 * @copyright Copyright 2004-2005, Jason Tourtelotte <wikka-admin@jsnx.com>
 * @copyright Copyright 2006-2009 {@link http://wikkawiki.org/CreditsPage Wikka Development Team}
 */

// Time to live for client-side cookies in seconds (90 days)
if(!defined('PERSISTENT_COOKIE_EXPIRY')) define('PERSISTENT_COOKIE_EXPIRY', 7776000);

// i18n TODO:move to language file
if(!defined('CREATE_THIS_PAGE_LINK_TITLE')) define('CREATE_THIS_PAGE_LINK_TITLE', 'Create this page');
if(!defined('DEFAULT_THEMES_TITLE')) define('DEFAULT_THEMES_TITLE', 'Default themes (%s)'); //%s: number of available themes
if(!defined('CUSTOM_THEMES_TITLE')) define('CUSTOM_THEMES_TITLE', 'Custom themes (%s)'); //%s: number of available themes

/**
 * The Wikka core.
 *
 * This class contains all the core methods used to run Wikka.
 * @name Wakka
 * @package Wikka
 * @subpackage Libs
 *
 */
class Wakka
{
	var $config = array();
	var $dblink;
	var $page;
	var $tag;
	var $queryLog = array();
	var $interWiki = array();
	var $VERSION;
	var $cookies_sent = false;
	var $cookie_expiry = PERSISTENT_COOKIE_EXPIRY; 
	var $wikka_cookie_path;
	var $additional_headers = array();

	/**
	 * Constructor
	 */
	function Wakka($config)
	{
		$this->config = $config;
		$this->dblink = @mysql_connect($this->config["mysql_host"], $this->config["mysql_user"], $this->config["mysql_password"]);
		if ($this->dblink)
		{
			if (!@mysql_select_db($this->config["mysql_database"], $this->dblink))
			{
				@mysql_close($this->dblink);
				$this->dblink = false;
			}
		}
		$this->VERSION = WAKKA_VERSION;
		$this->PATCH_LEVEL = WIKKA_PATCH_LEVEL;
	}

	/**
	 * Database methods
	 */
	function Query($query, $dblink='')
	{
		// init - detect if called from objct or externally
		if ('' == $dblink)
		{
			$dblink = $this->dblink;
			$object = TRUE;
			$start = $this->GetMicroTime();
		}
		else
		{
			$object = FALSE;
		}
		if (!$result = mysql_query($query, $dblink))
		{
			ob_end_clean();
			die("Query failed: ".$query." (".mysql_error().")");
		}
		if ($object && $this->config['sql_debugging'])
		{
			$time = $this->GetMicroTime() - $start;
			$this->queryLog[] = array(
				"query"		=> $query,
				"time"		=> $time);
		}
		return $result;
	}
	function LoadSingle($query) { if ($data = $this->LoadAll($query)) return $data[0]; }
	function LoadAll($query)
	{
		$data = array();
		if ($r = $this->Query($query))
		{
			while ($row = mysql_fetch_assoc($r)) $data[] = $row;
			mysql_free_result($r);
		}
		return $data;
	}
	/**
	 * Generic 'count' query.
	 *
	 * Get a count of the number of records in a given table that would be matched
	 * by the given (optional) WHERE criteria. Only a single table can be queried.
	 *
	 * @author		{@link http://wikkawiki.org/JavaWoman JavaWoman}
	 * @license		http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
	 * @since		Wikka 1.1.6.4
	 * @version		1.1
	 *
	 * @access	public
	 * @uses	Wakka::GetConfigValue()
	 * @uses	Wakka::Query()
	 *
	 * @param	string	$table	required: (logical) table name to query;
	 *							prefix will be automatically added
	 * @param	string	$where	optional: criteria to be specified for a WHERE clause;
	 *							do not include WHERE
	 * @return	integer	number of matches returned by MySQL
	 * @todo	move into a database class.
	 */
	function getCount($table, $where='')							# JW 2005-07-16
	{
		// build query
		$where = ('' != $where) ? ' WHERE '.$where : '';
		$query = "
			SELECT COUNT(*)
			FROM ".$this->GetConfigValue('table_prefix').$table.
			$where;

		// get and return the count as an integer
		$count = (int)mysql_result($this->Query($query),0);
		return $count;
	}
	function CheckMySQLVersion($major, $minor, $subminor)
	{
		$result = @mysql_query('SELECT VERSION() AS version');
		if ($result != FALSE && @mysql_num_rows($result) > 0)
		{
			$row   = mysql_fetch_array($result);
			$match = explode('.', $row['version']);
		}
		else
		{
			$result = @mysql_query('SHOW VARIABLES LIKE \'version\'');
			if ($result != FALSE && @mysql_num_rows($result) > 0) {
				$row   = mysql_fetch_row($result);
				$match = explode('.', $row[1]);
			} else {
				return 0;
			}
		}

		$mysql_major = $match[0];
		$mysql_minor = $match[1];
		$mysql_subminor = $match[2][0].$match[2][1];

		if ($mysql_major > $major) {
			return 1;
		} else {
			if (($mysql_major == $major) && ($mysql_minor >= $minor) && ($mysql_subminor >= $subminor)) {
				return 1;
			} else {
				return 0;
			}
		}
	}

	/**
	 * Misc methods
	 */
	function GetMicroTime() { list($usec, $sec) = explode(" ",microtime()); return ((float)$usec + (float)$sec); }
	function IncludeBuffered($filename, $notfoundText='', $vars='', $path='')
	{
		# TODO: change parameter order, so $path (no default,. it's required)
		# comes after $filename and only $notfoundtext and $vars will actually
		# be optional with a default of ''. MK/2007-03-31

		// check if required parameter $path is supplied (see TODO)
		if ('' != trim($path))
		{
			// build full (relative) path to requested plugin (method/action/formatter)
			$fullfilepath = $this->BuildFullpathFromMultipath($filename, $path);
			// check if requested file (method/action/formatter) actually exists
			if (FALSE===empty($fullfilepath))
			{
				if (is_array($vars))
				{
					// make the parameters also available by name (apart from the array itself):
					// some callers rely on these separate values, so we extract them, too
					// taking care not to overwrite any already-existing variable
					extract($vars, EXTR_SKIP);	# [SEC] EXTR_SKIP avoids collision with existing filenames
				}
				ob_start();
				include($fullfilepath);
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
			}
		}
		if ('' != trim($notfoundText))
		{
			return '<em class="error">'.$this->htmlspecialchars_ent(trim($notfoundText)).'</em>';	# [SEC] make error (including (part of) request) safe to display
		}
		else
		{
			return false;
		}
	}

	/**
	 * Create a unique id for an HTML element.
	 *
	 * Although - given Wikka accepts can use embedded HTML - it cannot be
	 * guaranteed that an id generated by this method is unique it tries its
	 * best to make it unique:
	 * - ids are organized into groups, with the group name used as a prefix
	 * - if an id is specified it is compared with other ids in the same group;
	 *   if an identical id exists within the same group, a sequence suffix is
	 *   added, otherwise the specified id is accepted and recorded as a member
	 *   of the group
	 * - if no id is specified (or an invalid one) an id will be generated, and
	 *   given a sequence suffix if needed
	 *
	 * For headings, it is possible to derive an id from the heading content;
	 * to support this, any embedded whitespace is replaced with underscores
	 * to generate a recognizable id that will remain (mostly) constant even if
	 * new headings are inserted in a page. (This is not done for embedded
	 * HTML.)
	 *
	 * The method supports embedded HTML as well: as long as the formatter
	 * passes each id found in embedded HTML through this method it can take
	 * care that the id is valid and unique.
	 * This works as follows:
	 * - indicate an 'embedded' id with group 'embed'
	 * - NO prefix will be added for this reserved group
	 * - ids will be recorded and checked for uniqueness and validity
	 * - invalid ids are replaced
	 * - already-existing ids in the group are given a sequence suffix
	 * The result is that as long as the already-defined id is valid and
	 * unique, it will be remain unchanged (but recorded to ensure uniqueness
	 * overall).
	 *
	 * @author		{@link http://wikka.jsnx.com/JavaWoman JavaWoman}
	 * @copyright	Copyright � 2005, Marjolein Katsma
	 * @license		http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
	 * @since		Wikka 1.1.6.4
	 * @version		1.0
	 *
	 * @access	public
	 * @uses	ID_LENGTH
	 *
	 * @param	string	$group	required: id group (e.g. form, head); will be
	 *							used as prefix (except for the reserved group
	 *							'embed' to be used for embedded HTML only)
	 * @param	string	$id		optional: id to use; if not specified or
	 *							invalid, an id will be generated; if not
	 *							unique, a sequence number will be appended
	 * @return	string	resulting id
	 */
	function makeId($group,$id='')
	{
		// initializations
		static $aSeq = array();										# group sequences
		static $aIds = array();										# used ids

		// preparation for group
		if (!preg_match('/^[A-Z-a-z]/',$group))						# make sure group starts with a letter
		{
			$group = 'g'.$group;
		}
		if (!isset($aSeq[$group]))
		{
			$aSeq[$group] = 0;
		}
		if (!isset($aIds[$group]))
		{
			$aIds[$group] = array();
		}
		if ('embed' != $group)
		{
			$id = preg_replace('/\s+/','_',trim($id));				# replace any whitespace sequence in $id with a single underscore
		}

		// validation (full for 'embed', characters only for other groups since we'll add a prefix)
		if ('embed' == $group)
		{
			$validId = preg_match('/^[A-Za-z][A-Za-z0-9_:.-]*$/',$id);	# ref: http://www.w3.org/TR/html4/types.html#type-id
		}
		else
		{
			$validId = preg_match('/^[A-Za-z0-9_:.-]*$/',$id);
		}

		// build or generate id
		if ('' == $id || !$validId || in_array($id,$aIds))			# ignore specified id if it is invalid or exists already
		{
			$id = substr(md5($group.$id),0,ID_LENGTH);				# use group and id as basis for generated id
		}
		$idOut = ('embed' == $group) ? $id : $group.'_'.$id;		# add group prefix (unless embedded HTML)
		if (in_array($id,$aIds[$group]))
		{
			$idOut .= '_'.++$aSeq[$group];							# add suffiX to make ID unique
		}

		// result
		$aIds[$group][] = $id;										# keep track of both specified and generated ids (without suffix)
		return $idOut;
	}

	/**
	 * Strip potentially dangerous tags from embedded HTML.
	 *
	 * @param	string $html mandatory: HTML to be secured
	 * @return	string sanitized HTML
	 */
	function ReturnSafeHTML($html)
	{
        $safehtml_classpath =
		$this->GetConfigValue('safehtml_path').DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'safehtml.php';
        require_once $safehtml_classpath;

		// Instantiate the handler
		$safehtml = new safehtml();

		$filtered_output = $safehtml->parse($html);

		return $filtered_output;
	}

	/**
	 * Make sure a (user-provided) URL does use &amp; instead of & and is protected from attacks.
	 *
#	 * Any already-present '&amp;' is first turned into '&'; then htmlspecialchars() is applied so
	 * Any already-present '&amp;' is first turned into '&'; then hsc_secure()
	 * is applied so all ampersands are "escaped" while characters that could be
	 * used to create a script attack (< > or ") are "neutralized" by escaping
	 * them.
	 *
	 * This method should be applied on any user-provided url in actions,
	 * handlers etc.
	 *
	 * Note: hsc_secure() is the secure replacement for PHP's htmlspecialchars().
	 * See #427.
	 *
	 * @author		{@link http://wikkawiki.org/JavaWoman JavaWoman}
	 * @copyright	Copyright � 2004, Marjolein Katsma
	 * @license		http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
	 * @version		1.0
	 *
	 * @access		public
	 * @uses		Wakka::hsc_secure()
	 * @param		string	$url  required: URL to sanitize
	 * @return		string	sanitzied URL
	 */
	function cleanUrl($url)
	{
		#return htmlspecialchars(preg_replace('/&amp;/','&',$url));
		return $this->hsc_secure(preg_replace('/&amp;/','&',$url));
	}

	/**
	 * Wrapper around hsc_secure() which preserves entity references.
	 *
	 * The first two parameters for this function as the same as those for
	 * htmlspecialchars() in PHP: the text to be treated, and an optional
	 * parameter determining how to handle quotes; both these parameters are
	 * passed on to our hsc_secure() replacement for htmlspecialchars().
	 *
	 * Since hsc_secure() does not need a character set parameter, we don't
	 * have that here any more either.
	 *
	 * A third 'doctype' parameter is for local use only and determines how
	 * pre-existing entity references are treated after hsc_secure() has done
	 * its work: numeic entity references are always "unescaped' since they are
	 * valid for both HTML and XML doctypes; for XML the named entity references
	 * for the special characters are unescaped as well, while for for HTML any
	 * named entity reference is unescaped. This parameter is optional and
	 * defaults to HTML.
	 *
	 * The function first applies hsc_secure() to the input string and then
	 * "unescapes" character entity references and numeric character references
	 * (both decimal and hexadecimal).
	 * Entities are recognized also if the ending semicolon is omitted at the
	 * end or before a newline or tag but for consistency the semicolon is
	 * always added in the output where it was omitted.
	 *
	 * Usage note:
	 * Where code should be rendered <em>as code</em> hsc_secure() should be
	 * used directly so that entity references are also rendered as such instead
	 * of as their corresponding characters.
	 *
	 * Documentation note:
	 * It seems the $doctype parameter was added in 1.1.6.2; version should have
	 * been bumped up to 1.1, and the param documented. We'll assume the updated
	 * version was indeed 1.1, and put this one using hsc_secure() at 1.2 (at
	 * the same time updating the 'XML' doctype with apos as named entity).
	 *
	 * @access	public
	 * @since	Wikka 1.1.6.0
	 * @version	1.2
	 *
	 * @uses	Wakka::hsc_secure()
	 * @param	string	$text required: text to be converted
	 * @param	integer	$quote_style optional: quoting style - can be ENT_COMPAT
	 * 			(default, escape only double quotes), ENT_QUOTES (escape both
	 * 			double and single quotes) or ENT_NOQUOTES (don't escape any
	 * 			quotes)
	 * @param	string $doctype 'HTML' (default) or 'XML'; for XML only the XML
	 * 			standard entities are unescaped so we'll have valid XML content
	 * @return	string	converted string with escaped special characted but
	 * 			entity references intact
	 *
	 * @todo	(maybe) recognize valid html entities and only leave those
	 * 			alone, thus transform &error; to &amp;error;
	 * @todo	later - maybe) support full range of situations where (in SGML)
	 * 			a terminating ; may legally be omitted (end, newline and tag are
	 * 			merely the most common ones); such usage is quite rare though
	 * 			and may not be worth the effort
	 */
	function htmlspecialchars_ent($text,$quote_style=ENT_COMPAT,$doctype='HTML')
	{
		// re-establish default if overwritten because of third parameter
		// [ENT_COMPAT] => 2
		// [ENT_QUOTES] => 3
		// [ENT_NOQUOTES] => 0
		if (!in_array($quote_style,array(ENT_COMPAT,ENT_QUOTES,ENT_NOQUOTES))) {
			$quote_style = ENT_COMPAT;
		}

		// define patterns
		$terminator = ';|(?=($|[\n<]|&lt;))';	// semicolon; or end-of-string, newline or tag
		$numdec = '#[0-9]+';					// numeric character reference (decimal)
		$numhex = '#x[0-9a-f]+';				// numeric character reference (hexadecimal)
		if ($doctype == 'XML')					// pure XML allows only named entities for special chars
		{
			// only valid named entities in XML (case-sensitive)
			$named = 'lt|gt|quot|apos|amp';
			$ignore_case = '';
			$entitystring = $named.'|'.$numdec.'|'.$numhex;
		}
		else									// (X)HTML
		{
			$alpha  = '[a-z]+';					// character entity reference TODO $named='eacute|egrave|ccirc|...'
			$ignore_case = 'i';					// names can consist of upper and lower case letters
			$entitystring = $alpha.'|'.$numdec.'|'.$numhex;
		}
		$escaped_entity = '&amp;('.$entitystring.')('.$terminator.')';

		// execute our replacement hsc_secure() function, passing on optional parameters
		$output = $this->hsc_secure($text,$quote_style);

		// "repair" escaped entities
		// modifiers: s = across lines, i = case-insensitive
		$output = preg_replace('/'.$escaped_entity.'/s'.$ignore_case,"&$1;",$output);

		// return output
		return $output;
	}

	/**
	 * Secure replacement for PHP built-in function htmlspecialchars().
	 *
	 * See ticket #427 (http://wush.net/trac/wikka/ticket/427) for the rationale
	 * for this replacement function.
	 *
	 * The INTERFACE for this function is almost the same as that for
	 * htmlspecialchars(), with the same default for quote style; however, there
	 * is no 'charset' parameter. The reason for this is as follows:
	 *
	 * The PHP docs say:
	 * 	"The third argument charset defines character set used in conversion."
	 *
	 * I suspect PHP's htmlspecialchars() is working at the byte-value level and
	 * thus _needs_ to know (or assume) a character set because the special
	 * characters to be replaced could exist at different code points in
	 * different character sets. (If indeed htmlspecialchars() works at
	 * byte-value level that goes some  way towards explaining why the
	 * vulnerability would exist in this function, too, and not only in
	 * htmlentities() which certainly is working at byte-value level.)
	 *
	 * This replacement function however works at character level and should
	 * therefore be "immune" to character set differences - so no charset
	 * parameter is needed or provided. If a third parameter is passed, it will
	 * be silently ignored.
	 *
	 * In the OUTPUT there is a minor difference in that we use '&#39;' instead
	 * of PHP's '&#039;' for a single quote: this provides compatibility with
	 * 	get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES)
	 * (see comment by mikiwoz at yahoo dot co dot uk on
	 * http://php.net/htmlspecialchars); it also matches the entity definition
	 * for XML 1.0
	 * (http://www.w3.org/TR/xhtml1/dtds.html#a_dtd_Special_characters).
	 * Like PHP we use a numeric character reference instead of '&apos;' for the
	 * single quote. For the other special characters we use the named entity
	 * references, as PHP is doing.
	 *
	 * And finally:
	 * The name for this function was basically inspired by waawaamilk (GeSHi),
	 * kindly provided by BenBE (GeSHi), happily acknowledged by WikkaWiki Dev
	 * Team and finally used by JavaWoman. :)
	 *
	 * @author 		{@link http://wikkawiki.org/JavaWoman Marjolein Katsma}
	 *
	 * @since		Wikka 1.1.7
	 * @version		1.0
	 * @license		http://www.gnu.org/copyleft/lgpl.html
	 * 				GNU Lesser General Public License
	 * @copyright	Copyright 2007, {@link http://wikkawiki.org/CreditsPage
	 * 				Wikka Development Team}
	 *
	 * @access	public
	 * @param	string	$string	string to be converted
	 * @param	integer	$quote_style
	 * 			- ENT_COMPAT:   escapes &, <, > and double quote (default)
	 * 			- ENT_NOQUOTES: escapes only &, < and >
	 * 			- ENT_QUOTES:   escapes &, <, >, double and single quotes
	 * @return	string	converted string
	 */
	 function hsc_secure($string, $quote_style=ENT_COMPAT)
	 {
		// init
		$aTransSpecchar = array('&' => '&amp;',
								'"' => '&quot;',
								'<' => '&lt;',
								'>' => '&gt;'
								);			// ENT_COMPAT set
		if (ENT_NOQUOTES == $quote_style)	// don't convert double quotes
		{
			unset($aTransSpecchar['"']);
		}
		elseif (ENT_QUOTES == $quote_style)	// convert single quotes as well
		{
			$aTransSpecchar["'"] = '&#39;';	// (apos) htmlspecialchars() uses '&#039;'
		}

		// return translated string
		return strtr($string,$aTransSpecchar);
	 }

	/**
	 * Get a value provided by user (by get, post or cookie) and sanitize it.
	 * The method is also helpful to disable warning when the value was absent.
	 *
	 * @access	public
	 * @since	Wikka 1.1.7.0
	 * @version	1.0
	 *
	 * @param	string	$varname required: field name on get or post or cookie name
	 * @param	string	$gpc one of get, post, request and cookie. Optional, defaults to request.
	 * @return	string	sanitized value of $_GET[$varname] (or $_POST, $_COOKIE, depending on $gpc)
	 */
	function GetSafeVar($varname, $gpc='get')
	{
		$safe_var = null;
		if ($gpc == 'post')
		{
			$safe_var = isset($_POST[$varname]) ? $_POST[$varname] : null;
		}
		elseif ($gpc == 'get')
		{
			$safe_var = isset($_GET[$varname]) ? $_GET[$varname] : null;
		}
		elseif ($gpc == 'cookie')
		{
			$safe_var = isset($_COOKIE[$varname]) ? $_COOKIE[$varname] : null;
		}
		return ($this->htmlspecialchars_ent($safe_var));
	}

	/**
	 * Highlight a code block with GeSHi.
	 *
	 * The path to GeSHi and the GeSHi language files must be defined in the configuration.
	 *
	 * This implementation fits in with general Wikka behavior; e.g., we use classes and an external
	 * stylesheet to render hilighting.
	 *
	 * Apart from this fixed general behavior, WikiAdmin can configure a few behaviors via the
	 * configuration file:
	 * geshi_header			- wrap code in div (default) or pre
	 * geshi_line_numbers	- disable line numbering, or enable normal or fancy line numbering
	 * geshi_tab_width		- override tab width (default is 8 but 4 is more commonly used in code)
	 *
	 * Limitation: while line numbering is supported, extra GeSHi styling for line numbers is not.
	 * When line numbering is enabled, the end user can "turn it on" by specifying a starting line
	 * number together with the language code in a code block, e.g., (php;260); this number is then
	 * passed as the $start parameter for this method.
	 *
	 * @access	public
	 * @since	wikka 1.1.6.0
	 * @uses	Wakka::config
	 * @uses	GeShi
	 * @todo	support for GeSHi line number styles
	 * @todo	enable error handling
	 *
	 * @param	string	$sourcecode	required: source code to be highlighted
	 * @param	string	$language	required: language spec to select highlighter
	 * @param	integer	$start		optional: start line number; if supplied and >= 1 line numbering
	 * 			will be turned on if it is enabled in the configuration.
	 * @return	string	code block with syntax highlighting classes applied
	 */
	function GeSHi_Highlight($sourcecode, $language, $start=0)
	{
		// create GeSHi object
		include_once($this->config['geshi_path'].'/geshi.php');
		$geshi = new GeSHi($sourcecode, $language, $this->config['geshi_languages_path']);				# create object by reference

		$geshi->enable_classes();								# use classes for hilighting (must be first after creating object)
		$geshi->set_overall_class('code');						# enables using a single stylesheet for multiple code fragments

		// configure user-defined behavior
		$geshi->set_header_type(GESHI_HEADER_DIV);				# set default
		if (isset($this->config['geshi_header']))				# config override
		{
			if ('pre' == $this->config['geshi_header'])
			{
				$geshi->set_header_type(GESHI_HEADER_PRE);
			}
		}
		$geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);		# set default
		if ($start > 0)											# line number > 0 _enables_ numbering
		{
			if (isset($this->config['geshi_line_numbers']))		# effect only if enabled in configuration
			{
				if ('1' == $this->config['geshi_line_numbers'])
				{
					$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
				}
				elseif ('2' == $this->config['geshi_line_numbers'])
				{
					$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
				}
				if ($start > 1)
				{
					$geshi->start_line_numbers_at($start);
				}
			}
		}
		if (isset($this->config['geshi_tab_width']))			# GeSHi override (default is 8)
		{
			$geshi->set_tab_width($this->config['geshi_tab_width']);
		}

		// parse and return highlighted code
		// comments added to make GeSHi-highlighted block visible in code JW/20070220
		return '<!--start GeSHi-->'."\n".$geshi->parse_code()."\n".'<!--end GeSHi-->'."\n";
	}

	/**
	 * Variable-related methods
	 */
	function GetPageTag() { return $this->tag; }
	function GetPageTime() { return $this->page["time"]; }
	function GetMethod() { return $this->method; }
	function GetConfigValue($name) { return (isset($this->config[$name])) ? $this->config[$name] : null; }
	function GetWakkaName() { return $this->GetConfigValue("wakka_name"); }
	function GetWakkaVersion() { return $this->VERSION; }
	function GetWikkaPatchLevel() { return $this->PATCH_LEVEL; }

	/**
	 * Page-related methods
	 */
	function LoadPage($tag, $time = "", $cache = 1) {
		// retrieve from cache
		if (!$time && $cache) {
			$page = isset($this->pageCache[$tag]) ? $this->pageCache[$tag] : null;
			if ($page=="cached_nonexistent_page") return null;
		}
		// load page
		if (!isset($page)) $page = $this->LoadSingle("select * from ".$this->config["table_prefix"]."pages where tag = '".mysql_real_escape_string($tag)."' ".($time ? "and time = '".mysql_real_escape_string($time)."'" : "and latest = 'Y'")." limit 1");
		// cache result
		if ($page && !$time) {
			$this->pageCache[$page["tag"]] = $page;
		} elseif (!$page) {
			$this->pageCache[$tag] = "cached_nonexistent_page";
		}
		return $page;
	}
	function IsLatestPage() {
		return $this->latest;
	}
	function GetCachedPage($tag) { return (isset($this->pageCache[$tag])) ? $this->pageCache[$tag] : null; }
	function CachePage($page) { $this->pageCache[$page["tag"]] = $page; }
	function SetPage($page) { $this->page = $page; if ($this->page["tag"]) $this->tag = $this->page["tag"]; }
	function LoadPageById($id) { return $this->LoadSingle("select * from ".$this->config["table_prefix"]."pages where id = '".mysql_real_escape_string($id)."' limit 1"); }
	function LoadRevisions($page) { return $this->LoadAll("select * from ".$this->config["table_prefix"]."pages where tag = '".mysql_real_escape_string($page)."' order by id desc"); }
	function LoadPagesLinkingTo($tag) { return $this->LoadAll("select from_tag as tag from ".$this->config["table_prefix"]."links where to_tag = '".mysql_real_escape_string($tag)."' order by tag"); }
	function LoadRecentlyChanged()
	{
		if ($pages = $this->LoadAll("select * from ".$this->config["table_prefix"]."pages where latest = 'Y' order by id desc"))
		{
			foreach ($pages as $page)
			{
				$this->CachePage($page);
			}
			return $pages;
		}
	}
	function LoadWantedPages() { return $this->LoadAll("select distinct ".$this->config["table_prefix"]."links.to_tag as tag,count(".$this->config["table_prefix"]."links.from_tag) as count from ".$this->config["table_prefix"]."links left join ".$this->config["table_prefix"]."pages on ".$this->config["table_prefix"]."links.to_tag = ".$this->config["table_prefix"]."pages.tag where ".$this->config["table_prefix"]."pages.tag is NULL group by ".$this->config["table_prefix"]."links.to_tag order by count desc"); }
	function IsWantedPage($tag)
	{
		if ($pages = $this->LoadWantedPages())
		{
			foreach ($pages as $page)
			{
				if ($page["tag"] == $tag) return true;
			}
		}
		return false;
	}
	function LoadOrphanedPages() { return $this->LoadAll("select distinct tag from ".$this->config["table_prefix"]."pages left join ".$this->config["table_prefix"]."links on ".$this->config["table_prefix"]."pages.tag = ".$this->config["table_prefix"]."links.to_tag where ".$this->config["table_prefix"]."links.to_tag is NULL order by tag"); }
	function LoadPageTitles() { return $this->LoadAll("select distinct tag from ".$this->config["table_prefix"]."pages order by tag"); }
	function LoadAllPages() { return $this->LoadAll("select * from ".$this->config["table_prefix"]."pages where latest = 'Y' order by tag"); }
	function FullTextSearch($phrase, $caseSensitive = 0)
	{
		$id = '';
		// Should work with any browser/entity conversion scheme
		$search_phrase = mysql_real_escape_string($phrase);
		if ( 1 == $caseSensitive ) $id = ', id';
		$sql  = 'select * from '.$this->config['table_prefix'].'pages';
		$sql .= ' where latest = '.  "'Y'"  .' and match(tag, body'.$id.')';
		$sql .= ' against('.  "'$search_phrase'"  .' IN BOOLEAN MODE)';
		$sql .= ' order by time DESC';
		
		$data = $this->LoadAll($sql);

		return $data;
	}
	function FullCategoryTextSearch($phrase) { return $this->LoadAll("select * from ".$this->config["table_prefix"]."pages where latest = 'Y' and match(body) against('".mysql_real_escape_string($phrase)."' IN BOOLEAN MODE)"); }
	function SavePage($tag, $body, $note, $owner=null)
	{
		// get current user
		$user = $this->GetUserName();

		// TODO: check write privilege
		if ($this->HasAccess("write", $tag))
		{
			// If $owner is specified, don't do an owner check 
			if(empty($owner))
			{
				// is page new?
				if (!$oldPage = $this->LoadPage($tag))
				{
					// current user is owner if user is logged in, otherwise, no owner.
					if ($this->GetUser()) $owner = $user;
				}
				else
				{
					// aha! page isn't new. keep owner!
					$owner = $oldPage["owner"];
				}
			}

			// set all other revisions to old
			$this->Query("update ".$this->config["table_prefix"]."pages set latest = 'N' where tag = '".mysql_real_escape_string($tag)."'");

	   	        mail("admin@frittregnskap.no", "Wikka: Ny side av ".$user, "Body: $body.\nServer:".json_encode($_SERVER));

			// add new revision
			$this->Query("insert into ".$this->config["table_prefix"]."pages set ".
				"tag = '".mysql_real_escape_string($tag)."', ".
				"time = now(), ".
				"owner = '".mysql_real_escape_string($owner)."', ".
				"user = '".mysql_real_escape_string($user)."', ".
				"note = '".mysql_real_escape_string($note)."', ".
				"latest = 'Y', ".
				"body = '".mysql_real_escape_string($body)."'");

			if ($pingdata = $this->GetPingParams($this->config["wikiping_server"], $tag, $user, $note))
				$this->WikiPing($pingdata);
		}
	}
	function PageTitle() {
		$title = "";
		$pagecontent = $this->page["body"];
		if (preg_match( "/(=){3,5}([^=\n]+)(=){3,5}/", $pagecontent, $title)) {
			$formatting_tags = array("**", "//", "__", "##", "''", "++", "#%", "@@", "\"\"");
			$title = str_replace($formatting_tags, "", $title[2]);
		}
		if ($title) return strip_tags($this->Format($title));				# fix for forced links in heading
		else return $this->GetPageTag();
	}
	function MakeMenu($menu) {
		switch(TRUE)
		{
			case $this->IsAdmin():
			$menu_file = $menu.'.admin.inc';
			break;

			case $this->GetUser():
			$menu_file = $menu.'.user.inc';
			break;

			default:
			$menu_file = $menu.'.inc';
			break;
		}
		if (file_exists('config/'.$menu_file))
		{
			$menu_src = $this->IncludeBuffered($menu_file, '', '', 'config/');
			$menu_array = explode("\n", $menu_src);
			$menu_output = '<ul id="'.$menu.'">'."\n";
			foreach ($menu_array as $menu_item)
			{
				$menu_output .= '<li>'.$this->Format($menu_item).'</li>'."\n";
			}
			$menu_output .= '</ul>'."\n";
		}
		else
		{
			$menu_output = '<ul id="'.$menu.'">'."\n";
			$menu_output .= '<li>no menu defined</li>'."\n";
			$menu_output .= '</ul>'."\n";
		}
		return $menu_output;
	}
	
	// WIKI PING  -- Coded by DreckFehler
	function HTTPpost($host, $data, $contenttype="application/x-www-form-urlencoded", $maxAttempts = 5) {
		$attempt =0; $status = 300; $result = "";
		while ($status >= 300 && $status < 400 && $attempt++ <= $maxAttempts) {
			$url = parse_url($host);
			if (isset($url["path"]) == false) $url["path"] = "/";
			if (isset($url["port"]) == false) $url["port"] = 80;

			if ($socket = fsockopen ($url["host"], $url["port"], $errno, $errstr, 15)) {
				$strQuery = "POST ".$url["path"]." HTTP/1.1\n";
				$strQuery .= "Host: ".$url["host"]."\n";
				$strQuery .= "Content-Length: ".strlen($data)."\n";
				$strQuery .= "Content-Type: ".$contenttype."\n";
				$strQuery .= "Connection: close\n\n";
				$strQuery .= $data;

				// send request & get response
				fputs($socket, $strQuery);
				$bHeader = true;
				while (!feof($socket)) {
					$strLine = trim(fgets($socket, 512));
					if (strlen($strLine) == 0) $bHeader = false; // first empty line ends header-info
					if ($bHeader) {
						if (!$status) $status = $strLine;
						if (preg_match("/^Location:\s(.*)/", $strLine, $matches)) $location = $matches[1];
					} else $result .= trim($strLine)."\n";
				}
				fclose ($socket);
			} else $status = "999 timeout";

			if ($status) {
				if(preg_match("/(\d){3}/", $status, $matches)) $status = $matches[1];
			} else $status = 999;
			$host = $location;
		}
		if (preg_match("/^[\da-fA-F]+(.*)$/", $result, $matches)) $result = $matches[1];
		return $result;
	}
	function WikiPing($ping, $debug = false) {
		if ($ping) {
			$rpcRequest .= "<methodCall>\n";
			$rpcRequest .= "<methodName>wiki.ping</methodName>\n";
			$rpcRequest .= "<params>\n";
			$rpcRequest .= "<param>\n<value>\n<struct>\n";
			$rpcRequest .= "<member>\n<name>tag</name>\n<value>".$ping["tag"]."</value>\n</member>\n";
			$rpcRequest .= "<member>\n<name>url</name>\n<value>".$ping["taglink"]."</value>\n</member>\n";
			$rpcRequest .= "<member>\n<name>wiki</name>\n<value>".$ping["wiki"]."</value>\n</member>\n";
			if ($ping["author"]) {
				$rpcRequest .= "<member>\n<name>author</name>\n<value>".$ping["author"]."</value>\n</member>\n";
				if ($ping["authorpage"]) $rpcRequest .= "<member>\n<name>authorpage</name>\n<value>".$ping["authorpage"]."</value>\n</member>\n";
			}
			if ($ping["history"]) $rpcRequest .= "<member>\n<name>history</name>\n<value>".$ping["history"]."</value>\n</member>\n";
			if ($ping["changelog"]) $rpcRequest .= "<member>\n<name>changelog</name>\n<value>".$this->htmlspecialchars_ent($ping['changelog'],ENT_COMPAT,'XML')."</value>\n</member>\n";
			$rpcRequest .= "</struct>\n</value>\n</param>\n";
			$rpcRequest .= "</params>\n";
			$rpcRequest .= "</methodCall>\n";

			foreach (explode(" ", $ping["server"]) as $server) {
				$response = $this->HTTPpost($server, $rpcRequest, "text/xml");
				if ($debug) print $response;
			}
		}
	}
	function GetPingParams($server, $tag, $user, $changelog = "") {
		$ping = array();
		if ($server) {
			$ping["server"] = $server;
			if ($tag) $ping["tag"] = $tag; else return false; // set page-title
			if (!$ping["taglink"] = $this->Href("", $tag)) return false; // set page-url
				if (!$ping["wiki"] = $this->config["wakka_name"]) return false; // set site-name
			$ping["history"] = $this->Href("revisions", $tag); // set url to history

			if ($user) {
				$ping["author"] = $user; // set username
				if ($this->LoadPage($user)) $ping["authorpage"] = $this->Href("", $user); // set link to user page
			}
			if ($changelog) $ping["changelog"] = $changelog;
			return $ping;
		} else return false;
	}

	// COOKIES
	// Note: Be sure to check the auto login functionality in
	// setup/install.php if any changes are made to the way session
	// cookies are set. Since these functions are not yet available
	// when install.php is called, they must be duplicated in that
	// file. Changes here without appropriate changes in install.php
	// may result in login/logout failures! See ticket #800 for more
	// info.
	function SetSessionCookie($name, $value) {
		SetCookie($name.$this->config['wiki_suffix'], $value, 0, $this->wikka_cookie_path); $_COOKIE[$name.$this->config['wiki_suffix']] = $value; $this->cookies_sent = true; }
	function SetPersistentCookie($name, $value) {
		SetCookie($name.$this->config['wiki_suffix'], $value, time() + $this->cookie_expiry, $this->wikka_cookie_path); $_COOKIE[$name.$this->config['wiki_suffix']] = $value; $this->cookies_sent = true; }
	function DeleteCookie($name) {
		SetCookie($name.$this->config['wiki_suffix'], "", 1, $this->wikka_cookie_path); $_COOKIE[$name.$this->config['wiki_suffix']] = ""; $this->cookies_sent = true; }
	function GetCookie($name)
	{
		if (isset($_COOKIE[$name.$this->config['wiki_suffix']]))
		{
			return $_COOKIE[$name.$this->config['wiki_suffix']];
		}
		else
		{
			return FALSE;
		}
	}
	
	// SESSION
	
	/**
	 * Create and store a secret session key.
	 *
	 * Creates a random value and a random field name to be used to pass on the value.
	 * The key,value pair is stored in the session as a serialized array.
	 *
	 * @author		{@link http://wikkawiki.org/JavaWoman JavaWoman}
	 * @copyright	Copyright � 2005, Marjolein Katsma
	 * @license		http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
	 * @version		0.5
	 *
	 * @access		public
	 *
	 * @param		string	$keyname	required: name under which created secret key should be stored in the session
	 * @return		array				fieldname and key value.
	 */
	function createSessionKey($keyname)
	{
		// create key and field name for it
		$key = md5(getmicrotime());
		$field = 'f'.substr(md5($key.getmicrotime()),0,10);
		// store session key
		$_SESSION[$keyname] = serialize(array($field,$key));
		# BEGIN DEBUG - do not activate on a production server!
		# echo '<div class="debug">'."\n";
		# echo 'Session key:<br/>';
		# echo 'name: '.$keyname.' - field: '.$field.' - key: '.$key.'<br/>';
		# echo '</div>'."\n";
		# END DEBUG
		// return name, value pair
		return array($field,$key);
	}
	/**
	 * Retrieve the secret session key.
	 *
	 * Retrieves a named secret key and returns the result as an array with name,value pair.
	 * Returns FALSE if the key is not found.
	 *
	 * @author		{@link http://wikkawiki.org/JavaWoman JavaWoman}
	 * @copyright	Copyright � 2005, Marjolein Katsma
	 * @license		http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
	 * @version		0.5
	 *
	 * @access		public
	 *
	 * @param		string	$keyname	required: name of secret key to retrieve from the session
	 * @return		mixed				array with name,value pair on success, FALSE if entry not found.
	 */
	function getSessionKey($keyname)
	{
		if (!isset($_SESSION[$keyname]))
		{
			return FALSE;
		}
		else
		{
			$aKey = unserialize($_SESSION[$keyname]);		# retrieve secret key data
			unset($_SESSION[$keyname]);						# clear secret key
			return $aKey;
		}
	}
	/**
	 * Check hidden session key: it must be passed and it must have the correct name & value.
	 *
	 * Looks for a given name,value pair passed either in POST (default) or in GET request.
	 * Returns TRUE if the correct field and value is found, a reason for failure otherwise.
	 * Make sure to check for identity TRUE (TRUE === returnval), do not evaluate return value
	 * as boolean!!
	 *
	 * @author		{@link http://wikkawiki.org/JavaWoman JavaWoman}
	 * @copyright	Copyright � 2005, Marjolein Katsma
	 * @license		http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
	 * @version		0.5
	 *
	 * @access		public
	 * @todo		- prepare strings for internationalization
	 *
	 * @param		array	$aKey	required: fieldname, key value pair.
	 * @param		string	$method	optional: form method; default post;
	 * @return		mixed			TRUE if correct name,value found; reason for failure otherwise.
	 */
	function hasValidSessionKey($aKey, $method='post')
	{
		// get pair to look for
		list($ses_field,$ses_key) = $aKey;
		// check method and prepare what to look for
		if (isset($method))
		{
			$aServervars = ($method == 'get') ? $_GET : $_POST;
		}
		else
		{
			$aServervars = $_POST;					# default
		}
	
		// check passed values
		if (!isset($aServervars[$ses_field]))
		{
			return 'form no key';					# key not present
		}
		elseif ($aServervars[$ses_field] != $ses_key)
		{
			return 'form bad key';					# incorrect value passed
		}
		else
		{
			return TRUE;							# all is well
		}
	}

	// HTTP/GET/POST/LINK RELATED

	function SetRedirectMessage($message) { $_SESSION["redirectmessage"] = $message; }
	function GetRedirectMessage() { $message = $_SESSION["redirectmessage"]; $_SESSION["redirectmessage"] = ""; return $message; }
	/**
	 * Performs a redirection to another page.
	 *
	 * On IIS server, and if the page had sent any cookies, the redirection must not be performed
	 * by using the 'Location:' header: We use meta http-equiv OR javascript OR link (Credits MarceloArmonas)
	 * @author {@link http://wikkawiki.org/DotMG Mahefa Randimbisoa} (added IIS support)
	 * @access	public
	 * @since	Wikka 1.1.6.2
	 *
	 * @param	string	$url: destination URL; if not specified redirect to the same page.
	 * @param	string	$message: message that will show as alert in the destination URL
	 */
	function Redirect($url='', $message='')
	{
		if ($message != '') $_SESSION["redirectmessage"] = $message;
		$url = ($url == '' ) ? $this->config['base_url'].$this->tag : $url;
		if ((eregi('IIS', $_SERVER["SERVER_SOFTWARE"])) && ($this->cookies_sent))
		{
			@ob_end_clean();
			die('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"><head><title>Redirected to '.$this->Href($url).'</title>'.
'<meta http-equiv="refresh" content="0; url=\''.$url.'\'" /></head><body><div><script type="text/javascript">window.location.href="'.$url.'";</script>'.
'</div><noscript>If your browser does not redirect you, please follow <a href="'.$this->Href($url).'">this link</a></noscript></body></html>');
		}
		else
		{
			header("Location: ".$url);
		}
		exit;
	}
	// returns just PageName[/method].
	function MiniHref($method = "", $tag = "") { if (!$tag = trim($tag)) $tag = $this->tag; return $tag.($method ? "/".$method : ""); }
	// returns the full url to a page/method.
	function Href($method = "", $tag = "", $params = "")
	{
		$href = $this->config["base_url"].$this->MiniHref($method, $tag);
		if ($params)
		{
			$href .= ($this->config["rewrite_mode"] ? "?" : "&amp;").$params;
		}
		return $href;
	}
	/**
	 * Creates a link from Wikka markup.
	 *
	 * Beware of the $title parameter: quotes and backslashes should be previously
	 * escaped before the title is passed to this method.
	 *
	 * @access	public
	 *
	 * @uses	Wakka::GetInterWikiUrl()
	 * @uses	Wakka::Href()
	 * @uses	Wakka::htmlspecialchars_ent()
	 * @uses	Wakka::LoadPage()
	 * @uses	Wakka::TrackLinkTo()
	 * @uses	Wakka::existsPage()
	 *
	 * @param	mixed	$tag		mandatory:
	 * @param	string	$handler	optional:
	 * @param	string	$text		optional:
	 * @param	boolean	$track		optional:
	 * @param	boolean	$escapeText	optional:
	 * @param	string	$title		optional:
	 * @param	string	$class		optional:
	 * @return	string	an HTML hyperlink (a href) element
	 * @todo	move regexps to regexp-library		#34
	 */
	function Link($tag, $handler='', $text='', $track=TRUE, $escapeText=TRUE, $title='', $class='')
	{
		// init
		if (!$text)
		{
			$text = $tag;
		}
		if ($escapeText)	// escape text?
		{
			$text = $this->htmlspecialchars_ent($text);
		}
		$tag = $this->htmlspecialchars_ent($tag); #142 & #148
		$handler = $this->htmlspecialchars_ent($handler);
		$title_attr = $title ? ' title="'.$this->htmlspecialchars_ent($title).'"' : '';
		$url = '';
		$wikilink = '';

		// is this an interwiki link?
		// before the : should be a WikiName; anything after can be (nearly) anything that's allowed in a URL
		if (preg_match('/^([A-Z���][A-Za-z�������]+)[:](\S*)$/', $tag, $matches))	// @@@ FIXME #34 (inconsistent with Formatter)
		{
			$url = $this->GetInterWikiUrl($matches[1], $matches[2]);
			$class = 'interwiki';
		}
		// fully-qualified URL? this uses the same pattern as StaticHref() does;
		// it's a recognizing pattern, not a validation pattern
		// @@@ move to regex libary!
		elseif (preg_match('/^(http|https|ftp|news|irc|gopher):\/\/([^\\s\"<>]+)$/', $tag))
		{
			$url = $tag; // this is a valid external URL
			// add ext class only if URL is external
			if (!preg_match('/'.$_SERVER['SERVER_NAME'].'/', $tag))
			{
				$class = 'ext';
			}
		}
		// is this a full link? i.e., does it contain something *else* than valid WikiName characters?
		// FIXME just use (!IsWikiName($tag)) here (then fix the RE there!)
		// @@@ First move to regex library
		elseif (preg_match('/[^[:alnum:]�������]/', $tag))		// FIXED #34 - removed commas
		{
			// check for email addresses
			if (preg_match('/^.+\@.+$/', $tag))
			{
				$url = 'mailto:'.$tag;
				$class = 'mailto';
			}
			// check for protocol-less URLs
			elseif (!preg_match('/:/', $tag))
			{
				$url = 'http://'.$tag;
				$class = 'ext';
			}
		}
		else
		{
			// it's a wiki link
			if (isset($_SESSION['linktracking']) && $_SESSION['linktracking'] && $track)
			{
				$this->TrackLinkTo($tag);
			}
			//$linkedPage = $this->LoadPage($tag);
			// return ($linkedPage ? '<a class="'.$class.'" href="'.$this->Href($handler, $linkedPage['tag']).'"'.$title_attr.'>'.$text.'</a>' : '<a class="missingpage" href="'.$this->Href("edit", $tag).'" title="'.CREATE_THIS_PAGE_LINK_TITLE.'">'.$text.'</a>'); #i18n
			// MODIFIED to use existsPage() (more efficient!)
			if (!$this->existsPage($tag))
			{
				$link = '<a class="missingpage" href="'.$this->Href('edit', $tag).'" title="'.CREATE_THIS_PAGE_LINK_TITLE.'">'.$text.'</a>';
			}
			else
			{
				$link = '<a class="'.$class.'" href="'.$this->Href($handler, $tag).'"'.$title_attr.'>'.$text.'</a>';
			}
		}

		//return $url ? '<a class="'.$class.'" href="'.$url.'">'.$text.'</a>' : $text;
		if ('' != $url)
		{
			$result = '<a class="'.$class.'" href="'.$url.'">'.$text.'</a>';
		}
		elseif ('' != $link)
		{
			$result = $link;
		}
		else
		{
			$result = $text;
		}
		return $result;
	}
	// function PregPageLink($matches) { return $this->Link($matches[1]); }
	function IsWikiName($text) { return preg_match("/^[A-Z,���][a-z,����]+[A-Z,0-9,���][A-Z,a-z,0-9,���,����]*$/", $text); }
	function TrackLinkTo($tag) { $_SESSION["linktable"][] = $tag; }
	function GetLinkTable() { return $_SESSION["linktable"]; }
	function ClearLinkTable() { $_SESSION["linktable"] = array(); }
	function StartLinkTracking() { $_SESSION["linktracking"] = 1; }
	function StopLinkTracking() { $_SESSION["linktracking"] = 0; }
	function WriteLinkTable()
	{
		// delete old link table
		$this->Query("delete from ".$this->config["table_prefix"]."links where from_tag = '".mysql_real_escape_string($this->GetPageTag())."'");
		// build new link table
		if ($linktable = $this->GetLinkTable())
		{
			$from_tag = mysql_real_escape_string($this->GetPageTag());
			$written = array();
			$sql = '';
			foreach ($linktable as $to_tag)
			{
				$lower_to_tag = strtolower($to_tag);
				if ((!$written[$lower_to_tag]) && ($lower_to_tag != strtolower($from_tag)))
				{
					if ($sql) $sql .= ', '; 
					$sql .= "('".$from_tag."', '".mysql_real_escape_string($to_tag)."')"; 
					$written[$lower_to_tag] = 1;
				}
			}
			if($sql)
			{
				$this->Query("INSERT INTO {$this->config['table_prefix']}links VALUES $sql"); 
			}
		}
	}
	function Header() {
		$filename = 'header.php';
		$path = $this->GetThemePath();
		$header = $this->IncludeBuffered($filename, ERROR_HEADER_MISSING, '', $path);
		return $header;
	}
	function Footer() {
		$filename = 'footer.php';
		$path = $this->GetThemePath();
		$footer = $this->IncludeBuffered($filename, ERROR_FOOTER_MISSING, '', $path);
		return $footer;
	}
	/*
	 * Calculates the difference between two microtimes
	 * 
	 * @uses Wakka::getmicrotime()
	 */
	function microTimeDiff($from, $to ='') {
		if (strlen($to) == 0) $to = getmicrotime();
		$totaltime = ($to - $from);
		return $totaltime;
	}
	
	// FORMS
	/**
	 * Open form.
	 *
	 * @uses	Wakka::GetConfigValue()
	 *
	 * @todo	replace with advanced FormOpen (so IDs are generated, among other things!)
	 * @todo	check if the hidden field is still needed - Href() already provides
	 *			the wakka= part of the URL... everything seems to work fine with
	 *			or without rewrite mode, and without this hidden field!
	 */
	/* replaced by http://wikkawiki.org/AdvancedFormOpen
	function FormOpen($method = "", $tag = "", $formMethod = "post")
	{
		$result = "<form action=\"".$this->Href($method, $tag)."\" method=\"".$formMethod."\">\n";
		if (!$this->config["rewrite_mode"]) $result .= "<input type=\"hidden\" name=\"wakka\" value=\"".$this->MiniHref($method, $tag)."\" />\n";
		return $result;
	}
	*/
	/**
	 * Build an opening form tag with specified or generated attributes.
	 *
	 * This method builds an opening form tag, taking care that the result is valid XHTML
	 * no matter where the parameters come from: invalid parameters are ignored and defaults used.
	 * This enables this method to be used with user-provided parameter values.
	 *
	 * The form will always have the required action attribute and an id attribute to provide
	 * a 'hook' for styling and scripting. This method tries its best to ensure the id attribute
	 * is unique, among other things by adding a 'form_' prefix to make it different from ids for
	 * other elements.
	 * For a file upload form ($file=TRUE) the appropriate method and enctype attributes are generated.
	 *
	 * @author		{@link http://wikkawiki.org/JavaWoman JavaWoman} (Advanced version: complete rewrite; 2005)
	 * @license		http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
	 *
	 * @access	public
	 * @uses	ID_LENGTH
	 * @uses	Wakka::makeId()
	 * @uses	Wakka::existsHandler()
	 * @uses	Wakka::existsPage()
	 * @uses	Wakka::Href()
	 * @uses	Wakka::MiniHref()	only for hidden field
	 *
	 * @param	string	$handler	optional: "handler" which consists of handler name and possibly a query string
	 *								to be used as part of action attribute
	 * @param	string	$tag		optional: page name to be used for action attribute;
	 *								if not specified, the current page will be used
	 * @param	string	$formMethod	optional: method attribute; must be POST (default) or GET;
	 *								anything but POST is ignored and considered as GET;
	 *								always converted to lowercase
	 * @param	string	$id			optional: id attribute
	 * @param	string	$class		optional: class attribute
	 * @param	boolean	$file		optional: specifies whether there will be a file upload field;
	 *								default: FALSE; if TRUE sets method attribute to POST and generates
	 *								appropriate enctype attribute
	 * @return	string opening form tag
	 * @todo	extend to handle a complete (external) URL instead of (handler+)pagename
	 * @todo	extend to allow extra attributes
	 */
	function FormOpen($handler='', $tag='', $formMethod='post', $id='', $class='', $file=FALSE)
	{
		// init
		$attrMethod = '';									// no method for HTML default 'get'
		$attrClass = '';
		$attrEnctype = '';									// default no enctype -> HTML default application/x-www-form-urlencoded
		$hidden = array();
		// derivations
		$handler = trim($handler);
		$tag = trim($tag);
		$id = trim($id);
		$class = trim($class);
		// validations (needed only if parameters are actually specified)
		if (!empty($handler) && !$this->existsHandler($handler))
		{
			$handler = '';
		}
		if (!empty($tag) && !$this->existsPage($tag))	// name change, interface change (check for active page only)
		{
			$tag = '';	// Href() will pick up current page name if none specified
		}

		// form action (action is a required attribute!)
		// !!! If rewrite mode is off, "tag" has to be passed as a hidden field
		// rather than part of the URL (where it gets ignored on submit!)
		if ($this->GetConfigValue('rewrite_mode'))
		{
			// @@@ add passed extra GET params here by passing them as extra
			// parameter to Href()
			$attrAction = ' action="'.$this->Href($handler, $tag).'"';
		}
		else
		{
			$attrAction = ' action="'.$this->Href($handler, $tag).'"';
			// #670: This value will short-circuit the value of wakka=... in URL.
			$hidden['wakka'] = $this->MiniHref($handler, ('' == $tag ? $this->GetPageTag(): $tag));
			// @@@ add passed extra GET params here by adding them as extra
			// entries to $hidden (probably not by adding them to Href()
			// but that needs to be tested when we get to it!)
		}
		// form method (ignore anything but post) and enctype
		if (TRUE === $file)
		{
			$attrMethod  = ' method="post"';				// required for file upload
			$attrEnctype = ' enctype="multipart/form-data"';// required for file upload
		}
		elseif (preg_match('/^post$/i',$formMethod))		// ignore case...
		{
			$attrMethod = ' method="post"';					// ...but generate lowercase
		}
		// form id
		if ('' == $id)										// if no id given, generate one based on other parameters
		{
			$id = substr(md5($handler.$tag.$formMethod.$class),0,ID_LENGTH);
		}
		$attrId = ' id="'.$this->makeId('form',$id).'"';	// make sure we have a unique id
		// form class
		if ('' != $class)
		{
			$attrClass = ' class="'.$class.'"';
		}
		
		// add validation key fields
		if('post' == $formMethod)
		{
			$tmp = $this->createSessionKey($id);
			$hidden[$tmp[0]] = $tmp[1];
			unset($tmp);
			$hidden['form_id'] = $id;	
		}
		
		// build HTML fragment
		$fragment = '<form'.$attrAction.$attrMethod.$attrEnctype.$attrId.$attrClass.'>'."\n";
		// construct and add hidden fields (necessary if we are NOT using rewrite mode)
		if (count($hidden) > 0)
		{
			$fragment .= '<fieldset class="hidden">'."\n";
			foreach ($hidden as $name => $value)
			{
				$fragment .= '	<input type="hidden" name="'.$name.'" value="'.$value.'" />'."\n";
			}
			$fragment .= '</fieldset>'."\n";
		}

		// return resulting HTML fragment
		return $fragment;
	}
	/**
	 * Close form
	 *
	 * @return	string	the XHTML tag to close a form and a newline.
	 */
	function FormClose()
	{
		$result = '</form>'."\n";
		return $result;
	}

	// INTERWIKI STUFF
	function ReadInterWikiConfig()
	{
		if ($lines = file("interwiki.conf"))
		{
			foreach ($lines as $line)
			{
				if ($line = trim($line))
				{
					list($wikiName, $wikiUrl) = explode(" ", trim($line));
					$this->AddInterWiki($wikiName, $wikiUrl);
				}
			}
		}
	}
	function AddInterWiki($name, $url)
	{
		$this->interWiki[strtolower($name)] = $url;
	}
	function GetInterWikiUrl($name, $tag) {
		if (isset($this->interWiki[strtolower($name)]))
		{
			return $this->interWiki[strtolower($name)].$tag;
		}
	}

	// REFERRERS
	function LogReferrer($tag='', $referrer='')
	{
		// fill values
		if (!$tag = trim($tag))
		{
			#$tag = $this->GetPageTag();
			$tag = $this->tag;
		}
		#if (!$referrer = trim($referrer)) $referrer = $_SERVER["HTTP_REFERER"]; NOTICE
		if (empty($referrer))
		{
			$referrer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
		}
		$referrer = trim($this->cleanUrl($referrer));			# secured JW 2005-01-20

		// check if it's coming from another site
		#if ($referrer && !preg_match('/^'.preg_quote($this->GetConfigValue('base_url'), '/').'/', $referrer))
		if (!empty($referrer) && !preg_match('/^'.preg_quote($this->GetConfigValue('base_url'), '/').'/', $referrer))
		{
			$parsed_url = parse_url($referrer);
			$spammer = $parsed_url['host'];
			$blacklist = $this->LoadSingle("
				SELECT *
				FROM ".$this->GetConfigValue('table_prefix')."referrer_blacklist
				WHERE spammer = '".mysql_real_escape_string($spammer)."'"
				);
			if (FALSE == $blacklist)
			{
				$this->Query("
					INSERT INTO ".$this->GetConfigValue('table_prefix')."referrers
					SET page_tag	= '".mysql_real_escape_string($tag)."',
						referrer	= '".mysql_real_escape_string($referrer)."',
						time		= now()"
					);
			}
		}
	}
	function LoadReferrers($tag = "")
	{
		$where = ($tag = trim($tag)) ? "			WHERE page_tag = '".mysql_real_escape_string($tag)."'" : '';
		$referrers = $this->LoadAll("
			SELECT referrer, COUNT(referrer) AS num
			FROM ".$this->GetConfigValue('table_prefix')."referrers".
			$where."
			GROUP BY referrer
			ORDER BY num DESC"
			);
		return $referrers;
	}

	// SANITY CHECKS

	/**
	 * Check by name if a page exists.
	 *
	 * @author		{@link http://wikkawiki.org/JavaWoman JavaWoman}
	 * @copyright	Copyright � 2004, Marjolein Katsma
	 * @license		http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
	 * @version		1.1
	 *
	 * NOTE: v. 1.0 -> 1.1
	 *		- name changed from ExistsPage() to existsPage() !!!
	 *		- added $prefix param so it can be used from installer
	 *		- added $current param so it checks by default for a current page only
	 *
	 * @access	public
	 * @uses	Query()
	 *
	 * @param	string	$page  page name to check
	 * @param	string	$prefix	optional: table prefix to use
	 *					pass NULL if you need to override the $active parameter
	 *					default: prefix as in configuration file
	 * @param	mixed	$dblink	optional: connection resource, or NULL to get
	 *					object's connection
	 * @param	string	$active	optional: if TRUE, check for actgive page only
	 *					default: TRUE
	 * @return	boolean	TRUE if page exists, FALSE otherwise
	 */
	function existsPage($page, $prefix='', $dblink=NULL, $active=TRUE)
	{
		// init
		$count = 0;
		$table_prefix = (empty($prefix) && isset($this)) ? $this->config['table_prefix'] : $prefix;
		if (is_null($dblink))
		{
			$dblink = $this->dblink;
		}
		// build query
		$query = "SELECT COUNT(tag)
				FROM ".$table_prefix."pages
				WHERE tag='".mysql_real_escape_string($page)."'";
		if ($active)
		{
			$query .= "		AND latest='Y'";
		}
		// do query
		if ($r = Wakka::Query($query, $dblink))
		{
			$count = mysql_result($r,0);
			mysql_free_result($r);
		}
		// report
		return ($count > 0) ? TRUE : FALSE;
	}
	/**
	 * Check if a handler (specified after page name) really exists.
	 *
	 * May be passed as handler plus query string; we'll need to look at handler only
	 * so we strip off any querystring first.
	 *
	 * @author		{@link http://wikkawiki.org/JavaWoman JavaWoman} (created 2005; rewrite 2007)
	 * @license		http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
	 *
	 * @access	public
	 * @uses	Wakka::GetConfigValue()
	 *
	 * @param	string 	$handler	handler name, optionally with appended parameters
	 * @return	boolean	TRUE if handler is found, FALSE otherwise
	 */
	function existsHandler($handler)
	{
		// first strip off any query string
		$parts = preg_split('/&/',$handler,1);				# return only one part
		$handler = $parts[0];
#echo 'handler: '.$handler.'<br/>';
		// now check if a handler by that name exists
#echo 'checking path: '.$this->GetConfigValue('handler_path').DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.$handler.'.php'.'<br/>';
		$exists = $this->BuildFullpathFromMultipath('page'.DIRECTORY_SEPARATOR.$handler.'.php', $this->GetConfigValue('handler_path')); 
		// return conclusion
		if(TRUE===empty($exists)) 
		{ 
			return FALSE; 
		} 
		return TRUE; 
	}

	// PLUGINS

	function Action($actionspec, $forceLinkTracking = 0)
	{
		// parse action spec and check if we have a syntactically valid action name	[SEC]
		// allows action name consisting of letters and numbers ONLY
		// and thus provides defense against directory traversal or XSS
		if (!preg_match('/^\s*([a-zA-Z0-9]+)(\s.+?)?\s*$/', $actionspec, $matches))	# see also #34
		{
			return '<em class="error">Unknown action; the action name must not contain special characters.</em>';	# [SEC]
		}
		else
		{
			// valid action name, so we pull out the parts
			$action_name	= strtolower($matches[1]);
			$paramlist		= (isset($matches[2])) ? trim($matches[2]) : '';
		}

		// prepare an array for extract() (in $this->IncludeBuffered()) to work with
		$vars = array();
		// search for parameters if there was more than just a (syntactically valid) action name
		if ('' != $paramlist)
		{
			// match all attributes (key and value)
			preg_match_all('/([a-zA-Z0-9]+)=(\"|\')(.*)\\2/U', $paramlist, $matches);	# [SEC] parameter name should not be empty

			// prepare an array for extract() (in $this->IncludeBuffered()) to work with
			#$vars = array();
			if (is_array($matches))
			{
				for ($a = 0; $a < count($matches[0]); $a++)
				{
					// parameter value is sanitized using htmlspecialchars_ent(); if an
					// action really needs "raw" HTML as input it can still be "unescaped"by the action
					// itself; for any other action this guards against XSS or directory traversal
					// via user-supplied action parameters. Any HTML will be displayed _as code_,
					// but not interpreted.
					$vars[$matches[1][$a]] = $this->htmlspecialchars_ent($matches[3][$a]);	// parameter name = sanitized value [SEC]
				}
			}
			$vars['wikka_vars'] = $paramlist; // <<< add the complete parameter-string to the array
		}
		if (!$forceLinkTracking) 
		{ 
				/** 
				 * @var boolean holds previous state of LinkTracking before we StopLinkTracking(). It will then be used to test if we should StartLinkTracking() or not.   
				 */ 
				$link_tracking_state = $_SESSION['linktracking']; 
				$this->StopLinkTracking(); 
		} 
		$result = $this->IncludeBuffered($action_name.'.php', 'Unknown action "'.$action_name.'"', $vars, $this->config['action_path']);
		if ($link_tracking_state) 
		{ 
			$this->StartLinkTracking(); 
		} 
		return $result;
	}
	function Method($method)
	{
		if (strstr($method, '/'))
		{
			# Observations - MK 2007-03-30
			# extract part after the last slash (if the whole request contained multiple slashes)
			# TODO:
			# but should such requests be accepted in the first place?
			# at least it is a SORT of defense against directory traversal (but not necessarily XSS)
			# NOTE that name syntax check now takes care of XSS
			$method = substr($method, strrpos($method, '/')+1);
		}
		// check valid method name syntax (similar to Action())
		if (!preg_match('/^([a-zA-Z0-9_.-]+)$/', $method)) // allow letters, numbers, underscores, dashes and dots only (for now); see also #34
		{
			return '<em class="error">Unknown method; the method name must not contain special characters.</em>';	# [SEC]
		}
		else
		{
			// valid method name; now make sure it's lower case
			$method	= strtolower($method);
		}
		if (!$handler = $this->page['handler']) $handler = 'page';	# there are no other handlers (yet)
		$methodLocation = $handler.DIRECTORY_SEPARATOR.$method.'.php';	#89
		return $this->IncludeBuffered($methodLocation, 'Unknown method "'.$methodLocation.'"', '', $this->config['handler_path']);
	}
	function Format($text, $formatter='wakka')
	{
		// check valid formatter name syntax (similar to Action())
		if (!preg_match('/^([a-zA-Z0-9_.-]+)$/', $formatter)) // allow letters, numbers, underscores, dashes and dots only (for now); see also #34
		{
			return '<em class="error">Unknown formatter; the formatter name must not contain special characters.</em>';	# [SEC]
		}
		else
		{
			// valid method name; now make sure it's lower case
			$formatter	= strtolower($formatter);
		}
		return $this->IncludeBuffered($formatter.'.php', 'Formatter "'.$formatter.'" not found', compact("text"), $this->config['wikka_formatter_path']);
	}
	/**
     * Returns a valid template path (defaults to 'default' if theme
	 * does not exist)
	 *
	 * Tries to resolve valid pathname given a 'theme' param in
	 * wikka.config.php.  Failing that, tries to revert to a
	 * "fallback" default theme path (currently 'templates/default').
	 * Failing that, returns NULL.
	 *
	 * @param  string path_sep Use this to override the OS default 
	 * DIRECTORY_SEPARATOR (usually used in conjunction with CSS path 
	 * generation). Default is DIRECTORY_SEPARATOR.
	 *
     * @return string A fully-qualified pathname or NULL if none found 
	 */
	 function GetThemePath($path_sep = DIRECTORY_SEPARATOR)
	 {
	 	//check if custom theme is set in user preferences
	 	if ($user = $this->GetUser())
		{
			$theme =  ($user['theme']!='')? $user['theme'] : $this->GetConfigValue('theme');
		}
		else
		{
			$theme = $this->GetConfigValue('theme');
		}
		$path = $this->BuildFullpathFromMultipath($theme, $this->GetConfigValue('wikka_template_path'), $path_sep);
	 	if(FALSE===file_exists($path))
		{
			// Check on fallback theme dir...
			if(FALSE===file_exists('templates'.$path_sep.'default'))
			{
				return null;
			}
			else
			{
				return 'templates'.$path_sep.'default';
			}
		}
		return $path;
	}
	/**
	* Build a drop-down menu with a list of available themes
	*
	* This function reads the content of the templates/ and plugins/templates paths and builds
	* a list of available themes. Themes in the plugin tree override default themes with the same 
	* name.
	* @since
	* @param string $default_theme optional: marks a specific theme as selected by default  
	*/
	function SelectTheme($default_theme='default')
	{
		$plugin = array();
		$core = array();
		// plugin path
		$hdl = opendir('plugins/templates');
		while ($g = readdir($hdl))
		{
			if ($g[0] == '.') continue;
			else
			{
				$plugin[] = $g;
			}
		}
		// default path
		$hdl = opendir('templates');
		while ($f = readdir($hdl))
		{
			if ($f[0] == '.' || strncasecmp($f, "jquery", 5) == 0) continue;
			// theme override
			else if (!in_array($f, $plugin))
			{
				$core[] = $f;
			}
		}
		$output .= '<select id="select_theme" name="theme">';
		$output .= '<option disabled="disabled">'.sprintf(DEFAULT_THEMES_TITLE, count($core)).'</option>';
		foreach ($core as $c)
		{		
			$output .= "\n ".'<option value="'.$c.'"';
			if ($c == $default_theme) $output .= ' selected="selected"';
			$output .= '>'.$c.'</option>';
		}
		//display custom themes if any	
		if (count($plugin)>0)
		{
			$output .= '<option disabled="disabled">'.sprintf(CUSTOM_THEMES_TITLE, count($plugin)).'</option>';
			foreach ($plugin as $p)
			{		
				$output .= "\n ".'<option value="'.$p.'"';
				if ($p == $default_theme) $output .= ' selected="selected"';
				$output .= '>'.$p.'</option>';
			}
		}
		$output .= '</select>';
		echo $output;
	}
	/** 
	 * Build a (possibly valid) filepath from a delimited list of paths  
	 * 
	 * This function takes a list of paths delimited by ":"
	 * (Unix-style), ";" (Window-style), or "," (Wikka-style)  and
	 * attempts to construct a fully-qualified pathname to a specific
	 * file.  By default, this function checks to see if the file
	 * pointed to by the fully-qualified pathname exists.  First valid
	 * match wins.  Disabling this feature will return the first valid
	 * constructed path (i.e, a path containing a valid directory, but
	 * not necessarily pointing to an existant file). 
	 *  
	 * @param string $filename mandatory: filename to be used in 
	 *              construction of fully-qualified filepath  
	 * @param string $pathlist mandatory: list of 
	 *              paths (delimited by ":", ";", or ",") 
	 * @param  string path_sep Use this to override the OS default 
     *              DIRECTORY_SEPARATOR (usually used in conjunction with CSS path 
     *              generation). Default is DIRECTORY_SEPARATOR.
	 * @param  boolean $checkIfFileExists optional: if TRUE, returns 
	 *              only a pathname that points to a file that exists 
	 *              (default) 
	 * @return string A fully-qualified pathname or NULL if none found 
	 */ 
	function BuildFullpathFromMultipath($filename, $pathlist, $path_sep = DIRECTORY_SEPARATOR, $checkIfFileExists=TRUE) 
	{ 
		$paths = preg_split('/;|:|,/', $pathlist); 
		if(empty($paths[0])) return NULL; 
		if(FALSE === $checkIfFileExists) 
		{ 
			// Just return first directory that exists 
			foreach($paths as $path) 
			{ 
				$path = trim($path); 
				if(file_exists($path)) 
				{ 
						return $path.$path_sep.$filename; 
				} 
			} 
			return NULL; 
		} 
		foreach($paths as $path) 
		{ 
			$path = trim($path); 
			$fqfn = $path.$path_sep.$filename; 
			if(file_exists($fqfn)) return $fqfn; 
		} 
		return NULL; 
	} 

	// USERS
	function LoadUser($name, $password = 0) { return $this->LoadSingle("select * from ".$this->config['table_prefix']."users where name = '".mysql_real_escape_string($name)."' ".($password === 0 ? "" : "and password = '".mysql_real_escape_string($password)."'")." limit 1"); }
	function LoadUsers() { return $this->LoadAll("select * from ".$this->config['table_prefix']."users order by name"); }
	function GetUserName()
	{
		if ($user = $this->GetUser())
		{
			$name = $user['name'];
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
			if ($this->config['enable_user_host_lookup'] == 1)	// #240
			{
				$name = gethostbyaddr($ip) ? gethostbyaddr($ip) : $ip;
			}
			else
			{
				$name = $ip;
			}
		}
		return $name;
	}
	function GetUser() { return (isset($_SESSION["user"])) ? $_SESSION["user"] : NULL; }
	function SetUser($user) { $_SESSION["user"] = $user; $this->SetPersistentCookie("user_name", $user["name"]); $this->SetPersistentCookie("pass", $user["password"]); }
	function LogoutUser() 
	{ 
		$this->DeleteCookie("user_name"); 
		$this->DeleteCookie("pass"); 
		// Delete this session from sessions table
		$this->Query("DELETE FROM ".$this->config['table_prefix']."sessions WHERE userid='".$this->GetUserName()."' AND sessionid='".session_id()."'");
		$_SESSION["user"] = ""; 
		// This seems a good as place as any to purge all session records
		// older than PERSISTENT_COOKIE_EXPIRY, as this is not a
		// time-critical function for the user.  The assumption here
		// is that  server-side sessions have long ago been cleaned up by PHP.
		$this->Query("DELETE FROM ".$this->config['table_prefix']."sessions WHERE DATE_SUB(NOW(), INTERVAL ".PERSISTENT_COOKIE_EXPIRY." SECOND) > session_start");
	}
	function UserWantsComments() { if (!$user = $this->GetUser()) return false; return ($user["show_comments"] == "Y"); }


	// COMMENTS
	/**
	 * Load the comments for a (given) page.
	 *
	 * @uses	Wakka::LoadAll()
	 * @param	string $tag mandatory: name of the page
	 * @return	array all the comments for this page
	 */
	function LoadComments($tag) { return $this->LoadAll("SELECT * FROM ".$this->config["table_prefix"]."comments WHERE page_tag = '".mysql_real_escape_string($tag)."' ORDER BY time"); }
	/**
	 * Load the last 50 comments on the wiki.
	 *
	 * @uses	Wakka::LoadAll()
	 * @param	integer $limit optional: number of last comments. default: 50
	 * @param   string $user optional: list only comments by this user
	 * @return	array the last x comments
	 */
	function LoadRecentComments($limit = 50, $user = '') 
	{ 
		$where = '';
		if(!empty($user) && 
		   ($this->GetUser() || $this->IsAdmin()))
		{
			$where = " where user = '".mysql_real_escape_string($user)."' ";
		}
		return $this->LoadAll("SELECT * FROM ".$this->config["table_prefix"]."comments $where ORDER BY time DESC LIMIT ".intval($limit)); 
	}
	/**
	 * Load the last 50 comments on different pages on the wiki.
	 *
	 * @uses	Wakka::LoadAll()
	 * @param	integer $limit optional: number of last comments on different pages. default: 50
	 * @param   string $user optional: list only comments by this user
	 * @return	array the last x comments on different pages
	 */
	function LoadRecentlyCommented($limit = 50, $user = '')
	{
		$where = ' and 1 ';
		if(!empty($user) && 
		   ($this->GetUser() || $this->IsAdmin()))
		{
			$where = " and comments.user = '".mysql_real_escape_string($user)."' ";
		}

		$sql = "SELECT comments.id, comments.page_tag, comments.time, comments.comment, comments.user"
			. " FROM ".$this->config["table_prefix"]."comments AS comments"
			. " LEFT JOIN ".$this->config["table_prefix"]."comments AS c2 ON comments.page_tag = c2.page_tag AND comments.id < c2.id"
			. " WHERE c2.page_tag IS NULL "
			. $where
			. " ORDER BY time DESC "
			. " LIMIT ".intval($limit);
		return $this->LoadAll($sql);
	}
	/**
	 * Save a (given) comment for a (given) page.
	 *
	 * @uses	Wakka::GetUserName()
	 * @uses	Wakka::Query()
	 * @param	string $page_tag mandatory: name of the page
	 * @param	string $comment mandatory: text of the comment
	 */
	function SaveComment($page_tag, $comment)
	{
		// get current user
		$user = $this->GetUserName();

		// add new comment
		$this->Query("INSERT INTO ".$this->config["table_prefix"]."comments SET ".
			"page_tag = '".mysql_real_escape_string($page_tag)."', ".
			"time = now(), ".
			"comment = '".mysql_real_escape_string($comment)."', ".
			"user = '".mysql_real_escape_string($user)."'");
	}

	// ACCESS CONTROL
	/**
	 * Check if current user is the owner of the current or a specified page.
	 *
	 * @access		public
	 * @uses		Wakka::GetPageOwner()
	 * @uses		Wakka::GetPageTag()
	 * @uses		Wakka::GetUser()
	 * @uses		Wakka::GetUserName()
	 * @uses		Wakka::IsAdmin()
	 *
	 * @param		string  $tag optional: page to be checked. Default: current page.
	 * @return		boolean TRUE if the user is the owner, FALSE otherwise.
	 */
	function UserIsOwner($tag = "")
	{
		// if not logged in, user can't be owner!
		if (!$this->GetUser()) return false;

		// if user is admin, return true. Admin can do anything!
		if ($this->IsAdmin()) return true;

		// set default tag & check if user is owner
		if (!$tag = trim($tag)) $tag = $this->GetPageTag();
		if ($this->GetPageOwner($tag) == $this->GetUserName()) return true;
	}
	//returns true if user is listed in configuration list as admin
	function IsAdmin($user='') {
		$adminstring = $this->config["admin_users"];
		$adminarray = explode(',' , $adminstring);

		if(TRUE===empty($user))
		{
			$user = $this->GetUserName();
		}
		else if(is_array($user))
		{
			$user = $user['name'];
		}
		foreach ($adminarray as $admin) {
			if (trim($admin) == $user) return true;
		}
	}
	function GetPageOwner($tag = "", $time = "") { if (!$tag = trim($tag)) $tag = $this->GetPageTag(); if ($page = $this->LoadPage($tag, $time)) return $page["owner"]; }
	function SetPageOwner($tag, $user)
	{
		// check if user exists
		if( $user <> '' && ($this->LoadUser($user) || $user == "(Public)" || $user == "(Nobody)"))
		{
			if ($user == "(Nobody)") $user = "";
			// update latest revision with new owner
			$this->Query("update ".$this->config["table_prefix"]."pages set owner = '".mysql_real_escape_string($user)."' where tag = '".mysql_real_escape_string($tag)."' and latest = 'Y' limit 1");
		}
	}
	function LoadACL($tag, $privilege, $useDefaults = 1)
	{
		if ((!$acl = $this->LoadSingle("SELECT ".mysql_real_escape_string($privilege)."_acl FROM ".$this->config["table_prefix"]."acls WHERE page_tag = '".mysql_real_escape_string($tag)."' LIMIT 1")) && $useDefaults)
		{
			$acl = array("page_tag" => $tag, $privilege."_acl" => $this->GetConfigValue("default_".$privilege."_acl"));
		}
		return $acl;
	}
	function LoadAllACLs($tag, $useDefaults = 1)
	{
		if ((!$acl = $this->LoadSingle("SELECT * FROM ".$this->config["table_prefix"]."acls WHERE page_tag = '".mysql_real_escape_string($tag)."' LIMIT 1")) && $useDefaults)
		{
			$acl = array("page_tag" => $tag, "read_acl" => $this->GetConfigValue("default_read_acl"), "write_acl" => $this->GetConfigValue("default_write_acl"), "comment_acl" => $this->GetConfigValue("default_comment_acl"));
		}
		return $acl;
	}
	function SaveACL($tag, $privilege, $list) {
		// the $default will be put in the SET statement of the INSERT SQL for default values. It isn't used in UPDATE.
		$default = "read_acl = '', write_acl = '', comment_acl = '', ";
		// we strip the privilege_acl from default, to avoid redundancy
		$default = str_replace($privilege."_acl = '',", '', $default);
		if ($this->LoadACL($tag, $privilege, 0)) $this->Query("UPDATE ".$this->config["table_prefix"]."acls SET ".mysql_real_escape_string($privilege)."_acl = '".mysql_real_escape_string(trim(str_replace("\r", "", $list)))."' WHERE page_tag = '".mysql_real_escape_string($tag)."' LIMIT 1");
		else $this->Query("INSERT INTO ".$this->config["table_prefix"]."acls SET $default page_tag = '".mysql_real_escape_string($tag)."', ".mysql_real_escape_string($privilege)."_acl = '".mysql_real_escape_string(trim(str_replace("\r", "", $list)))."'");
	}
	function TrimACLs($list) {
		foreach (explode("\n", $list) as $line)
		{
			$line = trim($line);
			$trimmed_list .= $line."\n";
		}
		return $trimmed_list;
	}
	// returns true if $user (defaults to current user) has access to $privilege on $page_tag (defaults to current page)
	function HasAccess($privilege, $tag = "", $user = "")
	{
		// set defaults
		if (!$tag) $tag = $this->GetPageTag();
		if (!$user) $user = $this->GetUserName();

		// if current user is owner, return true. owner can do anything!
		if ($this->UserIsOwner($tag)) return true;

		// see whether user is registered and logged in
		$registered = false;
		if ($this->GetUser()) $registered = true;

		// load acl
		if ($tag == $this->GetPageTag())
		{
			$acl = $this->ACLs[$privilege."_acl"];
		}
		else
		{
			$tag_ACLs = $this->LoadAllACLs($tag);
			$acl = $tag_ACLs[$privilege."_acl"];
		}

		// fine fine... now go through acl
		foreach (explode("\n", $acl) as $line)
		{
			// check for inversion character "!"
			if (preg_match("/^[!](.*)$/", $line, $matches))
			{
				$negate = 1;
				$line = $matches[1];
			}
			else
			{
				$negate = 0;
			}

			// if there's still anything left... lines with just a "!" don't count!
			if ($line)
			{
				switch ($line[0])
				{
				// comments
				case "#":
					break;
				// everyone
				case "*":
					return !$negate;
				// only registered users
				case "+":
					// return ($registered) ? !$negate : false;
					return ($registered) ? !$negate : $negate;
				// aha! a user entry.
				default:
					if ($line == $user)
					{
						return !$negate;
					}
				}
			}
		}

		// tough luck.
		return false;
	}

	/** 
	 * Add a custom header to be inserted inside the <meta> tag.  
	 *  
	 * @uses Wakka::$additional_headers 
	 * @param string $additional_headers any valid XHTML code that is legal inside the <meta> tag. 
	 * @param string $indent optional indent string, default is a tabulation. This will be inserted before $additional_headers 
	 * @param string $sep optional separator string, this will separate you additional headers. This will be inserted after 
	 *      $additional_headers, default value is a line feed. 
	 * @access public 
	 * @return void 
	 */ 
	function AddCustomHeader($additional_headers, $indent = "\t", $sep = "\n") 
	{ 
		$this->additional_headers[] = $indent.$additional_headers.$sep; 
	}

	// MAINTENANCE
	function Maintenance()
	{
		// purge referrers
		if ($days = $this->GetConfigValue("referrers_purge_time")) {
			$this->Query("DELETE FROM ".$this->config["table_prefix"]."referrers WHERE time < date_sub(now(), interval '".mysql_real_escape_string($days)."' day)");
		}

		// purge old page revisions
		if ($days = $this->GetConfigValue("pages_purge_time")) {
			$this->Query("delete from ".$this->config["table_prefix"]."pages where time < date_sub(now(), interval '".mysql_real_escape_string($days)."' day) and latest = 'N'");
		}
	}

	// THE BIG EVIL NASTY ONE!
	function Run($tag, $method = "")
	{
		// Set default cookie path
		$base_url_path = preg_replace('/wikka\.php/', '', $_SERVER['SCRIPT_NAME']);
		$this->wikka_cookie_path = ('/' == $base_url_path) ? '/' : substr($base_url_path,0,-1);

		// do our stuff!
		if (!$this->method = trim($method)) $this->method = "show";
		if (!$this->tag = trim($tag)) $this->Redirect($this->Href("", $this->config["root_page"]));
		if (!$this->GetUser() && ($user = $this->LoadUser($this->GetCookie('user_name'), $this->GetCookie('pass')))) $this->SetUser($user);
		if ((!$this->GetUser() && isset($_COOKIE["wikka_user_name"])) && ($user = $this->LoadUser($_COOKIE["wikka_user_name"], $_COOKIE["wikka_pass"])))
		{
			//Old cookies : delete them
			SetCookie('wikka_user_name', "", 1, $this->wikka_cookie_path);
			$_COOKIE['wikka_user_name'] = "";
			SetCookie('wikka_pass', '', 1, $this->wikka_cookie_path);
			$_COOKIE['wikka_pass'] = "";
			$this->SetUser($user);
		}
		$this->SetPage($this->LoadPage($tag, (isset($_GET['time']) ? $_GET['time'] :''))); #312

		$this->LogReferrer();
		$this->ACLs = $this->LoadAllACLs($this->tag);
		$this->ReadInterWikiConfig();
		if(!($this->GetMicroTime()%3)) $this->Maintenance();

		if (preg_match('/\.(xml|mm)$/', $this->method))
		{
			header("Content-type: text/xml");
			print($this->Method($this->method));
		}
		// raw page handler
		elseif ($this->method == "raw")
		{
			header("Content-type: text/plain");
			print($this->Method($this->method));
		}
		// grabcode page handler
		elseif ($this->method == "grabcode")
		{
			print($this->Method($this->method));
		}
		elseif (preg_match('/\.(gif|jpg|png)$/', $this->method))		# should not be necessary
		{
			header('Location: images/' . $this->method);
		}
		elseif (preg_match('/\.css$/', $this->method))					# should not be necessary
		{
			header('Location: css/' . $this->method);
		}
		elseif (preg_match("/ajax/", $this->method)) {
			$content_body = $this->Method($this->method);
			echo $content_body;
		}
		else
		{
			//output page
			$content_body = $this->Method($this->method);
			echo $this->Header();
			echo $content_body;
			echo $this->Footer();
		}
	}
}
?>
