<?php

declare(strict_types=1);

namespace Gatherling\Tests\Auth;

use Gatherling\Auth\Login;
use Gatherling\Auth\LoginError;
use Gatherling\Auth\Registration;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

final class LoginTest extends DatabaseCase
{
    public function testBadLogin(): void
    {
        $result = Login::login('doesnotexist', 'testpassword');
        $this->assertFalse($result->success);
        $this->assertContains(LoginError::INVALID_CREDENTIALS, $result->errors);
    }

    public function testGoodLogin(): void
    {
        $regResult = Registration::register('testuser', 'testpassword', 'testpassword', 'test@example.com', 1, -5.0, null, null);
        $this->assertSame(Registration::SUCCESS, $regResult);

        $result = Login::login('testuser', 'wrongpassword');
        $this->assertFalse($result->success);
        $this->assertContains(LoginError::INVALID_CREDENTIALS, $result->errors);

        $result = Login::login('testuser', 'testpassword');
        $this->assertTrue($result->success);
        $this->assertEmpty($result->errors);

        $result = Login::login(null, 'testpassword');
        $this->assertFalse($result->success);
        $this->assertContains(LoginError::MISSING_USERNAME, $result->errors);

        $result = Login::login('testuser', null);
        $this->assertFalse($result->success);
        $this->assertContains(LoginError::MISSING_PASSWORD, $result->errors);
    }

    public function testShortPasswordLogin(): void
    {
        $regResult = Registration::register('testuser2', 'short', 'short', 'test2@example.com', 1, -5.0, null, null);
        $this->assertSame(Registration::SUCCESS, $regResult);

        $result = Login::login('testuser2', 'short');
        $this->assertTrue($result->success);
        $this->assertContains(LoginError::PASSWORD_TOO_SHORT, $result->errors);
    }
}
