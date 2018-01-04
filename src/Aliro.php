<?php
namespace UWDOEM\Aliro;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use UWDOEM\Person\Person;
use UWDOEM\Group\Group;

/**
 * Middleware for the Slim.php framework. Handles authorization against the UW Group Web Service.
 *
 * @package UWDOEM\Aliro
 */
class Aliro
{
    /** @var array */
    protected $appPermissions = [];

    /** @var array */
    protected $userGroups = [];

    /** @var function */
    protected $deniedHandler = null;

    /** @var Response */
    protected $response = null;

    /** @var Request */
    protected $request = null;

    /** @var mixed */
    protected $next = null;

    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {

        $this->appPermissions = $settings['permissions'];
        $this->deniedHandler = $settings['deniedHandler'];
    }

    /**
     * @param object $request
     * @param object $response
     * @param object $next
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $this->request = $request;
        $this->response = $response;
        $this->next = $next;

        session_start();

        //Check if current user matches session. If not, clear session.
        if (
            isset($_SERVER['uwnetid']) === true
            && (isset($_SESSION['aliro']) === false || $_SERVER['uwnetid'] !== $_SESSION['aliro']['uwnetid'])
        ) {
            unset($_SESSION['aliro']);
            $_SESSION['aliro'] = array();
            $_SESSION['aliro']['uwnetid'] = $_SERVER['uwnetid'];
        }

        if (isset($_SESSION['aliro']['userGroups']) === true) {
            $this->userGroups = $_SESSION['aliro']['userGroups'];
        }

        $route = $this->request->getAttribute('route');
        $routeName = $route->getPattern();

        $letUserThrough = false;

        // Get Person
        $p = Person::fromIdentifier("uwnetid", $_SESSION['aliro']['uwnetid']);

        // Check Person exists
        if (isset($p) === true) {
            $endpoint = $routeName;
            $allowedGroups = $this->appPermissions[$endpoint];

            // Loop through the groups with permissions, checking if user is in any of them
            foreach ($allowedGroups as $perm) {
                // Check if we have a cached record of this user's group membership. If not go ahead and query the GWS
                if (in_array($perm, $this->userGroups)=== true) {
                    // All good, user had permission
                    $letUserThrough = true;
                    break;
                } else {
                    $g = new Group($perm);

                    //Check that group exists
                    if ((isset($g) === true) && (empty($g->getRegId()) === false)) {
                        $members = $g->getMembers();

                        if (in_array($_SESSION['aliro']['uwnetid'], $members) === true) {
                            // Save group id to session list, so we don't have to use GWS more than necessary.
                            // This does mean lockouts will not function until the session expires.
                            $_SESSION['aliro']['userGroups'][] = $perm;

                            // Let request through
                            $letUserThrough = true;
                            break;
                        }
                    } else {
                        trigger_error("Group $perm not found", E_USER_WARNING);
                    }
                }
            }
        } else {
            trigger_error("User " . $_SESSION['aliro']['uwnetid'] . " not found", E_USER_WARNING);
        }

        // Decide what to return
        if ($letUserThrough === true) {
            $this->response = call_user_func($this->next, $this->request, $this->response);

            return $this->response;
        } else {
            //permissionDenied sets the response
            $this->permissionDenied();

            //Deny request
            return $this->response;
        }
    }

    /**
     * Sets the Slim response, clearing out anything previously added.
     *
     * @return void
     */
    public function permissionDenied()
    {
        if ($this->deniedHandler === null) {
            $data = array(
                "success" => false,
                "status" => 401,
                "previous" => null,
                "current" => "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                "next" => null,
                "data" => null,
                "time" => date('Y-m-d h:i:s a'),
                "error" => null
            );
            $this->response = $this->response->withJson($data, 401);
        } else {
            call_user_func($this->deniedHandler);
        }
    }
}
