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

namespace Markocupic\ContaoGitHubLogin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/_oauth2_login/{_oauth2_provider_type}/backend', name: 'markocupic_contao_github_login_redirect_backend', defaults: ['_scope' => 'backend'])]
#[Route('/_oauth2_login/{_oauth2_provider_type}/frontend', name: 'markocupic_contao_github_login_redirect_frontend', defaults: ['_scope' => 'frontend'])]
class GitHubLoginRedirectController extends AbstractController
{
    public function __invoke(Request $request,  string $_oauth2_provider_type): Response
    {
        // This point normally should not be reached.
        return new Response('');
    }
}
