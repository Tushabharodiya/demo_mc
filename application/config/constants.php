<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
|--------------------------------------------------------------------------
| Panel Setting
|--------------------------------------------------------------------------
*/

/* AWS Setting */
define("S3_REGION", "ap-south-1");
define("S3_SECRET", "135AegeYZSSn6kHOU0ospt8WdFCD6NIpzSubiIZD");
// define("BUCKET_NAME", "aws-mcpe-bucket");
define("BUCKET_NAME", "new-demo-bucket-one");
define("BUCKET_NAME_TWO", "new-demo-bucket-two");

// Session Settings
define("SESSION_TABLE", "ci_sessions");

// IMAGES NAMES
define("DATA_IMAGE", "data-image/");
define("DATA_BUNDLE", "data-bundle/");

// AUTH KEY 
define("AUTH_KEY", "2569854532568554123");

/* Common Setting */
define("OTP", "admin");

/* Database Setting */
define("URL", "https://syphnosys.com/mcpe/");
define("HOST", "localhost");
define("USER", "syphnosy_root");
define("PASS", "SYS@dev#web#cloud#12");
define("DB", "syphnosys_mcpe");

// TABLE NAME
define("ENCRYPT_TABLE", "mcpe_encrypt");
define("MEMBER_TABLE", "mcpe_member");
define("MCPE_CATEGORY_MODS", "mcpe_category_mods");
define("MCPE_CATEGORY_ADDONS", "mcpe_category_addons");
define("MCPE_CATEGORY_MAPS", "mcpe_category_maps");
define("MCPE_CATEGORY_SEEDS", "mcpe_category_seeds");
define("MCPE_CATEGORY_TEXTURES", "mcpe_category_textures");
define("MCPE_CATEGORY_SHADERS", "mcpe_category_shaders");
define("MCPE_CATEGORY_SKIN", "mcpe_category_skin");
define("MCPE_MODS", "mcpe_mods");
define("MCPE_ADDONS", "mcpe_addons");
define("MCPE_MAPS", "mcpe_maps");
define("MCPE_SEEDS", "mcpe_seeds");
define("MCPE_TEXTURES", "mcpe_textures");
define("MCPE_SHADERS", "mcpe_shaders");
define("MCPE_SKIN", "mcpe_skin");
define("MCPE_SEARCH_DATA", "mcpe_search_data");

define("APP_DATA_TABLE", "app_data");
define("APP_NOTIFICATION_TABLE", "app_notification");







	