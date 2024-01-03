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

namespace Markocupic\ContaoGitHubLogin\Security\UserMatcher;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Markocupic\ContaoOAuth2Client\Security\UserMatcher\AbstractUserMatcher;
use Markocupic\ContaoOAuth2Client\Security\UserMatcher\UserMatcherInterface;

class UserMatcher extends AbstractUserMatcher implements UserMatcherInterface
{
    private string $resOwnerIdentifierKey = 'email';
    private string $contaoUserIdentifierKey = 'email';

    private array $oauth2ClientsSupported = [
        'github_frontend',
        'github_backend',
    ];

    public function __construct(
        protected readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    /**
     * @return array<string>
     */
    public function supports(): array
    {
        return $this->oauth2ClientsSupported;
    }

    public function getResourceOwnerIdentifierKey(): string
    {
        return $this->resOwnerIdentifierKey;
    }

    public function getContaoUserIdentifierKey(): string
    {
        return $this->contaoUserIdentifierKey;
    }
}
