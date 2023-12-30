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

namespace Markocupic\ContaoGitHubLogin\Security\Authenticator;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\Authentication\AuthenticationSuccessHandler;
use Contao\MemberModel;
use Contao\Message;
use Contao\UserModel;
use Markocupic\ContaoGitHubLogin\Exception\GitHubAuthenticationException;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\Provider\Exception\GitHubIdentityProviderException;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\Provider\ProviderFactory;
use Markocupic\ContaoGitHubLogin\Util\AuthenticatorUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Contracts\Translation\TranslatorInterface;

class GitHubAuthenticator extends AbstractAuthenticator
{
    public const ALLOWED_LOGIN_ROUTES = [
        'markocupic_contao_github_backend_login',
        'markocupic_contao_github_frontend_login',
    ];

    public function __construct(
        private readonly AuthenticationSuccessHandler $authenticationSuccessHandler,
        private readonly AuthenticatorUtil $authenticatorUtil,
        private readonly ContaoFramework $framework,
        private readonly ProviderFactory $providerFactory,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    public function supports(Request $request): bool
    {
        // Do only log in users from allowed routes
        if (!\in_array($request->attributes->get('_route'), self::ALLOWED_LOGIN_ROUTES, true)) {
            return false;
        }

        if (empty($request->query->get('code'))) {
            return false;
        }

        return true;
    }

    public function start(Request $request, AuthenticationException|null $authException = null): RedirectResponse|Response
    {
        // Get the client from Contao scope
        $clientName = $request->attributes->get('_scope');

        $provider = $this->providerFactory->create($clientName);

        // Fetch the authorization URL from the provider;
        // this returns the urlAuthorize option and generates and applies any necessary parameters
        // (e.g. state).
        $authorizationUrl = $provider->getAuthorizationUrl();

        $sessionBag = $this->getSessionBag($request);
        $sessionBag->set('oauth2state', $provider->getState());

        return new RedirectResponse($authorizationUrl);
    }

    public function authenticate(Request $request): Passport
    {
        $this->framework->initialize();

        // Get the client from Contao scope
        $clientName = $request->attributes->get('_scope');

        // Get the message adapter
        $message = $this->framework->getAdapter(Message::class);

        $sessionBag = $this->getSessionBag($request);
        $request->request->set('_target_path', $sessionBag->get('_target_path'));
        $request->request->set('_always_use_target_path', $sessionBag->get('_always_use_target_path'));

        if (empty($request->query->get('state')) || empty($this->getSessionBag($request)->get('oauth2state')) || $request->query->get('state') !== $this->getSessionBag($request)->get('oauth2state')) {
            // This will trigger self::onAuthenticationFailure()
            $this->throwAuthenticationException(GitHubAuthenticationException::ERROR_INVALID_OAUTH_STATE);
        }

        try {
            $provider = $this->providerFactory->create($clientName);

            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $request->query->get('code'),
            ]);

            // Todo: Dispatch GetAccessTokenEvent (Import not existent users)

            // Get the email address from resource owner.
            $email = $provider->getResourceOwner($accessToken)->toArray()['email'];
        } catch (GitHubIdentityProviderException $e) {
            $message->addError($this->translator->trans('GITHUB_LOGIN_ERR.identityProviderException', [$e->getMessage()], 'contao_default'));

            if ($this->scopeMatcher->isBackendRequest($request)) {
                // This will trigger self::onAuthenticationFailure()
                $this->throwAuthenticationException(GitHubAuthenticationException::ERROR_CONTAO_BACKEND_USER_NOT_FOUND);
            } else {
                // This will trigger self::onAuthenticationFailure()
                $this->throwAuthenticationException(GitHubAuthenticationException::ERROR_CONTAO_FRONTEND_USER_NOT_FOUND);
            }
        } catch (\Exception $e) {
            // This will trigger self::onAuthenticationFailure()
            $this->throwAuthenticationException(GitHubAuthenticationException::ERROR_UNEXPECTED);
        }

        // Get the correct user model.
        if ($this->scopeMatcher->isBackendRequest($request)) {
            $userAdapter = $this->framework->getAdapter(UserModel::class);
        } else {
            $userAdapter = $this->framework->getAdapter(MemberModel::class);
        }


        $t = $userAdapter->getTable();
        $where = ["$t.email = '$email'"];

        $contaoUser = $userAdapter->findBy($where, []);

        if (null === $contaoUser) {
            $message->addError($this->translator->trans('GITHUB_LOGIN_ERR.userWithEmailAddressNotFound', [$this->authenticatorUtil->maskEmail($email)], 'contao_default'));

            if ($this->scopeMatcher->isBackendRequest($request)) {
                // This will trigger self::onAuthenticationFailure()
                $this->throwAuthenticationException(GitHubAuthenticationException::ERROR_CONTAO_BACKEND_USER_NOT_FOUND);
            } else {
                // This will trigger self::onAuthenticationFailure()
                $this->throwAuthenticationException(GitHubAuthenticationException::ERROR_CONTAO_FRONTEND_USER_NOT_FOUND);
            }
        }

        return new SelfValidatingPassport(new UserBadge($contaoUser->username, null, ['scope' => $request->attributes->get('_scope')]));
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        // Do nothing special here. Method override is redundant.
        // This method can also be omitted and the inherited method 'parent::createToken()' can be used.
        // @Todo Remove this method, because it is redundant.
        return parent::createToken($passport, $firewallName);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $firewallName): Response|null
    {
        // Clear session.
        $this->getSessionBag($request)->clear();

        // Trigger the on authentication success handler from the Contao Core.
        return $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response|null
    {
        // Do not use Contao Core's onAuthenticationFailure handler
        // because this leads to an endless redirection loop.
        $sessionBag = $this->getSessionBag($request);
        $targetPath = $request->get('_target_path');

        if ($this->scopeMatcher->isFrontendRequest($request) && $sessionBag->has('_failure_path')) {
            $targetPath = $sessionBag->get('_failure_path');
        }

        $sessionBag->clear();

        $this->logger?->info($exception->getMessage());

        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse(base64_decode($targetPath, true));
    }

    private function getSessionBag(Request $request): SessionBagInterface
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            return $request->getSession()->getBag('contao_github_login_attr_backend');
        }

        return $request->getSession()->getBag('contao_github_login_attr_frontend');
    }

    private function throwAuthenticationException(int $code): void
    {
        throw new GitHubAuthenticationException(GitHubAuthenticationException::ERROR_MAP[$code], $code);
    }
}
