<?php
/**
* Renders a string as colored text.
*
* You can specifiy either one of HTML defined names or hex-values (with the former one 
* taking priority). An optional background color can also be specified.
*
* @package    Actions
* @name    Color
*
* @author    {@link http://www.mornography.de/ Hendrik Mans}
* @author    {@link http://wikkawiki.org/NilsLindenberg Nils Lindenberg} (modifications)
* @author    {@link http://wikkawiki.org/DotMG Mahefa Randimbisoa} (modification - RGB syntax)
*
* @version    1.3
*
* @input    string $text mandatory: the text which should be colored.    
* @input    string $c optional: (html)name or hex-value of the color for the text;
* @input    string $hex optional: alias for $c
*           kept for backwards-compatibility;
* @input    string fg optional: alias for $c
*           introduced to fit with $bg
* @input    string $bg optional: (html)name or hex-value for the backgroundcolor;
* @output   colored text
*
* @constraint    at least one valid parameter for $c (or $hex or $fg) or $bg is required
*
* @documentation  {@link http://wikkawiki.org/PatternValidColorInfoFR}
* @documentation  {@link http://wikkawiki.org/ColorActionInfo}
*
* @todo     make it part of the formatter instead of using an action
*/

// *** Constant section ***
if (!defined('ERROR_NO_TEXT_GIVEN')) define('ERROR_NO_TEXT_GIVEN','Det er ingen tekst for merking!');
if (!defined('ERROR_NO_COLOR_SPECIFIED')) define('ERROR_NO_COLOR_SPECIFIED', 'Du valgte ingen tekst for merking!');

// ***Internal function to test if syntax is valid
if (!function_exists('color_syntax_is_valid'))
{
	function color_syntax_is_valid($syntax)
	{
		//Todo: To be more strict, ensure that when using rgb(r, g, b) syntax, integer values for r, g, and b are less than 256, or if % is used, those values are not greater than 100%
		if (!defined('PATTERN_VALID_HEX_COLOR')) define('PATTERN_VALID_HEX_COLOR', '#(?>[\da-f]{3}){1,2}');
		if (!defined('PATTERN_VALID_RGB_COLOR')) define('PATTERN_VALID_RGB_COLOR', 'rgb\(\s*\d+((?>\.\d*)?%)?\s*(?>,\s*\d+(?(1)(\.\d*)?%)\s*){2}\)');
		$html_color_names = array('aqua', 'black', 'blue', 'fuchsia', 'gray', 'green', 'lime', 'maroon', 'navy', 'olive', 'purple', 'red', 'silver', 
		 'teal', 'white', 'yellow');
		$syntax = trim(strtolower($syntax));
		if (in_array($syntax, $html_color_names))
		{
			return($syntax);
		}
		if (preg_match('/^(?>'.PATTERN_VALID_HEX_COLOR.'|'.PATTERN_VALID_RGB_COLOR.')$/i', $syntax, $match))
		{
			return($match[0]);
		}
		return(false);
	}
}

// initialization
$mytext = '';
$style = '';
$output = '';

// *** User input section ***
if (is_array($vars)) 
{
	foreach ($vars as $param => $value) 
	{
		if ($param == 'text')
		{
			$mytext = $this->htmlspecialchars_ent($value);
		}
		elseif (($param == 'c') || ($param == 'hex') || ($param == 'fg'))
		{
			$fgcolor = color_syntax_is_valid($value);
			if ($fgcolor)
			{
				$style .= "color: $fgcolor; ";
			}
		}
		elseif ($param == 'bg')
		{
			$bgcolor = color_syntax_is_valid($value);
			if ($bgcolor)
			{
				$style .= "background-color: $bgcolor; ";
			}
		}
	}
	if (($mytext) && ($style))
	{
		$output .= '<span style="'.$style.'">'.$mytext.'</span>';
	}
	elseif (!$mytext)
	{
		$output .= '<em class="error">'.ERROR_NO_TEXT_GIVEN.'</em>';
	}
	elseif (!$style)
	{
		$output .= '<em class="error">'.ERROR_NO_COLOR_SPECIFIED.'</em>';
	}
	// *** Output section ***
	print ($output);
}

?>
