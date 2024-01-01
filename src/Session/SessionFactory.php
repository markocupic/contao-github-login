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

namespace Markocupic\ContaoGitHubLogin\Session;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionFactory implements SessionFactoryInterface
{
    public function __construct(
        readonly private SessionFactoryInterface $inner,
        readonly private SessionBagInterface $sessionBag,
    ) {
    }

    public function createSession(): SessionInterface
    {
        $session = $this->inner->createSession();

        $session->registerBag($this->sessionBag);

        return $session;
    }
}
