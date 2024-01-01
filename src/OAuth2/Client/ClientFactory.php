<?php

declare(strict_types=1);

/*
 * This file is part of Contao GitHub Authenticator.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-github-login
 */

namespace Markocupic\ContaoGitHubLogin\OAuth2\Client;

use League\OAuth2\Client\Provider\Github;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class ClientFactory
{
    public function __construct(
        private RouterInterface $router,
        private ClientRegistry $clientRegistry,
    ) {
    }

    public function create(string $clientName): Github
    {
        $config = $this->clientRegistry->getClientConfigFor($clientName);
        $oauth2ProviderType = 'github';
        $redirectUrl = $this->router->generate($config['redirect_route'], ['_oauth2_provider_type' => $oauth2ProviderType], UrlGeneratorInterface::ABSOLUTE_URL);

        return new Github([
            'clientId' => $config['client_id'],
            'clientSecret' => $config['client_secret'],
            'redirectUri' => $redirectUrl,
        ]);
    }
}
