<?php

namespace UWDOEM\Group\Test;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') &&
    class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}
use PHPUnit_Framework_TestCase;

require './vendor/autoload.php';

// Intialize the required settings
define('UW_GWS_BASE_PATH', '');
define('UW_GWS_SSL_KEY_PATH', '');
define('UW_GWS_SSL_CERT_PATH', '');
define('UW_GWS_SSL_KEY_PASSWD', '');  // Can be blank for no password: ''
define('UW_GWS_VERBOSE', true);  // (Optional) Whether to include verbose cURL messages in error messages.


class AliroTest extends \PHPUnit\Framework\TestCase
{

    public function testParseGroup()
    {


    }

}

