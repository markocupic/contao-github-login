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
                ->arrayNode('clients')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('backend')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('disable_contao_login')
                                    ->defaultValue(false)
                                ->end()
                                ->booleanNode('enable_github_login')
                                    ->defaultValue(true)
                                ->end()
                                ->booleanNode('enable_csrf_token_check')
                                    ->defaultValue(true)
                                ->end()
                                ->scalarNode('redirect_route')
                                    ->cannotBeEmpty()
                                    ->defaultValue('markocupic_contao_github_frontend_login')
                                ->end()
                                ->scalarNode('client_id')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('client_secret')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('frontend')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enable_github_login')
                                    ->defaultValue(true)
                                ->end()
                                ->booleanNode('enable_csrf_token_check')
                                    ->defaultValue(true)
                                ->end()
                                ->scalarNode('redirect_route')
                                    ->cannotBeEmpty()
                                    ->defaultValue('markocupic_contao_github_frontend_login')
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
