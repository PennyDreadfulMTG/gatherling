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
        self::assertFalse($result->success);
        self::assertContains(LoginError::INVALID_CREDENTIALS, $result->errors);
    }

    public function testGoodLogin(): void
    {
        $regResult = Registration::register('testuser', 'testpassword', 'testpassword', 'test@example.com', 1, -5.0, null, null);
        self::assertSame(Registration::SUCCESS, $regResult);

        $result = Login::login('testuser', 'wrongpassword');
        self::assertFalse($result->success);
        self::assertContains(LoginError::INVALID_CREDENTIALS, $result->errors);

        $result = Login::login('testuser', 'testpassword');
        self::assertTrue($result->success);
        self::assertEmpty($result->errors);

        $result = Login::login(null, 'testpassword');
        self::assertFalse($result->success);
        self::assertContains(LoginError::MISSING_USERNAME, $result->errors);

        $result = Login::login('testuser', null);
        self::assertFalse($result->success);
        self::assertContains(LoginError::MISSING_PASSWORD, $result->errors);
    }

    public function testShortPasswordLogin(): void
    {
        $regResult = Registration::register('testuser2', 'short', 'short', 'test2@example.com', 1, -5.0, null, null);
        self::assertSame(Registration::SUCCESS, $regResult);

        $result = Login::login('testuser2', 'short');
        self::assertTrue($result->success);
        self::assertContains(LoginError::PASSWORD_TOO_SHORT, $result->errors);
    }
}
