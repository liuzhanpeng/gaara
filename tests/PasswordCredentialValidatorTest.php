<?php

namespace Gaara\Tests;

use Gaara\CredentialValidator\PasswordCredentialValidator;
use Gaara\CredentialValidator\PasswordUserInterface;
use Gaara\Exception\AuthenticateException;
use Gaara\Exception\InvalidCredentialException;
use Gaara\UserInterface;
use Gaara\UserProviderInterface;
use PHPUnit\Framework\TestCase;

class PasswordCredentialValidatorTest extends TestCase
{
    public function testBasic()
    {
        $userStub = $this->createMock(PasswordUserInterface::class);
        $userStub->method('id')->willReturn(1);
        $userStub->method('password')->willReturn('$2y$10$bHKZuDDoWLMPkP07oaXnJuSgmamsA4xFVqbCPXHUAmoPGRCv02Aje');

        $userProviderStub = $this->createMock(UserProviderInterface::class);
        $userProviderStub->method('findByCredential')->willReturn($userStub);

        $credential = ['name' => 'peng', 'password' => '123654'];
        $validator = new PasswordCredentialValidator('password');
        $user = $validator->validateCredential($userProviderStub, $credential);

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals(1, $user->id());
    }

    public function testException1()
    {
        $this->expectException(InvalidCredentialException::class);

        $userStub = $this->createMock(PasswordUserInterface::class);
        $userStub->method('id')->willReturn(1);
        $userStub->method('password')->willReturn('$2y$10$bHKZuDDoWLMPkP07oaXnJuSgmamsA4xFVqbCPXHUAmoPGRCv02Aje');

        $userProviderStub = $this->createMock(UserProviderInterface::class);
        $userProviderStub->method('findByCredential')->willReturn($userStub);

        $credential = ['name' => 'peng', 'password' => 'wrong'];
        $validator = new PasswordCredentialValidator('password');
        $validator->validateCredential($userProviderStub, $credential);
    }

    public function testException2()
    {
        $this->expectException(AuthenticateException::class);

        $userStub = $this->createMock(UserInterface::class);
        $userStub->method('id')->willReturn(1);

        $userProviderStub = $this->createMock(UserProviderInterface::class);
        $userProviderStub->method('findByCredential')->willReturn($userStub);

        $credential = ['name' => 'peng', 'password' => 'wrong'];
        $validator = new PasswordCredentialValidator('password');
        $validator->validateCredential($userProviderStub, $credential);
    }
}
