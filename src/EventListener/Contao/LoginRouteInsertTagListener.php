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

namespace Markocupic\ContaoGitHubLogin\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

#[AsHook('replaceInsertTags')]
class LoginRouteInsertTagListener
{
    public const TAG = 'github_login_url';

    public function __construct(
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

        if (!isset($chunks[1]) || ('frontend' !== $chunks[1] && 'backend' !== $chunks[1])) {
            $chunks[1] = 'frontend';
        }

        if ($chunks[1] === 'backend') {
            return $this->uriSigner->sign($this->router->generate('markocupic_contao_github_backend_login', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        return $this->uriSigner->sign($this->router->generate('markocupic_contao_github_frontend_login', [], UrlGeneratorInterface::ABSOLUTE_URL));
    }
}
