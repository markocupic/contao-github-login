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

namespace Markocupic\ContaoGitHubLogin\Util;

final class AuthenticatorUtil
{
    public function maskEmail(string $email): string
    {
        return substr($email, 0, 3).'****'.substr($email, strpos($email, '@'));
    }
}
