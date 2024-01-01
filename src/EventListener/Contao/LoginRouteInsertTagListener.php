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

namespace Markocupic\ContaoGitHubLogin\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Markocupic\ContaoGitHubLogin\Controller\GitHubLoginStartController;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

#[AsHook('replaceInsertTags')]
class LoginRouteInsertTagListener
{
    public const TAG = 'github_login_url';

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RouterInterface $router,
        private readonly UriSigner $uriSigner,
    ) {
    }

    public function __invoke(string $tag)
    {
        $chunks = explode('::', $tag);

        if (self::TAG !== $chunks[0]) {
            return false;
        }

        $clients = $this->clientRegistry->getAvailableClients();

        if (empty($chunks[1]) || !\in_array($chunks[1], $clients, true)) {
            return false;
        }

        $availableRoutes = [
            'backend' => GitHubLoginStartController::LOGIN_ROUTE_BACKEND,
            'frontend' => GitHubLoginStartController::LOGIN_ROUTE_FRONTEND,
        ];

        $clientName = $chunks[1];
        $oauth2ProviderType = 'github';

        return $this->uriSigner->sign($this->router->generate($availableRoutes[$clientName], ['_oauth2_provider_type' => $oauth2ProviderType], UrlGeneratorInterface::ABSOLUTE_URL));
    }
}
