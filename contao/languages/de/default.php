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
$GLOBALS['TL_LANG']['GITHUB_LOGIN_MSC']['loginWithGitHub'] = 'GitHub Login';
$GLOBALS['TL_LANG']['GITHUB_LOGIN_MSC']['or'] = 'oder';

/*
 * Errors
 */
$GLOBALS['TL_LANG']['GITHUB_LOGIN_ERR']['userWithEmailAddressNotFound'] = 'Es wurde kein Benutzer mit der Email-Adresse "%s" in der Datenbank gefunden.';
$GLOBALS['TL_LANG']['GITHUB_LOGIN_ERR']['identityProviderException'] = 'Zugriff auf GitHub Benutzer aufgrund eines Konfigurationsfehlers nicht möglich. Bitte überprüfen Sie die GitHub App Einstellungen.';
//$GLOBALS['TL_LANG']['GITHUB_LOGIN_ERR']['identityProviderException'] = 'GitHub user not accessible by integration. Please check your GitHub App settings.';
