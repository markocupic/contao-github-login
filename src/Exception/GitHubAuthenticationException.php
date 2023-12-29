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

namespace Markocupic\ContaoGitHubLogin\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class GitHubAuthenticationException extends AuthenticationException
{
    public const ERROR_UNEXPECTED = 0;
    public const ERROR_INVALID_OAUTH_STATE = 1;
    public const ERROR_CONTAO_BACKEND_USER_NOT_FOUND = 2;
    public const ERROR_CONTAO_FRONTEND_USER_NOT_FOUND = 3;

    public const ERROR_MAP = [
        self::ERROR_UNEXPECTED => 'Unexpected error',
        self::ERROR_INVALID_OAUTH_STATE => 'Invalid OAuth state',
        self::ERROR_CONTAO_BACKEND_USER_NOT_FOUND => 'Contao backend user not found',
        self::ERROR_CONTAO_FRONTEND_USER_NOT_FOUND => 'Contao frontend user not found',
    ];
}
