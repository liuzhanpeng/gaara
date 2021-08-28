<?php

namespace Gaara\Authentication\Credential;

use Gaara\Authentication\CredentialInterface;
use Gaara\Authentication\Exception\InvalidCredentialException;
use Gaara\User\UserProviderInterface;
use Gaara\User\UserInterface;

/**
 * 通用的登录凭证
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class GenericCendential implements CredentialInterface
{
	protected $params = [];

	public function __construct(array $params)
	{
		$this->params = $params;
	}

	/**
	 * @inheritDoc
	 */
	public function validate(UserProviderInterface $userProvider): UserInterface
	{
		$user = $userProvider->findByParams($this->params);

		if (is_null($user)) {
			throw new InvalidCredentialException('无效登录凭证');
		}

		return $user;
	}
}
