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

use Markocupic\ContaoGitHubLogin\OAuth2\Client\GitHubBackendClientFactory;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\GitHubFrontendClientFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROOT_KEY = 'markocupic_contao_github_login';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_KEY);

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('contao_oauth2_clients')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode(GitHubBackendClientFactory::NAME)
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enable_login')
                                    ->defaultValue(true)
                                ->end()
                                ->scalarNode('client_id')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('client_secret')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(GitHubFrontendClientFactory::NAME)
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enable_login')
                                    ->defaultValue(true)
                                ->end()
                                ->scalarNode('client_id')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('client_secret')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }
}
