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

        $loader->load('parameters.yaml');
        $loader->load('services.yaml');
        $loader->load('listener.yaml');

        $rootKey = $this->getAlias();
        $container->setParameter($rootKey.'.backend.disable_contao_login', $config['backend']['disable_contao_login']);
        $container->setParameter($rootKey.'.backend.enable_github_login', $config['backend']['enable_github_login']);
        $container->setParameter($rootKey.'.backend.enable_csrf_token_check', $config['backend']['enable_csrf_token_check']);
        $container->setParameter($rootKey.'.frontend.enable_github_login', $config['frontend']['enable_github_login']);
        $container->setParameter($rootKey.'.frontend.enable_csrf_token_check', $config['frontend']['enable_csrf_token_check']);
        $container->setParameter($rootKey.'.flash_bag_key', $config['flash_bag_key']);
    }
}
