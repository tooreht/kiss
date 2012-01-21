<?php

/** Configuration Variables **/

/** Debug */

define ('DEVELOPMENT_ENVIRONMENT',true);

/** Database */

define('DB_NAME', 'framework');
define('DB_USER', 'framework');
define('DB_PASSWORD', 'framework');
define('DB_HOST', 'localhost');
define('DB_PORT', NULL);
define('DB_SOCKET', NULL);

/** Site */

define('SITE_ROOT' , 'http://tooreht.net/kiss');

/** View */

define('PAGINATE_LIMIT', '5');


/** 
 * Session Handler
 * 
 * for more information
 * @see SessionHandler
 */

// required settings
define('SECURITY_CODE', 'aSiAe6U5?E2O*' );
define('LOCK_TIMEOUT', 60);
define('SESSION_TABLE', 'session');

// (optional) use 'default' to keep settings from the php.ini
define('SESSION_LIFETIME', 'default');
define('GC_PROBABILITY', 10);
define('GC_DIVISOR', 'default');