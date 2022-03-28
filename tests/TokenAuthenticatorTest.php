<?php

namespace Gaara\Tests;

use Gaara\Authenticator\TokenAuthenticator;
use Gaara\CredentialValidator\PasswordCredentialValidator;
use Gaara\CredentialValidator\PasswordUserInterface;
use Gaara\UserProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class TokenAuthenticatorTest extends TestCase
{
    public function testBasic()
    {
        $requestStub = $this->createMock(ServerRequestInterface::class);
        $requestStub->method('getHeaderLine')->willReturn('eyJpZCI6MSwidG9rZW4iOiIxMjNhYmMiLCJzaWduIjoiMDEwNjU1MmVmNTg3YzBlOWJlYTI0MzY2ZDcxNjZjMWEwZDNiNTZlZDA2ZGYwNzBiODYzZGU2ZWM5MGFhNDAwZiJ9');

        $psr6Cahce = new ArrayAdapter();
        $cache = new Psr16Cache($psr6Cahce);

        $userStub = $this->createMock(PasswordUserInterface::class);
        $userStub->method('id')->willReturn(1);
        $userStub->method('password')->willReturn('$2y$10$bHKZuDDoWLMPkP07oaXnJuSgmamsA4xFVqbCPXHUAmoPGRCv02Aje');

        $userProviderStub = $this->createMock(UserProviderInterface::class);
        $userProviderStub->method('findById')->willReturn($userStub);
        $userProviderStub->method('findByCredential')->willReturn($userStub);

        $authenticator = new TokenAuthenticator($requestStub, $cache, 'user', 60 * 30, 'salt', false);
        $authenticator->setUserProvider($userProviderStub);
        $authenticator->setCredentialValidator(new PasswordCredentialValidator('password'));

        // $this->assertFalse($authenticator->isAuthenticated());
        // $this->assertNull($authenticator->user());

        $identity = $authenticator->authenticate(['name' => 'peng', 'password' => '123654']);
        // $this->assertEquals('eyJpZCI6MSwidG9rZW4iOiIxMjNhYmMiLCJzaWduIjoiMDEwNjU1MmVmNTg3YzBlOWJlYTI0MzY2ZDcxNjZjMWEwZDNiNTZlZDA2ZGYwNzBiODYzZGU2ZWM5MGFhNDAwZiJ9', $identity->data()['token']);
        // $this->assertTrue($authenticator->isAuthenticated());

        $this->assertEquals(1, $identity->user()->id());

        // $authenticator->clearUser();

        // $this->assertFalse($authenticator->isAuthenticated());
        // $this->assertNull($authenticator->user());
    }
}
