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
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as Twig;

#[AsHook('parseBackendTemplate')]
class ParseBackendTemplateListener
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly RouterInterface $router,
        private readonly Twig $twig,
        private readonly UriSigner $uriSigner,
    ) {
    }

    public function __invoke(string $strContent, string $strTemplate): string
    {
        if ('be_login' === $strTemplate) {
            $system = $this->framework->getAdapter(System::class);
            $container = $system->getContainer();

            if (!$container->getParameter('markocupic_contao_github_login.backend.enable_github_login')) {
                return $strContent;
            }

            $template = [];

            // Generate & sign url to the markocupic_contao_github_backend_login route
            $template['url'] = $this->uriSigner->sign($this->router->generate('markocupic_contao_github_backend_login', [], UrlGeneratorInterface::ABSOLUTE_URL));

            // Get request token (disabled by default)
            $template['request_token'] = '';
            $template['enable_csrf_token_check'] = false;

            if ($container->getParameter('markocupic_contao_github_login.backend.enable_csrf_token_check')) {
                $template['request_token'] = $this->getRequestToken();
                $template['enable_csrf_token_check'] = true;
            }

            $template['target_path'] = $this->getTargetPath($strContent);
            $template['always_use_target_path'] = $this->getAlwaysUseTargetPath($strContent);
            $template['error'] = $this->getErrorMessage();
            $template['disable_contao_login'] = $container->getParameter('markocupic_contao_github_login.backend.disable_contao_login');

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
            if ($container->getParameter('markocupic_contao_github_login.backend.disable_contao_login')) {
                $strContent = preg_replace('/<form class="tl_login_form"[^>]*>(.*?)<\/form>/is', '', $strContent);
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

    /**
     * Retrieve first error message.
     *
     * @throws \Exception
     */
    private function getErrorMessage(): array|null
    {
        $system = $this->framework->getAdapter(System::class);
        $container = $system->getContainer();

        $flashBag = $container->get('request_stack')
            ->getCurrentRequest()
            ->getSession()
            ->getFlashBag()
            ->get($container->getParameter('markocupic_contao_github_login.flash_bag_key'))
        ;

        if (!empty($flashBag)) {
            $arrError = [];

            foreach ($flashBag[0] as $k => $v) {
                $arrError[$k] = $v;
            }

            return $arrError;
        }

        return null;
    }
}
