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

use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Github;
use Markocupic\ContaoOAuth2Client\Event\CreateOAauth2ProviderEvent;
use Markocupic\ContaoOAuth2Client\OAuth2\Client\AbstractClientFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class GitHubBackendClientFactory extends AbstractClientFactory
{
    public const NAME = 'github_backend';
    public const PROVIDER = 'github';
    public const CONTAO_FIREWALL = 'contao_backend';

    public function __construct(
        ContaoFramework $framework,
        Connection $connection,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly RouterInterface $router,
        protected array $config,
    ) {
        parent::__construct($framework, $connection);
    }

    public function createClient(Request $request, array $options = []): AbstractProvider
    {
        $options = array_merge($this->getConfig(), $options);

        // Add required options to the provider constructor
        $opt = [];
        $opt['clientSecret'] = $options['client_secret'];
        $opt['clientId'] = $options['client_id'];
        $opt['redirectUri'] = $this->router->generate($this->getRedirectRoute(), ['_oauth2_client' => $this->getName()], UrlGeneratorInterface::ABSOLUTE_URL);

        $client = new Github($opt, []);

        // Allow modifications on the OAuth2 client using event listeners or event subscribers
        $event = new CreateOAauth2ProviderEvent($request, $client, $opt);
        $this->eventDispatcher->dispatch($event);

        return $event->getClient();
    }
}
