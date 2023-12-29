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

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ProviderFactory
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    public function create(Request $request): GitHub
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            $clientId = $_ENV['GITHUB_BACKEND_LOGIN_CLIENT_ID'];
            $clientSecret = $_ENV['GITHUB_BACKEND_LOGIN_CLIENT_SECRET'];
            $redirectUri = $this->router->generate('markocupic_contao_github_backend_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $clientId = $_ENV['GITHUB_FRONTEND_LOGIN_CLIENT_ID'];
            $clientSecret = $_ENV['GITHUB_FRONTEND_LOGIN_CLIENT_SECRET'];
            $redirectUri = $this->router->generate('markocupic_contao_github_frontend_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return new GitHub([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
        ]);
    }
}
