<?php

declare(strict_types=1);

/*
 * This file is part of Contao GitHub Authenticator.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
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
use League\OAuth2\Client\Provider\Exception\GithubIdentityProviderException;
use Markocupic\ContaoGitHubLogin\Event\GetAccessTokenEvent;
use Markocupic\ContaoGitHubLogin\Exception\GitHubAuthenticationException;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\ClientFactory;
use Markocupic\ContaoGitHubLogin\OAuth2\Client\ClientRegistry;
use Markocupic\ContaoGitHubLogin\Util\AuthenticatorUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    public function __construct(
        private readonly AuthenticationSuccessHandler $authenticationSuccessHandler,
        private readonly AuthenticatorUtil $authenticatorUtil,
        private readonly ClientFactory $clientFactory,
        private readonly ClientRegistry $clientRegistry,
        private readonly ContaoFramework $framework,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    public function supports(Request $request): bool
    {
        if (empty($request->query->get('code'))) {
            return false;
        }

        if (!$request->attributes->has('_scope')) {
            return false;
        }

        if ('github' !== $request->attributes->get('_oauth2_provider_type')) {
            return false;
        }

        $oauthClientName = $request->attributes->get('_scope');

        if (!\in_array($oauthClientName, $this->clientRegistry->getAvailableClients(), true)) {
            return false;
        }

        $clientConfig = $this->clientRegistry->getClientConfigFor($oauthClientName);

        // Do only log in users from allowed routes
        if ($request->attributes->get('_route') !== $clientConfig['redirect_route']) {
            return false;
        }

        return true;
    }

    public function start(Request $request, string $oauthClientName, AuthenticationException|null $authException = null): RedirectResponse|Response
    {
        $client = $this->clientFactory->create($oauthClientName);

        // Fetch the authorization URL from the provider;
        // this returns the urlAuthorize option and generates and applies any necessary parameters
        // (e.g. state).
        $authorizationUrl = $client->getAuthorizationUrl();

        $sessionBag = $this->getSessionBag($request);
        $sessionBag->set('oauth2state', $client->getState());

        return new RedirectResponse($authorizationUrl);
    }

    public function authenticate(Request $request): Passport
    {
        $this->framework->initialize();

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
            // Get the client name (backend or frontend) from the contao scope
            $oauthClientName = $request->attributes->get('_scope');
            $client = $this->clientFactory->create($oauthClientName);

            // Try to get an access token using the authorization code grant.
            $accessToken = $client->getAccessToken('authorization_code', [
                'code' => $request->query->get('code'),
            ]);

            // Dispatch GetAccessTokenEvent event
            $event = new GetAccessTokenEvent($accessToken, $request, $request->attributes->get('_scope'));
            $this->eventDispatcher->dispatch($event, GetAccessTokenEvent::NAME);

            // Get the email address from resource owner.
            $email = $client->getResourceOwner($accessToken)->toArray()['email'];
        } catch (GithubIdentityProviderException $e) {
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

        return new SelfValidatingPassport(new UserBadge($contaoUser->username));
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
        return $request->getSession()->getBag('contao_github_login_attr');
    }

    private function throwAuthenticationException(int $code): void
    {
        throw new GitHubAuthenticationException(GitHubAuthenticationException::ERROR_MAP[$code], $code);
    }
}
