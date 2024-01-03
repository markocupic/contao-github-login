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

namespace Markocupic\ContaoGitHubLogin\ButtonGenerator;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\Client;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\GitHubBackendClientFactory;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\GitHubFrontendClientFactory;
use Markocupic\ContaoOAuth2Client\ButtonGenerator\ButtonGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

final class ButtonGenerator implements ButtonGeneratorInterface
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly RequestStack $requestStack,
        private readonly Environment $twig,
    ) {
    }

    /**
     * Return all supported oauth clients
     * e.g. ['github_backend','github_frontend'].
     *
     * @return array<string>
     */
    public function supports(): array
    {
        return [GitHubBackendClientFactory::NAME, GitHubFrontendClientFactory::NAME];
    }

    public function renderButton(string $clientName): string
    {
        return $this->twig->render(
            '@MarkocupicContaoGitHubLogin/components/_login_button.html.twig',
            [
                'client_name' => $clientName,
            ]
        );
    }
}
