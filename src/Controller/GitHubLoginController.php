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

namespace Markocupic\ContaoGitHubLogin\Controller;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Exception\InvalidRequestTokenException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use Markocupic\ContaoGitHubLogin\Security\Authenticator\GitHubAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

#[Route('/_github_backend_login', name: 'markocupic_contao_github_backend_login', defaults: ['_scope' => 'backend', '_token_check' => false])]
#[Route('/_github_frontend_login', name: 'markocupic_contao_github_frontend_login', defaults: ['_scope' => 'frontend', '_token_check' => false])]
class GitHubLoginController extends AbstractController
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ContaoCsrfTokenManager $tokenManager,
        private readonly GitHubAuthenticator $authenticator,
        private readonly RouterInterface $router,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly UriSigner $uriSigner,
    ) {
    }

    public function __invoke(Request $request, string $_scope): Response|null
    {
        if (!$this->uriSigner->checkRequest($request)) {
            return new JsonResponse(['message' => 'Access denied.'], Response::HTTP_BAD_REQUEST);
        }

        $system = $this->framework->getAdapter(System::class);
        $csrfTokenName = $system->getContainer()->getParameter('contao.csrf_token_name');

        $this->checkGitHubLoginIsActivated($request);

        if ('backend' === $_scope) {
            // Check csrf token
            if ($system->getContainer()->getParameter('markocupic_contao_github_login.backend.enable_csrf_token_check')) {
                $this->validateCsrfToken($request->get('REQUEST_TOKEN'), $this->tokenManager, $csrfTokenName);
            }

            $targetPath = $request->get('_target_path', base64_encode($this->router->generate('contao_backend', [], UrlGeneratorInterface::ABSOLUTE_URL)));
        } else {
            // Check csrf token
            if ($system->getContainer()->getParameter('markocupic_contao_github_login.frontend.enable_csrf_token_check')) {
                $this->validateCsrfToken($request->get('REQUEST_TOKEN'), $this->tokenManager, $csrfTokenName);
            }

            // Frontend: If there is an authentication error, Contao will redirect the user back to the login form
            $failurePath = $request->get('_failure_path', null);
            $targetPath = $request->get('_target_path', base64_encode($request->getSchemeAndHttpHost()));
        }

        // Write _target_path, _always_use_target_path and _failure_path to the session
        $sessionBag = $this->getSessionBag($request);
        $sessionBag->set('_target_path', $targetPath);
        $sessionBag->set('_always_use_target_path', $request->get('_always_use_target_path', '0'));

        if (!empty($failurePath)) {
            $sessionBag->set('_failure_path', $failurePath);
        }

        return $this->authenticator->start($request);
    }

    private function getSessionBag(Request $request): SessionBagInterface
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            return $request->getSession()->getBag('contao_github_login_attr_backend');
        }

        return $request->getSession()->getBag('contao_github_login_attr_frontend');
    }

    private function validateCsrfToken(string $strToken, ContaoCsrfTokenManager $tokenManager, string $csrfTokenName): void
    {
        $token = new CsrfToken($csrfTokenName, $strToken);

        if (!$tokenManager->isTokenValid($token)) {
            throw new InvalidRequestTokenException('Invalid CSRF token. Please reload the page and try again.');
        }
    }

    /**
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    private function checkGitHubLoginIsActivated(Request $request): void
    {
        $container = $this->framework->getAdapter(System::class)->getContainer();

        if ($this->scopeMatcher->isBackendRequest($request)) {
            if (!$container->getParameter('markocupic_contao_github_login.backend.enable_github_login')) {
                throw new \Exception('GitHub Backend Login not activated. Please check your container configuration.');
            }
        } else {
            if (!$container->getParameter('markocupic_contao_github_login.frontend.enable_github_login')) {
                throw new \Exception('GitHub Frontend Login not activated. Please check your container configuration.');
            }
        }
    }
}
