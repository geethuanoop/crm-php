<?php
/* Copyright (C) 2002-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Xavier Dutoit        <doli@sydesy.com>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2005-2013 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2005 	   Simon Tosser         <simon@kornog-computing.com>
 * Copyright (C) 2006 	   Andre Cianfarani     <andre.cianfarani@acdeveloppement.net>
 * Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('DOL_VERSION'))
    define('DOL_VERSION', '0.2.45');
if (!defined('EURO'))
    define('EURO', chr(128));

// Define syslog constants
if (!defined('LOG_DEBUG')) {
    if (function_exists("define_syslog_variables")) {
        define_syslog_variables(); // Deprecated since php 5.3.0, syslog variables no longer need to be initialized
    } else {
        // Pour PHP sans syslog (comme sous Windows)
        define('LOG_EMERG', 0);
        define('LOG_ALERT', 1);
        define('LOG_CRIT', 2);
        define('LOG_ERR', 3);
        define('LOG_WARNING', 4);
        define('LOG_NOTICE', 5);
        define('LOG_INFO', 6);
        define('LOG_DEBUG', 7);
    }
}

// Define DOL_DOCUMENT_ROOT
define('DOL_DOCUMENT_ROOT', realpath(dirname(__FILE__)));   // Filesystem core php (htdocs)

// Define MAIN_PROTOCOL
$protocol = ((!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on')?'https':'http');
define('MAIN_PROTOCOL', $protocol);

// End of common declaration part
if (defined('DOL_INC_FOR_VERSION_ERROR'))
    return;


// Define vars
$conffiletoshowshort = "conf.php";
// Define localization of conf file
$conffile = "conf/conf.php";
// Include configuration
$result = @include_once $conffile;
if (!$result && !empty($_SERVER["GATEWAY_INTERFACE"])) {    // If install not done and we are in a web session
    header("Location: install/index.php");
    exit;
}

// Force PHP error_reporting setup (Speedealing may report warning without this)
if (!empty($main_strict_mode)) {
    error_reporting(E_ALL | E_STRICT);
} else {
    if (!defined('E_DEPRECATED'))
        define('E_DEPRECATED', 0); // For PHP < 5.3.0 compatibility
    error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED));
}

// Disable php display errors
if (!empty($dolibarr_main_prod))
    ini_set('display_errors', 'Off');

// Clean parameters
// TODO deprecated
if (!empty($dolibarr_main_data_root)) {
	$dolibarr_main_data_root = trim($dolibarr_main_data_root);
	$dolibarr_main_url_root = trim($dolibarr_main_url_root);
	$dolibarr_main_url_root_alt = trim($dolibarr_main_url_root_alt);
	$dolibarr_main_document_root = realpath(dirname(__FILE__));
	$dolibarr_main_document_root_alt = trim($dolibarr_main_document_root_alt);
}


if (empty($dolibarr_main_db_port))
    $dolibarr_main_db_port = 0;  // Pour compatibilite avec anciennes configs, si non defini, on prend 'mysql'
if (empty($dolibarr_main_db_type))
    $dolibarr_main_db_type = 'mysqli'; // Pour compatibilite avec anciennes configs, si non defini, on prend 'mysql'
if (empty($dolibarr_main_db_prefix))
    $dolibarr_main_db_prefix = 'llx_';
if (empty($dolibarr_main_db_character_set))
    $dolibarr_main_db_character_set = 'utf8';
if (empty($dolibarr_main_db_collation))
    $dolibarr_main_db_collation = 'utf8_general_ci';
if (empty($dolibarr_main_db_encryption))
    $dolibarr_main_db_encryption = 0;
if (empty($dolibarr_main_db_cryptkey))
    $dolibarr_main_db_cryptkey = '';
if (empty($dolibarr_main_limit_users))
    $dolibarr_main_limit_users = 0;
if (empty($dolibarr_mailing_limit_sendbyweb))
    $dolibarr_mailing_limit_sendbyweb = 0;
if (empty($force_charset_do_notuse))
    $force_charset_do_notuse = 'UTF-8';
if (empty($main_strict_mode))
    $main_strict_mode = 0; // For debug in php strict mode



// Security: CSRF protection
// This test check if referrer ($_SERVER['HTTP_REFERER']) is same web site than Speedealing ($_SERVER['HTTP_HOST'])
// when we post forms (we allow GET to allow direct link to access a particular page).
if (!defined('NOCSRFCHECK') && empty($dolibarr_nocsrfcheck) && !empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'GET' && !empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_REFERER']) && !preg_match('/' . preg_quote($_SERVER['HTTP_HOST'], '/') . '/i', $_SERVER['HTTP_REFERER'])) {
    //print 'HTTP_POST='.$_SERVER['HTTP_HOST'].' HTTP_REFERER='.$_SERVER['HTTP_REFERER'];
    print "Access refused by CSRF protection in main.inc.php.\n";
    print "If you access your server behind a proxy using url rewriting, you might add the line \$dolibarr_nocsrfcheck=1 into your conf.php file.\n";
    die;
}
/*if (empty($dolibarr_main_couchdb_host)) {
    print '<div align="center">Speedealing setup is not yet complete.<br><br>' . "\n";
    print '<a href="install/index.php">Click here to finish Speedealing install process</a> ...</div>' . "\n";
    die;
}*/

