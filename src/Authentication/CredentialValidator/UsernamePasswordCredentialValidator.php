<?php

namespace Gaara\Authentication\CredentialValidator;

use Gaara\Authentication\CredentialValidatorInterface;
use Gaara\Authentication\Exception\AuthenticationException;
use Gaara\Authentication\Exception\InvalidCredentialException;
use Gaara\Authentication\UserProviderInterface;
use Gaara\User\UserInterface;

/**
 * 基于用户密码的凭证验证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class UsernamePasswordCredentialValidator implements CredentialValidatorInterface
{
	/**
	 * 密码key
	 *
	 * @var string
	 */
	protected $passwordKey;

	/**
	 * password hasher
	 *
	 * @var PasswordHasherInterfacer
	 */
	protected $passwordHasher;

	public function __construct(string $passwordKey, PasswordHasherInterface $passwordHasher)
	{
		$this->passwordKey = $passwordKey;
		$this->passwordHasher = $passwordHasher;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($credential, UserProviderInterface $userProvider): UserInterface
	{
		if (!is_array($credential)) {
			throw new InvalidCredentialException('登录凭证必须是数组类型');
		}

		$user = $userProvider->findByParams($this->params);

		if (!$user instanceof PasswordInterface) {
			throw new AuthenticationException('用户身份对象必须实现PasswordInterface接口');
		}

		if (!$this->passwordHasher->verify($user->password(), $this->params[$this->passwordKey])) {
			throw new InvalidCredentialException('密码错误');
		}

		return $user;
	}
}
