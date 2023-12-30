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
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('replaceInsertTags')]
class FailurePathInsertTagListener
{
    public const TAG = 'github_login_failure_path';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(string $tag): false|string
    {
        $chunks = explode('::', $tag);

        if (empty($chunks) || self::TAG !== $chunks[0]) {
            return false;
        }

        return base64_encode($this->requestStack->getCurrentRequest()->getUri());
    }
}