// Define some constants
define('DOL_CLASS_PATH', 'class/');         // Filesystem path to class dir (defined only for some code that want to be compatible with old versions without this parameter)
// TODO deprecated
if (!empty($dolibarr_main_data_root)) {
	define('DOL_DATA_ROOT', $dolibarr_main_data_root);     // Filesystem data (documents)
	if (!empty($dolibarr_main_document_root_alt)) {
		define('DOL_DOCUMENT_ROOT_ALT', $dolibarr_main_document_root_alt); // Filesystem paths to alternate core php (alternate htdocs)
	}
}

// Define DOL_MAIN_URL_ROOT and DOL_URL_ROOT
$tmp = '';
$found = 0;
// TODO deprecated
if (!empty($dolibarr_main_document_root)) {
	$real_dolibarr_main_document_root = str_replace('\\', '/', realpath($dolibarr_main_document_root));
	$pathroot = $_SERVER["DOCUMENT_ROOT"];
	$paths = explode('/', str_replace('\\', '/', $_SERVER["SCRIPT_NAME"]));
	$concatpath = '';
	foreach ($paths as $tmppath) {
		if ($tmppath)
			$concatpath.='/' . $tmppath;
		//print $_SERVER["SCRIPT_NAME"].'-'.$pathroot.'-'.$concatpath.'-'.$real_dolibarr_main_document_root.'-'.realpath($pathroot.$concatpath).'<br>';
		if ($real_dolibarr_main_document_root == @realpath($pathroot . $concatpath)) {    // @ avoid warning when safe_mode is on.
			$tmp3 = $concatpath;
			//print "Found relative url = ".$tmp3;
			$found = 1;
			break;
		}
		//else print "Not found yet for concatpath=".$concatpath."<br>\n";
	}
}

if (!$found && !empty($dolibarr_main_url_root)) { // If autodetect fails (Ie: when using apache alias that point outside default DOCUMENT_ROOT.
    $tmp = $dolibarr_main_url_root;
}
else
    $tmp = MAIN_PROTOCOL . '://' . $_SERVER["SERVER_NAME"] . ((empty($_SERVER["SERVER_PORT"]) || $_SERVER["SERVER_PORT"] == 80) ? '' : ':' . $_SERVER["SERVER_PORT"]) . (!empty($tmp3) ? (preg_match('/^\//', $tmp3) ? '' : '/') . $tmp3 : '');
//print "tmp1=".$tmp1." tmp2=".$tmp2." tmp3=".$tmp3." tmp=".$tmp;

if (!empty($dolibarr_main_force_https))
    $tmp = preg_replace('/^http:/i', 'https:', $tmp);
define('DOL_MAIN_URL_ROOT', $tmp);           // URL absolute root (https://sss/dolibarr, ...)
$uri = preg_replace('/^http(s?):\/\//i', '', constant('DOL_MAIN_URL_ROOT')); // $uri contains url without http*
$suburi = strstr($uri, '/');            // $suburi contains url without domain
if ($suburi == '/')
    $suburi = '';           // If $suburi is /, it is now ''
