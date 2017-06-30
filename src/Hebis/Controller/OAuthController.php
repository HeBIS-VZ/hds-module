<?php
/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an 
 * extension of the open source library search engine VuFind, that 
 * allows users to search and browse beyond resources. More 
 * Information about VuFind you will find on http://www.vufind.org
 * 
 * Copyright (C) 2016 
 * HeBIS Verbundzentrale des HeBIS-Verbundes 
 * Goethe-Universität Frankfurt / Goethe University of Frankfurt
 * http://www.hebis.de
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace Hebis\Controller;

use Hebis\Db\Table\UserOAuth as UserOAuthTable;
use Hebis\Db\Row\UserOAuth as UserOAuthRow;
use League\OAuth2\Client\Provider\GenericProvider;
use VuFind\Controller\AbstractBase;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Container;
use Zend\Session\SessionManager;

/**
 * Class OAuthController
 * @package Hebis\Controller
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class OAuthController extends AbstractBase
{


    private $provider;


    private $sessionOAuth;

    /**
     * SessionManager
     *
     * @var SessionManager
     */
    protected $sessionManager;

    public function init()
    {
        //parent::init();
        $config = $this->getConfig('HEBIS');
        $this->sessionManager = new SessionManager();

        if (null === $this->sessionOAuth) {
            $this->sessionOAuth = new Container('PAIA', $this->sessionManager);
        }

        $this->provider = new GenericProvider([
            'clientId' => $config['PAIA']['client_id'],    // The client ID assigned to you by the provider
            'clientSecret' => $config['PAIA']['client_secret'],   // The client password assigned to you by the provider
            'redirectUri' => $config['PAIA']['callback_url'],
            'urlAuthorize' => $config['PAIA']['baseUrl'] . 'oauth/v2/auth',
            'urlAccessToken' => $config['PAIA']['baseUrl'] . 'oauth/v2/token',
            'urlResourceOwnerDetails' => $config['PAIA']['baseUrl'] . 'core/',
            'scopes' => 'read_patron read_fees read_items write_items',
        ]);
    }

    public function renewAction()
    {
        return $this->redirect()->toUrl($this->provider->getAuthorizationUrl());
    }

    public function callbackAction()
    {
        $state = $this->params()->fromQuery('state');
        $code = $this->params()->fromQuery('code');

        if (empty($code)) {
            throw new \OAuthException("Invalid or empty authorize code.");
        }
        //$oauth2state = unserialize($_SESSION['oauth2state']);

        $oauth2state = unserialize($this->sessionOAuth->oauth2state);

        if ($oauth2state !== $state) {
            //throw new \OAuthException("Invalid state.");
        }


        // Try to get an access token using the authorization code grant.
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
            'scope' => "read_patron read_fees read_items write_items"
        ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.


        /** @var UserOAuthTable $table */
        $table = $this->getTable('user_oauth');
        //$user = $this->getUser();

        //$session = $this->getSession();
        $user = $this->getUser();

        /** @var UserOAuthRow $userOAuthRow */
        $userOAuthRow = $table->getByUsername($user->username);
        $expires = new \DateTime();
        $expires->setTimestamp($accessToken->getExpires());


        $config = $this->getConfig('HEBIS');

        $userOAuthRow->user_id = $user->id;
        $userOAuthRow->username = $user->username;
        $userOAuthRow->access_token = $accessToken->getToken();
        $userOAuthRow->refresh_token = $accessToken->getRefreshToken();
        $userOAuthRow->expires = $expires->format("Y-m-d H:i:s");
        $userOAuthRow->provider = $config['PAIA']['callback_url'];

        $userOAuthRow->save();

        return $this->redirect()->toRoute('my-research');

        /*

        // If we don't have an authorization code then get one
        if (!isset($content)) {

            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            $authorizationUrl = $this->provider->getAuthorizationUrl();

            // Get the state generated for you and store it to the session.

            $this->sessionOAuth->append(['state' => $this->provider->getState()]);
            // Redirect the user to the authorization URL.
            $this->redirect()->toUrl($authorizationUrl);

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            throw new Auth("Invalid OAuth state");

        } else {

            try {

                // Try to get an access token using the authorization code grant.
                $accessToken = $this->provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                // We have an access token, which we may use in authenticated
                // requests against the service provider's API.
                echo $accessToken->getToken() . "\n";
                echo $accessToken->getRefreshToken() . "\n";
                echo $accessToken->getExpires() . "\n";
                echo ($accessToken->hasExpired() ? 'expired' : 'not expired') . "\n";

                // Using the access token, we may look up details about the
                // resource owner.
                $resourceOwner = $this->provider->getResourceOwner($accessToken);

                var_export($resourceOwner->toArray());

                // The provider provides a way to get an authenticated API request for
                // the service, using the access token; it returns an object conforming
                // to Psr\Http\Message\RequestInterface.
                $request = $this->provider->getAuthenticatedRequest(
                    'GET',
                    'http://localhost:8000/core/',
                    $accessToken
                );

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

                // Failed to get the access token or user details.
                exit($e->getMessage());

            }

        }
        */
    }

    protected function getSession()
    {
        // SessionContainer not defined yet? Build it now:
        if (null === $this->sessionOAuth) {
            $this->sessionOAuth = new Container('PAIA', $this->sessionManager);
        }
        return $this->sessionOAuth;
    }
}
