<?php

namespace Gaara\Authorization\Authorizator;

use Gaara\Authorization\AuthorizatorInterface;
use Gaara\Authorization\Exception\AuthorizationException;
use Gaara\Authorization\ResourceProviderInterface;
use Gaara\User\UserInterface;

/**
 * 通用的授权器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class GenericAuthorizator implements AuthorizatorInterface
{
	/**
	 * 资源提供器
	 *
	 * @var ResourceProviderInterface
	 */
	private $resourceProvider;

	public function __construct()
	{
	}

	/**
	 * @inheritDoc
	 */
	public function setResorceProvider(ResourceProviderInterface $resourceProvider)
	{
		$this->resourceProvider = $resourceProvider;
	}

	/**
	 * @inheritDoc
	 *
	 * @param UserInterface $user 用户身份
	 * @param mixed $resourceId 资源标识
	 * @return boolean
	 */
	public function isAllowed(UserInterface $user, $resourceId): bool
	{
		if (is_null($this->resourceProvider)) {
			throw new AuthorizationException('授权器未设置资源提供器');
		}

		foreach ($this->resourceProvider->resources($user) as $resource) {
			if ($resource->id() === $resourceId) {
				return true;
			}
		}

		return false;
	}
}
