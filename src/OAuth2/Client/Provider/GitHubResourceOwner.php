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

namespace Markocupic\ContaoGitHubLogin\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class GitHubResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * Domain.
     */
    protected string $domain;

    /**
     * Raw response.
     */
    protected array $response;

    /**
     * Creates new resource owner.
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id.
     */
    public function getId(): int|null
    {
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * Get resource owner email.
     */
    public function getEmail(): string|null
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * Get resource owner name.
     */
    public function getName(): string|null
    {
        return $this->getValueByKey($this->response, 'name');
    }

    /**
     * Get resource owner nickname.
     */
    public function getNickname(): string|null
    {
        return $this->getValueByKey($this->response, 'login');
    }

    /**
     * Get resource owner url.
     */
    public function getUrl(): string|null
    {
        $urlParts = array_filter([$this->domain, $this->getNickname()]);

        return \count($urlParts) ? implode('/', $urlParts) : null;
    }

    /**
     * Set resource owner domain.
     */
    public function setDomain(string $domain): ResourceOwnerInterface
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Return all the owner details available as an array.
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
