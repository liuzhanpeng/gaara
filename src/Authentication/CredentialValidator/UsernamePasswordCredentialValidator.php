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
	 * @var PasswordHasherInterfacer|callable
	 */
	protected $passwordHasher;

	/**
	 * 构造
	 *
	 * @param string $passwordKey 密码在凭证数组中的key
	 * @param PasswordHasherInterface|callable $passwordHasher
	 */
	public function __construct(string $passwordKey, $passwordHasher)
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
		if (!isset($credential[$this->passwordKey])) {
			throw new InvalidCredentialException(sprintf('登录凭证没找到密码项[%s]', $this->passwordKey));
		}

		$user = $userProvider->findByParams($credential);
		if (is_null($user)) {
			throw new InvalidCredentialException('无效登录凭证');
		}

		if (!$user instanceof PasswordInterface) {
			throw new AuthenticationException('用户身份对象必须实现PasswordInterface接口');
		}

		if ($this->passwordHasher instanceof PasswordHasherInterface) {
			if (!$this->passwordHasher->verify($user->password(), $this->params[$this->passwordKey])) {
				throw new InvalidCredentialException('密码错误');
			}
		} elseif (is_callable($this->passwordHasher)) {
			$result = call_user_func($this->passwordHasher, $user->password(), $this->params[$this->passwordKey]);
			if ($result !== true) {
				throw new InvalidCredentialException('密码错误');
			}
		} else {
			throw new AuthenticationException('不支持的PasswordHasher类型');
		}

		return $user;
	}
}
