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
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_backend', $config['contao_oauth2_clients']['github_backend']);
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_backend.enable_login', $config['contao_oauth2_clients']['github_backend']['enable_login']);
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_backend.redirect_route', $config['contao_oauth2_clients']['github_backend']['redirect_route']);
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_backend.client_id', $config['contao_oauth2_clients']['github_backend']['client_id']);
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_backend.client_secret', $config['contao_oauth2_clients']['github_backend']['client_secret']);

        // Frontend client
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_frontend', $config['contao_oauth2_clients']['github_frontend']);
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_frontend.enable_login', $config['contao_oauth2_clients']['github_frontend']['enable_login']);
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_frontend.redirect_route', $config['contao_oauth2_clients']['github_frontend']['redirect_route']);
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_frontend.client_id', $config['contao_oauth2_clients']['github_frontend']['client_id']);
        $container->setParameter($rootKey.'.contao_oauth2_clients.github_frontend.client_secret', $config['contao_oauth2_clients']['github_frontend']['client_secret']);
    }
}
