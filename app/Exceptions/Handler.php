<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Actions\Public\RecordPublicEventAction;
use App\Enums\PublicEventKind;
use App\Enums\VisitorVariant;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if (! $this->isPublicSurface($request)) {
                return null;
            }

            $status = $this->resolveStatusCode($e);

            if ($status < 500) {
                // 4xx on the public surface (e.g. validation, 404, 410, 429)
                // are handled by their dedicated views or by the controllers themselves.
                return null;
            }

            $this->emitErrorEvent($request, $e, $status);

            return response()->view('public.errors.server-error', [
                'status' => $status,
            ], $status);
        });
    }

    /**
     * Public surface = anything under /bolsa-de-trabajo* or the sitemap. Mirrors the
     * route group registered in routes/web.php.
     */
    private function isPublicSurface(Request $request): bool
    {
        return $request->is('bolsa-de-trabajo')
            || $request->is('bolsa-de-trabajo/*')
            || $request->is('sitemap.xml');
    }

    private function resolveStatusCode(Throwable $e): int
    {
        // HttpResponseException wraps a fully-formed response (e.g.,
        // ThrottleRequests uses it to signal 429 with the limiter's view
        // attached). Use the inner response's status so we don't
        // misclassify it as 500.
        if ($e instanceof HttpResponseException) {
            return $e->getResponse()->getStatusCode();
        }

        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        return 500;
    }

    private function emitErrorEvent(Request $request, Throwable $e, int $status): void
    {
        try {
            RecordPublicEventAction::run(
                kind: PublicEventKind::ErrorShown,
                request: $request,
                variant: VisitorVariant::Anonymous,
                pageNumber: $request->integer('page') ?: null,
                payload: [
                    'failure_mode' => $this->classifyFailure($e),
                    'http_status' => $status,
                ],
            );
        } catch (Throwable $loggingFailure) {
            // Never let observability swallow the original error.
            Log::warning('PublicEvent emit failed during error handling', [
                'original' => $e::class,
                'observability' => $loggingFailure::class,
            ]);
        }
    }

    private function classifyFailure(Throwable $e): string
    {
        $name = strtolower($e::class);

        return match (true) {
            str_contains($name, 'timeout') => 'timeout',
            str_contains($name, 'connection') => 'network',
            $e instanceof HttpExceptionInterface => '5xx',
            default => 'uncaught',
        };
    }
}
