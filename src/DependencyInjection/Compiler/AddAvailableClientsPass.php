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

namespace Markocupic\ContaoGitHubLogin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddAvailableClientsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Add available clients as second argument to the client registry service
        $gitHubLoginConfig = $container->getExtensionConfig('markocupic_contao_github_login');

        $definition = $container->findDefinition('markocupic.contao_github_login.oauth2.client.client_registry');

        $definition->setArgument(0, $gitHubLoginConfig[0]['clients']);
    }
}
