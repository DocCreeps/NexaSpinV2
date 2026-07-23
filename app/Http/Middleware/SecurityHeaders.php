<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ajoute des en-têtes de sécurité HTTP en défense en profondeur.
 *
 * Aucune faille XSS connue dans l'application (pas d'echo brut Blade),
 * mais ces en-têtes limitent l'impact d'une éventuelle régression future
 * (ex: ajout accidentel de {!! !!} ou d'un innerHTML côté JS).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // En développement, Vite sert les assets (dont le CSS compilé par Tailwind) et le HMR
        // depuis une origine séparée (ex: http://localhost:5173) via <script type="module">
        // + une connexion WebSocket. Une CSP stricte en 'self' casse ce chargement — d'où
        // l'absence totale de style Tailwind observée. On n'applique donc la CSP qu'en prod.
        if (app()->environment('production')) {
            $response->headers->set(
                'Content-Security-Policy',
                implode('; ', [
                    "default-src 'self'",
                    "script-src 'self' 'unsafe-eval'",
                    "style-src 'self' 'unsafe-inline'",
                    "img-src 'self' data:",
                    "font-src 'self' data:",
                    "connect-src 'self'",
                    "frame-ancestors 'none'",
                    "base-uri 'self'",
                    "form-action 'self'",
                ])
            );
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