if (!empty($dolibarr_urlrewrite) && !empty($_GET['db'])) //URL rewrite + entity in url www.speedealing.com/[entity]/...
    $suburi .= "/" . $_GET['db'];
define('DOL_URL_ROOT', $suburi);           // URL relative root ('', '/dolibarr', ...)
// Define DOL_MAIN_URL_ROOT_ALT and DOL_URL_ROOT_ALT
if (!empty($dolibarr_main_url_root_alt)) {
    $altpart = str_replace($dolibarr_main_url_root, '', $dolibarr_main_url_root_alt);
    if (!preg_match('/^\//', $altpart) && !empty($altpart)) {
        $tmp_alt = $dolibarr_main_url_root_alt;
    } // Manage case url=http://localhost/aaa and url_alt=http://localhost/aaabbb
    else
        $tmp_alt = $tmp . ((preg_match('/\/$/', $tmp) || preg_match('/^\//', $altpart)) ? '' : '/') . $altpart;
    //$tmp_alt=$dolibarr_main_url_root_alt;
    define('DOL_MAIN_URL_ROOT_ALT', $tmp_alt);                  // URL absolute root (https://sss/dolibarr/custom, ...)
    $uri = preg_replace('/^http(s?):\/\//i', '', constant('DOL_MAIN_URL_ROOT_ALT'));    // $uri contains url without http*
    $suburi = strstr($uri, '/');                   // $suburi contains url without domain
    if ($suburi == '/')
        $suburi = '';              // If $suburi is /, it is now ''
    define('DOL_URL_ROOT_ALT', $suburi);              // URL relative root ('', '/dolibarr/custom', ...)
}
// Define prefix
define('MAIN_DB_PREFIX', $dolibarr_main_db_prefix);
//print DOL_URL_ROOT.'-'.DOL_URL_ROOT_ALT;


/*
 * Define PATH to external libraries
 * To use other version than embeded libraries, define here constant to path. Use '' to use include class path autodetect.
 */
