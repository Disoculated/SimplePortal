<?php
/**
 * Mobile Device Detect (MDD)
 *
 * @package MDD
 * @author emanuele
 * @2nd author feline
 * @copyright 2012 feline, emanuele, Simple Machines
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0 (AL2)
 *
 * @version 0.2.2
 */

/**
 * This function checks if the current user is using a mobile device
 *
 * @return: boolean true if the user is using a mobile device UA, false if not
 */
function CheckIfMobile()
{
	//Gets set so if you want to use mobile status for other
	//settings, it's available without reparsing.
	global $context;

	//Skip all the parsing if we already know it's mobile
	if(isset($context['MobileDevice']))
	{
		return true;
	}

	//if we don't have a useragent, assume we're not mobile?
	if(isset($_SERVER['HTTP_USER_AGENT'])
	{
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	} else {
		return false;
	}
	
	$context['MobileDevice'] = array(
		'isMobile' => false,
	);

	// These strings cannot be used in isMobile because are way too generic
	$genericStrings = array(
		'engineWebKit' 		=> 'webkit',
		'deviceMacPpc' 		=> 'macintosh', //Used for disambiguation
		'vendorMS'		=> 'microsoft',
		'osWindows' 		=> 'windows',
		'devicePpc' 		=> 'ppc', 	//Stands for PocketPC
		'osLinux' 		=> 'linux',
		'engineOpera' 		=> 'opera', 	//Popular browser
		'engineMozilla' 	=> 'mozilla', 
		'engineGecko' 		=> 'gecko', 
		'engineChrome' 		=> 'chrome', 
		'engineSafari' 		=> 'safari', 
	);

	//Initialize some initial smartphone string variables.
	$mobileStrings = array(
		//Apple
		'deviceIphone' 		=> 'iphone',
		'deviceIpod' 		=> 'ipod',
		'deviceIpad' 		=> 'ipad',
		//Google
		'deviceAndroid' 	=> 'android',
		'deviceGoogleTV' 	=> 'googletv',
		'deviceXoom' 		=> 'xoom', 	//Motorola Xoom
		'deviceHtcFlyer' 	=> 'htc_flyer', //HTC Flyer
		'deviceNuvifone' 	=> 'nuvifone',  //Garmin Nuvifone
		'deviceGTI9000' 	=> 'gt-i9000',  //Samsung Galaxy I9000
		//Symbian
		'deviceSymbian' 	=> 'symbian',
		'deviceS60' 		=> 'series60',
		'deviceS70' 		=> 'series70',
		'deviceS80' 		=> 'series80',
		'deviceS90' 		=> 'series90',
		//Microsoft
		'deviceWinPhone7' 	=> 'windows phone os 7',
		'deviceWinMob' 		=> 'windows ce',
		'enginePie' 		=> 'wm5 pie', 		//An old Windows Mobile
		//RIM
		'deviceBB' 		=> 'blackberry',
		'vendorRIM' 		=> 'vnd.rim', 		//Detectable when BB devices emulate IE or Firefox
		'deviceBBStorm' 	=> 'blackberry95',  	//Storm 1 and 2
		'deviceBBBold' 		=> 'blackberry97', 	//Bold 97x0 (non-touch)
		'deviceBBBoldTouch' 	=> 'blackberry 99', 	//Bold 99x0 (touchscreen)
		'deviceBBTour' 		=> 'blackberry96', 	//Tour
		'deviceBBCurve' 	=> 'blackberry89', 	//Curve2
		'deviceBBTorch' 	=> 'blackberry 98', 	//Torch
		'deviceBBPlaybook' 	=> 'playbook', 		//PlayBook tablet
		//Palm
		'devicePalm' 		=> 'palm',
		'deviceWebOS' 		=> 'webos', 		//For Palm's line of WebOS devices
		'deviceWebOShp' 	=> 'hpwos', 		//For HP's line of WebOS devices
		'engineBlazer' 		=> 'blazer', 		//Old Palm browser
		'engineXiino' 		=> 'xiino', 		//Another old Palm
		//Amazon
		'deviceKindle' 		=> 'kindle', 		//Amazon Kindle, eInk one.
		//Mozilla
		'engineFirefoxIOS' 	=> 'fxios', 		//Mozilla FF for IOS
		//Initialize variables for mobile-specific content.
		'vndwap' 		=> 'vnd.wap',
		'wml' 			=> 'wml',
		//Initialize variables for other random devices and mobile browsers.
		//It might be cheeky to call consoles 'mobile', but we do.
		'deviceBrew' 		=> 'brew',
		'deviceDanger' 		=> 'danger',
		'deviceHiptop' 		=> 'hiptop',
		'devicePlaystation' 	=> 'playstation',
		'deviceNintendoDs' 	=> 'nitro',
		'deviceNintendo' 	=> 'nintendo',
		'deviceWii' 		=> 'wii',
		'deviceXbox' 		=> 'xbox',
		'deviceArchos' 		=> 'archos',
		'engineNetfront' 	=> 'netfront', 		//Common embedded OS browser
		'engineUpBrowser' 	=> 'up.browser', 	//common on some phones
		'engineOpenWeb' 	=> 'openweb', 		//Transcoding by OpenWave server
		'deviceMidp' 		=> 'midp', 		//a mobile Java technology
		'uplink' 		=> 'up.link',
		'engineTelecaQ' 	=> 'teleca q', 		//a modern feature phone browser
		'vendorXiaomi' 		=> 'miui',        	//popular Chinese mobile browser
		'vendorUCWeb' 		=> 'ucbrowser',   	//another Chinese mobile browser
		'vendorCloudMosa'	=> 'puffin', 		//Puffin Android optimized browser
		//Generics
		'devicePda' 	=> 'pda', 	//some devices report themselves as PDAs
		'mini' 		=> 'mini',  	//Some mobile browsers put 'mini' in their names.
		'mobile' 	=> 'mobile', 	//Some mobile browsers put 'mobile' in their user agent strings.
		'mobi' 		=> 'mobi', 	//Some mobile browsers put 'mobi' in their user agent strings.
		'tablet' 	=> 'tablet', 	//Some mobile browsers put 'tablet' in their user agent strings.
		//Nokia's Internet Tablets.
		'maemo' 	=> 'maemo',
		'qtembedded' 	=> 'qt embedded', 	//for Sony Mylo and others
		'mylocom2' 	=> 'com2', 		//for Sony Mylo also
		//In some UserAgents, the only clue is the manufacturer.
		'manuSonyEricsson' 	=> 'sonyericsson',
		'manuericsson'		=> 'ericsson',
		'manuSamsung1' 		=> 'sec-sgh',
		'manuSony' 		=> 'sony',
		'manuHtc' 		=> 'htc', 	//Popular Android and WinMo manufacturer
		//In some UserAgents, the only clue is the operator.
		'svcDocomo' 		=> 'docomo',
		'svcKddi' 		=> 'kddi',
		'svcVodafone' 		=> 'vodafone',
	);

	$find = implode('\b|', array_diff($mobileStrings, $genericStrings));
	if(preg_match_all('~(' . $find . '\b)~i', $useragent, $tmp))
	{
		$context['MobileDevice'] = array(
			'isMobile' => true,
		);
	}

	if ($device)
		return $context['MobileDevice']['device'];
	else
		return $context['MobileDevice']['isMobile'];
}

?>
