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

namespace Markocupic\ContaoGitHubLogin\OAuth2\Client;

use Markocupic\ContaoGitHubLogin\OAuth2\Client\Exception\ClientNotFoundException;

readonly class ClientRegistry
{
    public function __construct(
        private array $clients,
    ) {
    }

    /**
     * @return array['client_id' => string, 'client_secret' => string, 'redirect_route' => string, 'enable_github_login' => bool]
     */
    public function getClientConfigFor(string $clientName): array
    {
        if (empty($this->clients[$clientName]) || !\is_array($this->clients[$clientName])) {
            throw new ClientNotFoundException(sprintf('Client with name: "%s" not found. Did you mean one of these? %s', $clientName, implode(', ', array_keys($this->clients))));
        }

        return $this->clients[$clientName];
    }

    /**
     * @return array<string>
     */
    public function getAvailableClients(): array
    {
        return array_keys($this->clients);
    }
}
