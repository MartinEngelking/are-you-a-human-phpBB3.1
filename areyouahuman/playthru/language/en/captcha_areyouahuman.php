<?php
/**
 *
 * @package Are You A Human PlayThru
 * @version 2.0.0
 * @copyright (c) AreYouAHuman
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, v2
 */

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'AREYOUAHUMAN_LANG'			=> 'en',
	'CAPTCHA_AREYOUAHUMAN'			=> 'AreYouAHuman',
	'AREYOUAHUMAN_PUBLISHER_KEY'		=> 'AreYouAHuman Publisher Key',
	'AREYOUAHUMAN_PUBLISHER_KEY_EXPLAIN'	=> 'This is your AreYouAHuman publisher key.',
	'AREYOUAHUMAN_SCORING_KEY'		=> 'AreYouAHuman Scoring Key',
	'AREYOUAHUMAN_SCORING_KEY_EXPLAIN'	=> 'This is your AreYouAHuman scoring key.',
	'AREYOUAHUMAN_SERVER'			=> 'AreYouAHuman Server',
	'AREYOUAHUMAN_SERVER_EXPLAIN'		=> 'This is usually ws.areyouahuman.com.',
	'AREYOUAHUMAN_NO_KEY'			=> 'This extension\'s configuration is missing required keys. Please configure the extension. Register at <a href="http://www.areyouahuman.com">www.areyouahuman.com</a> for keys.',
    'AREYOUAHUMAN_GAME_MODE_NOTICE' => 'If PlayThru is set to "Lightbox" mode, it won\'t display until you submit your form. You can check which mode PlayThru is set to in the "Game Style" section of the <a href="http://portal.areyouahuman.com/dashboard">AreYouAHuman Dashboard</a>.',

	# error messages
	'AREYOUAHUMAN_ERROR_UNAVAILABLE'    => '<strong>AreYouAHuman PlayThru is unavailable.</strong><br/>Please contact the administrator of this site.',
    'AREYOUAHUMAN_NOT_A_HUMAN'          => 'Sorry, we were not able to confirm that you are a human. Please complete the PlayThru game.'
));

?>
