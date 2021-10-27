<?php

namespace App\Tests;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class RegistrationTest extends KernelTestCase
{
    use ResetDatabase, Factories, HasBrowser;

    /**
     * @test
     */
    public function can_register(): void
    {
        UserFactory::assert()->empty();

        $this->browser()
            ->visit('/')
            ->assertNotSee('Logged in as')
            ->visit('/register')
            ->fillField('Email', 'kevin@example.com')
            ->fillField('Password', 'passw0rd')
            ->checkField('Agree terms')
            ->click('Register')
            ->assertOn('/')
            ->assertNotSee('Logged in as')
            ->visit('/login')
            ->fillField('Email', 'kevin@example.com')
            ->fillField('Password', 'passw0rd')
            ->click('Sign in')
        ;

        UserFactory::assert()->exists(['email' => 'kevin@example.com']);
    }

    /**
     * @test
     */
    public function cannot_register_existing_email(): void
    {
        UserFactory::createOne(['email' => 'kevin@example.com']);

        $this->browser()
            ->visit('/register')
            ->fillField('Email', 'kevin@example.com')
            ->fillField('Password', 'passw0rd')
            ->checkField('Agree terms')
            ->click('Register')
            ->assertOn('/register')
            ->assertSee('There is already an account with this email')
        ;

        UserFactory::assert()->count(1);
    }

    /**
     * @test
     */
    public function minimum_password_length_required(): void
    {
        $this->browser()
            ->visit('/register')
            ->fillField('Email', 'kevin@example.com')
            ->fillField('Password', 'p')
            ->checkField('Agree terms')
            ->click('Register')
            ->assertOn('/register')
            ->assertSee('Your password should be at least 6 characters')
        ;

        UserFactory::assert()->empty();
    }

    /**
     * @test
     */
    public function email_is_required(): void
    {
        $this->browser()
            ->visit('/register')
            ->fillField('Password', 'passw0rd')
            ->checkField('Agree terms')
            ->click('Register')
            ->assertOn('/register')
            ->assertSee('Please enter an email')
        ;

        UserFactory::assert()->empty();
    }

    /**
     * @test
     */
    public function email_must_be_used(): void
    {
        $this->browser()
            ->visit('/register')
            ->fillField('Email', 'not-an-email')
            ->fillField('Password', 'passw0rd')
            ->checkField('Agree terms')
            ->click('Register')
            ->assertOn('/register')
            ->assertSee('This value is not a valid email address.')
        ;

        UserFactory::assert()->empty();
    }

    /**
     * @test
     */
    public function password_is_required(): void
    {
        $this->browser()
            ->visit('/register')
            ->fillField('Email', 'kevin@example.com')
            ->checkField('Agree terms')
            ->click('Register')
            ->assertOn('/register')
            ->assertSee('Please enter a password')
        ;

        UserFactory::assert()->empty();
    }

    /**
     * @test
     */
    public function must_agree_to_terms(): void
    {
        $this->browser()
            ->visit('/register')
            ->fillField('Email', 'kevin@example.com')
            ->fillField('Password', 'passw0rd')
            ->click('Register')
            ->assertOn('/register')
            ->assertSee('You should agree to our terms.')
        ;

        UserFactory::assert()->empty();
    }
}
