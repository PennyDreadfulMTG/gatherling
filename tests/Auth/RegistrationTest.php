<?php

declare(strict_types=1);

namespace Gatherling\Tests\Auth;

use Gatherling\Auth\Registration;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

final class RegistrationTest extends DatabaseCase
{
    public function testRegister(): void
    {
        // Test successful registration with all fields
        $this->assertEquals(Registration::SUCCESS, Registration::register('newuser1', 'password123', 'password123', 'newuser1@example.com', 1, -5.0, '123456789', 'NewUser1#1234'));

        // Test registration with minimum required fields
        $this->assertEquals(Registration::SUCCESS, Registration::register('newuser2', 'password456', 'password456', 'newuser2@example.com', 0, 5.5, null, null));

        // Test registration with Discord ID but no password
        $this->assertEquals(Registration::SUCCESS, Registration::register('discorduser', '', '', 'discorduser@example.com', 2, 3.5, '987654321', 'DiscordUser#5678'));

        // Test registration with mismatched passwords
        $this->assertEquals(Registration::ERROR_PASSWORD_MISMATCH, Registration::register('failuser1', 'password1', 'password2', 'failuser1@example.com', 0, 0, null, null));

        // Test registration with empty password and no Discord ID
        $this->assertEquals(Registration::ERROR_PASSWORD_MISMATCH, Registration::register('failuser2', '', '', 'failuser2@example.com', 0, 0, null, null));

        // Test registration with existing username
        $this->assertEquals(Registration::SUCCESS, Registration::register('existinguser', 'testpass', 'testpass', 'existinguser@example.com', 0, 0, null, null));
        $this->assertEquals(Registration::ERROR_PLAYER_EXISTS, Registration::register('existinguser', 'newpass', 'newpass', 'newemail@example.com', 1, 1.0, null, null));

        // Test registration with various email privacy settings
        $this->assertEquals(Registration::SUCCESS, Registration::register('privacyuser1', 'pass123', 'pass123', 'privacy1@example.com', 0, 0, null, null));
        $this->assertEquals(Registration::SUCCESS, Registration::register('privacyuser2', 'pass456', 'pass456', 'privacy2@example.com', 1, 0, null, null));
        $this->assertEquals(Registration::SUCCESS, Registration::register('privacyuser3', 'pass789', 'pass789', 'privacy3@example.com', 2, 0, null, null));

        // Test registration with extreme timezone values
        $this->assertEquals(Registration::SUCCESS, Registration::register('timezoneuser1', 'tzpass1', 'tzpass1', 'tz1@example.com', 0, -12.0, null, null));
        $this->assertEquals(Registration::SUCCESS, Registration::register('timezoneuser2', 'tzpass2', 'tzpass2', 'tz2@example.com', 0, 14.0, null, null));

        // Test registration with special characters in username and password
        $this->assertEquals(Registration::SUCCESS, Registration::register('special_user!@#', 'p@ssw0rd!@#', 'p@ssw0rd!@#', 'special@example.com', 0, 0, null, null));
    }
}
