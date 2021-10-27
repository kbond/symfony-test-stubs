<?php

namespace App\Tests;

use App\Factory\UserFactory;
use App\Tests\Support\ResetPasswordEmail;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Mailer\Test\InteractsWithMailer;

class ResetPasswordTest extends KernelTestCase
{
    use ResetDatabase, Factories, HasBrowser, InteractsWithMailer;

    /**
     * @test
     */
    public function full_successful_reset_password_flow(): void
    {
        UserFactory::createOne(['email' => 'kevin@example.com', 'password' => 'passw0rd']);

        $this->browser()
            ->visit('/reset-password')
            ->fillField('Email', 'kevin@example.com')
            ->click('Send password reset email')
            ->assertOn('/reset-password/check-email')
        ;

        $this->mailer()
            ->assertSentEmailCount(1)
            ->assertEmailSentTo('kevin@example.com', function(ResetPasswordEmail $email) {
                $email
                    ->assertSubject('Your password reset request')
                    ->assertContains('To reset your password, please visit the following link')
                ;
            })
        ;

        $this->browser()
            ->visit($this->mailer()->sentEmails()->first(ResetPasswordEmail::class)->resetLink())
            ->assertOn('/reset-password/reset')
            ->assertSuccessful()
            ->fillField('New password', 'newpassw0rd')
            ->fillField('Repeat Password', 'newpassw0rd')
            ->click('Reset password')
            ->assertOn('/')
            ->assertNotSee('Logged in as')
            ->visit('/login')
            ->fillField('Email', 'kevin@example.com')
            ->fillField('Password', 'newpassw0rd')
            ->click('Sign in')
            ->assertOn('/')
            ->assertSee('Logged in as kevin@example.com')
        ;
    }

    /**
     * @test
     */
    public function request_reset_non_user_email(): void
    {
        $this->browser()
            ->visit('/reset-password')
            ->fillField('Email', 'not-a-user@example.com')
            ->click('Send password reset email')
            ->assertOn('/reset-password/check-email')
        ;

        $this->mailer()->assertNoEmailSent();
    }

    /**
     * @test
     */
    public function request_reset_email_required(): void
    {
        $this->browser()
            ->visit('/reset-password')
            ->click('Send password reset email')
            ->assertOn('/reset-password')
            ->assertSee('Please enter your email')
        ;

        $this->mailer()->assertNoEmailSent();
    }

    /**
     * @test
     */
    public function reset_password_is_required(): void
    {
        $user = UserFactory::createOne(['email' => 'kevin@example.com']);
        $originalPassword = $user->getPassword();

        $this->browser()
            ->visit('/reset-password')
            ->fillField('Email', 'kevin@example.com')
            ->click('Send password reset email')
            ->visit($this->mailer()->sentEmails()->first(ResetPasswordEmail::class)->resetLink())
            ->click('Reset password')
            ->assertOn('/reset-password/reset')
            ->assertSee('Please enter a password')
        ;

        $this->assertSame($originalPassword, $user->getPassword());
    }

    /**
     * @test
     */
    public function reset_password_minimum_length(): void
    {
        $user = UserFactory::createOne(['email' => 'kevin@example.com']);
        $originalPassword = $user->getPassword();

        $this->browser()
            ->visit('/reset-password')
            ->fillField('Email', 'kevin@example.com')
            ->click('Send password reset email')
            ->visit($this->mailer()->sentEmails()->first(ResetPasswordEmail::class)->resetLink())
            ->fillField('New password', 'p')
            ->fillField('Repeat Password', 'p')
            ->click('Reset password')
            ->assertOn('/reset-password/reset')
            ->assertSee('Your password should be at least 6 characters')
        ;

        $this->assertSame($originalPassword, $user->getPassword());
    }

    /**
     * @test
     */
    public function reset_passwords_must_match(): void
    {
        $user = UserFactory::createOne(['email' => 'kevin@example.com']);
        $originalPassword = $user->getPassword();

        $this->browser()
            ->visit('/reset-password')
            ->fillField('Email', 'kevin@example.com')
            ->click('Send password reset email')
            ->visit($this->mailer()->sentEmails()->first(ResetPasswordEmail::class)->resetLink())
            ->fillField('New password', 'passw0rd')
            ->fillField('Repeat Password', 'not the same')
            ->click('Reset password')
            ->assertOn('/reset-password/reset')
            ->assertSee('The password fields must match')
        ;

        $this->assertSame($originalPassword, $user->getPassword());
    }
}
