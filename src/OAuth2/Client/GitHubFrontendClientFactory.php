<?php

declare(strict_types=1);

/*
 * This file is part of Contao GitHub Login.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-github-login
 */

namespace Markocupic\ContaoGitHubLogin\OAuth2\Client;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Github;
use Markocupic\ContaoOAuth2Client\OAuth2\Client\ClientFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class GitHubFrontendClientFactory implements ClientFactoryInterface
{
    public const NAME = 'github_frontend';
    private const PROVIDER = 'github';
    private const FIREWALL = 'contao_frontend';

    public function __construct(
        private readonly RouterInterface $router,
        private readonly array $config,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getProvider(): string
    {
        return self::PROVIDER;
    }

    public function getContaoFirewall(): string
    {
        return self::FIREWALL;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getConfigByKey(string $key): mixed
    {
        if (!isset($this->config[$key])) {
            throw new \Exception(sprintf('Invalid key "%s" selected. Did you mean one of these: "%s"?', $key, implode('", "', array_keys($this->config))));
        }

        return $this->config[$key];
    }

    public function createClient(array $options = []): AbstractProvider
    {
        $options = array_merge($this->getConfig(), $options);

        // Add required options to the provider constructor
        $opt = [];
        $opt['clientSecret'] = $options['client_secret'];
        $opt['clientId'] = $options['client_id'];
        $opt['redirectUri'] = $this->router->generate($options['redirect_route'], ['_oauth2_client' => $this->getName()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new Github($opt, []);
    }
}