// Path to root libraries
// TODO deprecated and unused
if (!defined('ADODB_PATH')) {
    define('ADODB_PATH', (!isset($dolibarr_lib_ADODB_PATH)) ? DOL_DOCUMENT_ROOT . '/includes/adodbtime/' : (empty($dolibarr_lib_ADODB_PATH) ? '' : $dolibarr_lib_ADODB_PATH . '/'));
}
if (!defined('TCPDF_PATH')) {
    define('TCPDF_PATH', (!isset($dolibarr_lib_TCPDF_PATH)) ? DOL_DOCUMENT_ROOT . '/includes/tcpdf/' : (empty($dolibarr_lib_TCPDF_PATH) ? '' : $dolibarr_lib_TCPDF_PATH . '/'));
}
if (!defined('FPDI_PATH')) {
    define('FPDI_PATH', (!isset($dolibarr_lib_FPDI_PATH)) ? DOL_DOCUMENT_ROOT . '/includes/fpdfi/' : (empty($dolibarr_lib_FPDI_PATH) ? '' : $dolibarr_lib_FPDI_PATH . '/'));
}
if (!defined('NUSOAP_PATH')) {
    define('NUSOAP_PATH', (!isset($dolibarr_lib_NUSOAP_PATH)) ? DOL_DOCUMENT_ROOT . '/includes/nusoap/lib/' : (empty($dolibarr_lib_NUSOAP_PATH) ? '' : $dolibarr_lib_NUSOAP_PATH . '/'));
}
if (!defined('PHPEXCEL_PATH')) {
    define('PHPEXCEL_PATH', (!isset($dolibarr_lib_PHPEXCEL_PATH)) ? DOL_DOCUMENT_ROOT . '/includes/phpexcel/' : (empty($dolibarr_lib_PHPEXCEL_PATH) ? '' : $dolibarr_lib_PHPEXCEL_PATH . '/'));
}
if (!defined('GEOIP_PATH')) {
    define('GEOIP_PATH', (!isset($dolibarr_lib_GEOIP_PATH)) ? DOL_DOCUMENT_ROOT . '/includes/geoip/' : (empty($dolibarr_lib_GEOIP_PATH) ? '' : $dolibarr_lib_GEOIP_PATH . '/'));
}
if (!defined('ODTPHP_PATH')) {
    define('ODTPHP_PATH', (!isset($dolibarr_lib_ODTPHP_PATH)) ? DOL_DOCUMENT_ROOT . '/includes/odtphp/' : (empty($dolibarr_lib_ODTPHP_PATH) ? '' : $dolibarr_lib_ODTPHP_PATH . '/'));
}
if (!defined('ODTPHP_PATHTOPCLZIP')) {
    define('ODTPHP_PATHTOPCLZIP', (!isset($dolibarr_lib_ODTPHP_PATHTOPCLZIP)) ? DOL_DOCUMENT_ROOT . '/includes/odtphp/zip/pclzip/' : (empty($dolibarr_lib_ODTPHP_PATHTOPCLZIP) ? '' : $dolibarr_lib_ODTPHP_PATHTOPCLZIP . '/'));
}
if (!defined('JS_CKEDITOR')) {
    define('JS_CKEDITOR', (!isset($dolibarr_js_CKEDITOR)) ? '' : (empty($dolibarr_js_CKEDITOR) ? '' : $dolibarr_js_CKEDITOR . '/'));
}
if (!defined('JS_JQUERY')) {
    define('JS_JQUERY', (!isset($dolibarr_js_JQUERY)) ? '' : (empty($dolibarr_js_JQUERY) ? '' : $dolibarr_js_JQUERY . '/'));
}
if (!defined('JS_JQUERY_UI')) {
    define('JS_JQUERY_UI', (!isset($dolibarr_js_JQUERY_UI)) ? '' : (empty($dolibarr_js_JQUERY_UI) ? '' : $dolibarr_js_JQUERY_UI . '/'));
}
if (!defined('JS_JQUERY_FLOT')) {
    define('JS_JQUERY_FLOT', (!isset($dolibarr_js_JQUERY_FLOT)) ? '' : (empty($dolibarr_js_JQUERY_FLOT) ? '' : $dolibarr_js_JQUERY_FLOT . '/'));
}
// Other required path
if (!defined('DOL_DEFAULT_TTF')) {
    define('DOL_DEFAULT_TTF', (!isset($dolibarr_font_DOL_DEFAULT_TTF)) ? DOL_DOCUMENT_ROOT . '/includes/fonts/Aerial.ttf' : (empty($dolibarr_font_DOL_DEFAULT_TTF) ? '' : $dolibarr_font_DOL_DEFAULT_TTF));
}
if (!defined('DOL_DEFAULT_TTF_BOLD')) {
    define('DOL_DEFAULT_TTF_BOLD', (!isset($dolibarr_font_DOL_DEFAULT_TTF_BOLD)) ? DOL_DOCUMENT_ROOT . '/includes/fonts/AerialBd.ttf' : (empty($dolibarr_font_DOL_DEFAULT_TTF_BOLD) ? '' : $dolibarr_font_DOL_DEFAULT_TTF_BOLD));
}

/*
 * Include functions
 */

if (!defined('ADODB_DATE_VERSION'))
    include_once ADODB_PATH . 'adodb-time.inc.php';

if (!file_exists(DOL_DOCUMENT_ROOT . "/core/lib/functions.lib.php")) {
    print "Error: Speedealing config file content seems to be not correctly defined.<br>\n";
    print "Please run speedealing setup by calling page <b>/install</b>.<br>\n";
    exit;
}

// Included by default
include_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';
include_once DOL_DOCUMENT_ROOT . '/core/lib/security.lib.php';
//print memory_get_usage();
// If password is encoded, we decode it
// TODO deprecated
if (!empty($dolibarr_main_db_pass)) {
	if (preg_match('/crypted:/i', $dolibarr_main_db_pass) || !empty($dolibarr_main_db_encrypted_pass)) {
		if (preg_match('/crypted:/i', $dolibarr_main_db_pass)) {
			$dolibarr_main_db_pass = preg_replace('/crypted:/i', '', $dolibarr_main_db_pass);
			$dolibarr_main_db_pass = dol_decode($dolibarr_main_db_pass);
			$dolibarr_main_db_encrypted_pass = $dolibarr_main_db_pass; // We need to set this as it is used to know the password was initially crypted
		}
		else
			$dolibarr_main_db_pass = dol_decode($dolibarr_main_db_encrypted_pass);
	}
}
?>
