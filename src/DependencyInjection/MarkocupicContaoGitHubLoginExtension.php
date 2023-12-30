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

namespace Markocupic\ContaoGitHubLogin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class MarkocupicContaoGitHubLoginExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return Configuration::ROOT_KEY;
    }

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );

        $loader->load('services.yaml');

        $rootKey = $this->getAlias();
        // Backend client
        $container->setParameter($rootKey.'.clients.backend.disable_contao_login', $config['clients']['backend']['disable_contao_login']);
        $container->setParameter($rootKey.'.clients.backend.enable_github_login', $config['clients']['backend']['enable_github_login']);
        $container->setParameter($rootKey.'.clients.backend.enable_csrf_token_check', $config['clients']['backend']['enable_csrf_token_check']);
        $container->setParameter($rootKey.'.clients.backend.redirect_route', $config['clients']['backend']['redirect_route']);
        $container->setParameter($rootKey.'.clients.backend.client_id', $config['clients']['backend']['client_id']);
        $container->setParameter($rootKey.'.clients.backend.client_secret', $config['clients']['backend']['client_secret']);
        // Frontend client
        $container->setParameter($rootKey.'.clients.frontend.enable_github_login', $config['clients']['frontend']['enable_github_login']);
        $container->setParameter($rootKey.'.clients.frontend.enable_csrf_token_check', $config['clients']['frontend']['enable_csrf_token_check']);
        $container->setParameter($rootKey.'.clients.frontend.redirect_route', $config['clients']['frontend']['redirect_route']);
        $container->setParameter($rootKey.'.clients.frontend.client_id', $config['clients']['frontend']['client_id']);
        $container->setParameter($rootKey.'.clients.frontend.client_secret', $config['clients']['frontend']['client_secret']);
    }
}
