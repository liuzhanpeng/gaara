<?php

namespace Gaara\Tests;

use Gaara\Authenticator\SessionAuthenticator;
use Gaara\CredentialValidator\PasswordCredentialValidator;
use Gaara\CredentialValidator\PasswordUserInterface;
use Gaara\Identity;
use Gaara\UserInterface;
use Gaara\UserProviderInterface;
use PHPUnit\Framework\TestCase;

class SessionAuthenticatorText extends TestCase
{
    public function testBasic()
    {
        $session = new Session();

        $userStub = $this->createMock(PasswordUserInterface::class);
        $userStub->method('id')->willReturn(1);
        $userStub->method('password')->willReturn('$2y$10$bHKZuDDoWLMPkP07oaXnJuSgmamsA4xFVqbCPXHUAmoPGRCv02Aje');

        $userProviderStub = $this->createMock(UserProviderInterface::class);
        $userProviderStub->method('findById')->willReturn($userStub);
        $userProviderStub->method('findByCredential')->willReturn($userStub);

        $authenticator = new SessionAuthenticator($session, 'auth');
        $authenticator->setUserProvider($userProviderStub);
        $authenticator->setCredentialValidator(new PasswordCredentialValidator('password'));

        $this->assertFalse($authenticator->isAuthenticated());
        $this->assertNull($authenticator->user());

        $identity = $authenticator->authenticate(['name' => 'peng', 'password' => '123654']);
        $this->assertTrue($authenticator->isAuthenticated());

        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertInstanceOf(UserInterface::class, $identity->user());
        $this->assertInstanceOf(UserInterface::class, $authenticator->user());
        $this->assertEquals(1, $identity->user()->id());

        $authenticator->clearUser();

        $this->assertFalse($authenticator->isAuthenticated());
        $this->assertNull($authenticator->user());
    }
}
