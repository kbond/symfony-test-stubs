<?php

namespace App\Tests;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AuthenticationTest extends KernelTestCase
{
    use ResetDatabase, Factories, HasBrowser;

    /**
     * @test
     */
    public function user_can_login_and_logout(): void
    {
        UserFactory::createOne(['email' => 'kevin@example.com', 'password' => 'passw0rd']);

        $this->browser()
            ->visit('/')
            ->assertNotSee('Logged in as')
            ->visit('/login')
            ->fillField('Email', 'kevin@example.com')
            ->fillField('Password', 'passw0rd')
            ->click('Sign in')
            ->assertOn('/')
            ->assertSee('Logged in as kevin@example.com')
            ->visit('/logout')
            ->assertOn('/')
            ->assertNotSee('Logged in as')
        ;
    }

    /**
     * @test
     */
    public function invalid_email(): void
    {
        $this->browser()
            ->visit('/login')
            ->fillField('Email', 'not-a-user@example.com')
            ->fillField('Password', 'passw0rd')
            ->click('Sign in')
            ->assertOn('/login')
            ->assertSee('Invalid credentials.')
        ;
    }

    /**
     * @test
     */
    public function invalid_password(): void
    {
        UserFactory::createOne(['email' => 'kevin@example.com', 'password' => 'passw0rd']);

        $this->browser()
            ->visit('/login')
            ->fillField('Email', 'kevin@example.com')
            ->fillField('Password', 'wrong password')
            ->click('Sign in')
            ->assertOn('/login')
            ->assertSee('Invalid credentials.')
            ->visit('/')
            ->assertNotSee('Logged in as')
        ;
    }
}
