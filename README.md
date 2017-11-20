[![Build Status](https://travis-ci.org/UWEnrollmentManagement/Aliro.svg?branch=master)](https://travis-ci.org/UWEnrollmentManagement/Aliro)
[![Latest Stable Version](https://poser.pugx.org/uwdoem/aliro/v/stable)](https://packagist.org/packages/uwdoem/aliro)

UWDOEM/Aliro
=============

Aliro is a Middleware class for the Slim.php framework. It provides user authorization through the UW Group Web Service.

It is easy to add to a Slim.php application. Simply register Aliro as middleware, and provide a list of secured endpoints and the UW groups that are authorized to access them. Visitors will be screened for membership, and if they are not found within the provided groups, they will recieve a 401 reponse, without any content from your application.

```
$aliro_settings = array(
    'permissions' => array(
        '/post/{name}' => ['u_bonifacp_test_user', 'u_bonifacp_test_admin'],
        '/admin/{name}' => ['u_bonifacp_test_admin']
    )
);

$app = new \Slim\App(...);

$app->add(new Aliro($aliro_settings));
```

Notice
------

This is *not* an official library, endorsed or supported by any party who manages or owns information accessed via GWS. This library is *not* endorsed or supported by the University of Washington Department of Enrollment Management.

Installation
------------

This library is published on packagist. To install using Composer, add the `"uwdoem/aliro": "1.*"` line to your "require" dependencies:

```
{
    "require": {
        "uwdoem/aliro": "1.*"
    }
}
```

Of course it is possible to use *Aliro* without Composer by downloading it directly, but use of Composer to manage packages is highly recommended. See [Composer](https://getcomposer.org/) for more information.

Use
---

Once installed, you will need to provide several pieces of configuration information, before registering Aliro as middleware for you Slim application.

First you will have to define the required settings for the Group Web Service

```
    // Intialize the required settings
    define('UW_GWS_BASE_PATH', '/path/to/my/private.key');
    define('UW_GWS_SSL_KEY_PATH', '/path/to/my/private.key');
    define('UW_GWS_SSL_CERT_PATH', '/path/to/my/public_cert.pem');
    define('UW_GWS_SSL_KEY_PASSWD', 'myprivatekeypassword');  // Can be blank for no password: ''
    define('UW_GWS_VERBOSE', false);  // (Optional) Whether to include verbose cURL messages in error messages.
```

You will also need to define the similar (but distinct) settings for the Person Web Service, Aliro relies on both systems.

```
    // Intialize the required settings
    define('UW_WS_BASE_PATH', '/path/to/my/private.key');
    define('UW_WS_SSL_KEY_PATH', '/path/to/my/private.key');
    define('UW_WS_SSL_CERT_PATH', '/path/to/my/public_cert.pem');
    define('UW_WS_SSL_KEY_PASSWD', 'myprivatekeypassword');  // Can be blank for no password: ''
    define('UW_WS_VERBOSE', false);  // (Optional) Whether to include verbose cURL messages in error messages.
```

Finally, you will need to provide settings specific to Aliro, and then register the middleware for your application.

```
$aliro_settings = array(
    'permissions' => array(
        '/post/{name}' => ['u_bonifacp_test_user', 'u_bonifacp_test_admin'],
        '/admin/{name}' => ['u_bonifacp_test_admin']
    ),
    'deniedHandler' => null
);

$app = new \Slim\App(...);

$app->add(new Aliro($aliro_settings));
```

That's all there is to it. Aliro will check the groups of the current user each time they request an end-point that you have listed. If the user is a member of one of the listed groups, the endpoint is allowed to resolve. If they are not a member, a 401 is returned, along with an error JSON object. 

```
//Default permissions denied response
{
  "success":false,
  "status":401,
  "previous":null,
  "current":"http:\/\/dev2.admit.washington.edu\/aliro\/public\/GWS.php\/admin\/jim",
  "next":null,
  "data":null,
  "time":"2017-11-20 02:13:37 pm",
  "error":null
}
```

You can define a custom permission denied handler in the initial settings, which will be called instead of returning JSON.

Unlisted end-points will be accessible by default. Aliro will not do any checking without a permissions rule in place.


Requirements
------------

* PHP 7.0
* uwdoem/connection 3.*
* uwdoem/group ^1.0.10
* uwdoem/person ^1.5
