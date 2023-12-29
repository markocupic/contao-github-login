<?php

declare(strict_types=1);

/*
 * This file is part of Contao GitHub Authenticator.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-github-login
 */

namespace Markocupic\ContaoGitHubLogin\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\Provider\Exception\GitHubIdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class GitHub extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Domain.
     */
    public string $domain = 'https://github.com';

    /**
     * Api domain.
     */
    public string $apiDomain = 'https://api.github.com';

    /**
     * Get authorization url to begin OAuth flow.
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->domain.'/login/oauth/authorize';
    }

    /**
     * Get access token url to retrieve token.
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->domain.'/login/oauth/access_token';
    }

    /**
     * Get provider url to fetch user details.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        if ('https://github.com' === $this->domain) {
            return $this->apiDomain.'/user';
        }

        return $this->domain.'/api/v3/user';
    }

    public function getState()
    {
        return parent::getState();
    }

    /**
     * @throws IdentityProviderException
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $response = parent::fetchResourceOwnerDetails($token);

        if (empty($response['email'])) {
            $url = $this->getResourceOwnerDetailsUrl($token).'/emails';

            $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);

            $responseEmail = $this->getParsedResponse($request);

            $response['email'] = $responseEmail[0]['email'] ?? null;
        }

        return $response;
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     */
    protected function getDefaultScopes(): array
    {
        return [
            'user.email',
        ];
    }

    /**
     * Check a provider response for errors.
     *
     * @see   https://developer.github.com/v3/#client-errors
     * @see   https://developer.github.com/v3/oauth/#common-errors-for-the-access-token-request
     *
     * @param array|string $data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw GitHubIdentityProviderException::clientException($response, $data);
        }

        if (isset($data['error'])) {
            throw GitHubIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        $user = new GitHubResourceOwner($response);

        return $user->setDomain($this->domain);
    }
}
