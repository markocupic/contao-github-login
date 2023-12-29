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

/*
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['GITHUB_LOGIN_MSC']['loginWithGitHub'] = 'GitHub login';
$GLOBALS['TL_LANG']['GITHUB_LOGIN_MSC']['or'] = 'or';

/*
 * Errors
 */
$GLOBALS['TL_LANG']['GITHUB_LOGIN_ERR']['userWithEmailAddressNotFound'] = 'No user with the email address "%s" was found in the database.';
$GLOBALS['TL_LANG']['GITHUB_LOGIN_ERR']['identityProviderException'] = 'GitHub user not accessible by integration. Please check your GitHub App settings.';
