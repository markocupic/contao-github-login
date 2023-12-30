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

namespace Markocupic\ContaoGitHubLogin\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsHook('parseBackendTemplate')]
readonly class ParseBackendTemplateListener
{
    public function __construct(
        private ContaoFramework $framework,
        private ClientRegistry $clientRegistry,
        private RouterInterface $router,
        private Twig $twig,
        private UriSigner $uriSigner,
    ) {
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(string $strContent, string $strTemplate): string
    {
        if ('be_login' === $strTemplate) {
            $system = $this->framework->getAdapter(System::class);
            $container = $system->getContainer();

            $clientConfig = $this->clientRegistry->getClientConfigFor('backend');

            if (!$clientConfig['enable_github_login']) {
                return $strContent;
            }

            $template = [];

            // Generate & sign url to the markocupic_contao_github_backend_login route
            $template['url'] = $this->uriSigner->sign($this->router->generate($clientConfig['redirect_route'], [], UrlGeneratorInterface::ABSOLUTE_URL));

            // Get request token (disabled by default)
            $template['request_token'] = '';
            $template['enable_csrf_token_check'] = false;

            if ($clientConfig['enable_csrf_token_check']) {
                $template['request_token'] = $this->getRequestToken();
                $template['enable_csrf_token_check'] = true;
            }

            $template['target_path'] = $this->getTargetPath($strContent);
            $template['always_use_target_path'] = $this->getAlwaysUseTargetPath($strContent);
            $template['disable_contao_login'] = $clientConfig['disable_contao_login'];

            // Render template
            $strGitHubLoginButton = $this->twig->render(
                '@MarkocupicContaoGitHubLogin/backend_login.html.twig',
                $template,
            );

            // Replace insert tags
            $strGitHubLoginButton = $container->get('contao.insert_tag.parser')->replaceInline($strGitHubLoginButton);

            // Prepend SAC SSO login form
            $strContent = str_replace('<form', $strGitHubLoginButton.'<form', $strContent);

            // Remove Contao Core backend login form markup
            if ($clientConfig['disable_contao_login']) {
                $strContent = $this->removeContaoLoginForm($strContent);
            }

            // Add hack: Test, if input field with id="username" exists.
            $strContent = str_replace("$('username').focus();", "if ($('username')){ \n\t\t$('username').focus();\n\t  }", $strContent);
        }

        return $strContent;
    }

    private function getRequestToken(): string
    {
        $system = $this->framework->getAdapter(System::class);
        $container = $system->getContainer();
        $tokenName = $container->getParameter('contao.csrf_token_name');

        if (null === $tokenName) {
            return '';
        }

        return $container->get('contao.csrf.token_manager')->getToken($tokenName)->getValue();
    }

    private function getTargetPath(string $strContent): string
    {
        $targetPath = '';

        if (preg_match('/name="_target_path"\s+value=\"([^\']*?)\"/', $strContent, $matches)) {
            $targetPath = $matches[1];
        }

        return $targetPath;
    }

    private function getAlwaysUseTargetPath(string $strContent): string
    {
        $targetPath = '';

        if (preg_match('/name="_always_use_target_path"\s+value=\"([^\']*?)\"/', $strContent, $matches)) {
            $targetPath = $matches[1];
        }

        return $targetPath;
    }

    private function removeContaoLoginForm(string $strContent): string
    {
        return preg_replace('/<form class="tl_login_form"[^>]*>(.*?)<\/form>/is', '', $strContent);
    }
}
