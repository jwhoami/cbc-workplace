<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * Verifies the SecurityHeaders middleware emits CSP, HSTS, and the
 * frame/MIME/referrer headers on every response.
 *
 * Closes #12.
 */
class SecurityHeadersTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    private function expectedHeaders(): array
    {
        return [
            'Content-Security-Policy' => "default-src 'self'; img-src 'self' data: https://ui-avatars.com; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; frame-ancestors 'none'",
            'Strict-Transport-Security' => 'max-age=31536000',
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer-when-downgrade',
        ];
    }

    public function test_admin_login_response_carries_security_headers(): void
    {
        $response = $this->get('/admin/login');

        foreach ($this->expectedHeaders() as $name => $value) {
            $response->assertHeader($name, $value);
        }
    }

    public function test_member_login_response_carries_security_headers(): void
    {
        $response = $this->get('/member/login');

        foreach ($this->expectedHeaders() as $name => $value) {
            $response->assertHeader($name, $value);
        }
    }
}
