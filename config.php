<?php
/**
 * This is a file automatically generated by the Mouf framework. Do not put any code except 'define' operations
 * as it could be overwritten.
 * Instead, use the Mouf User Interface to set all your constants: http://[server]/vendor/mouf/mouf/mouf/config
 */

/**
 * The host name for the Elastic Search server
 */
define('ELASTICSEARCH_HOST', getenv('ELASTICSEARCH_HOST') !== false?getenv('ELASTICSEARCH_HOST'):'elasticsearch');
/**
 * The default port to connect to Elastic Search server
 */
define('ELASTICSEARCH_PORT', getenv('ELASTICSEARCH_PORT') !== false?getenv('ELASTICSEARCH_PORT'):'9200');
/**
 * A random string. It should be different for any application deployed.
 */
define('SECRET', getenv('SECRET') !== false?getenv('SECRET'):'HLxRssObAZpJdFYfHJpT');
/**
 * The download directory
 */
define('DOWNLOAD_DIR', getenv('DOWNLOAD_DIR') !== false?getenv('DOWNLOAD_DIR'):'/var/downloads');
/**
 * Connection string to MongoDB
 */
define('MONGODB_CONNECTIONSTRING', getenv('MONGODB_CONNECTIONSTRING') !== false?getenv('MONGODB_CONNECTIONSTRING'):'mongodb://mongo:27017');
/**
 * Your Google Analytics key. Leave empty if you want to disable Google Analytics tracking. Don't have a key for your website? Get one here: http://www.google.com/analytics/
 */
define('GOOGLE_ANALYTICS_KEY', getenv('GOOGLE_ANALYTICS_KEY') !== false?getenv('GOOGLE_ANALYTICS_KEY'):'');
/**
 * The base domain name to track (if you are tracking sub-domains). In the form: '.example.com'. Keep this empty if you don't track subdomains.
 */
define('GOOGLE_ANALYTICS_DOMAIN_NAME', getenv('GOOGLE_ANALYTICS_DOMAIN_NAME') !== false?getenv('GOOGLE_ANALYTICS_DOMAIN_NAME'):'');
/**
 * Set to true to enable debug/development mode.
 */
define('DEBUG', getenv('DEBUG') !== false?filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN):true);
