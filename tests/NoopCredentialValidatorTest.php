<?php

namespace Gaara\Tests;

use Gaara\CredentialValidator\NoopCredentialValidator;
use Gaara\Exception\InvalidCredentialException;
use Gaara\UserInterface;
use Gaara\UserProviderInterface;
use PHPUnit\Framework\TestCase;

class NoopCredentialValidatorTest extends TestCase
{
    public function testBasic()
    {
        $userStub = $this->createMock(UserInterface::class);
        $userStub->name = 'peng';
        $userStub->method('id')->willReturn(1);

        $userProviderStub = $this->createMock(UserProviderInterface::class);
        $userProviderStub->method('findByCredential')->willReturn($userStub);

        $credential = ['name' => 'peng'];
        $validator = new NoopCredentialValidator();
        $user = $validator->validateCredential($userProviderStub, $credential);

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals(1, $user->id());
        $this->assertEquals('peng', $user->name);
    }

    public function testException()
    {
        $this->expectException(InvalidCredentialException::class);

        $userProviderStub = $this->createMock(UserProviderInterface::class);
        $userProviderStub->method('findByCredential')->willReturn(null);

        $credential = ['name' => 'peng'];
        $validator = new NoopCredentialValidator();
        $validator->validateCredential($userProviderStub, $credential);
    }
}
