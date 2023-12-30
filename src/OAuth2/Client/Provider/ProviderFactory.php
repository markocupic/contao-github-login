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

use Markocupic\ContaoGitHubLogin\OAuth2\Client\ClientRegistry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ProviderFactory
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly ClientRegistry $clientRegistry,
    ) {
    }

    public function create(string $clientName): GitHub
    {
        $config = $this->clientRegistry->getClientConfigFor($clientName);

        return new GitHub([
            'clientId' => $config['client_id'],
            'clientSecret' => $config['client_secret'],
            'redirectUri' => $this->router->generate($config['redirect_route'], [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
}
