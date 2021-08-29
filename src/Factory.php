<?php

namespace Gaara;

use Gaara\Authentication\AuthenticatorInterface;
use Gaara\Authorization\AuthorizatorInterface;
use Gaara\Authentication\UserProviderInterface;
use Gaara\Authorization\ResourceProviderInterface;

/**
 * Gate工厂类
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Factory
{
	/**
	 * Gate实例列表
	 *
	 * @var array
	 */
	private static $gates = [];

	/**
	 * 配置
	 *
	 * @var array
	 */
	private static $config = [];

	/**
	 * 创建用户提供器的callback列表
	 *
	 * @var array
	 */
	private static $userProviders = [];

	/**
	 * 创建认证器的callback列表
	 *
	 * @var array
	 */
	private static $authenticators = [];

	/**
	 * 创建授权器的callback列表
	 *
	 * @var array
	 */
	private static $authorizators = [];

	/**
	 * 创建资源提供器的callback列表
	 *
	 * @var array
	 */
	private static $resourceProviders = [];

	/**
	 * 初始化
	 *
	 * @param array $config 配置
	 * @return void
	 */
	protected static function init(array $config)
	{
		static::$config = $config;
	}

	/**
	 * 注册用户提供器
	 *
	 * @param string $driver
	 * @param callable $callbadk
	 * @return void
	 */
	public static function registerUserProvider(string $driver, callable $callbadk)
	{
		static::$userProviders[$driver] = $callbadk;
	}

	/**
	 * 注册认证器
	 *
	 * @param string $driver
	 * @param callable $callbadk
	 * @return void
	 */
	public static function registerAuthenticator(string $driver, callable $callbadk)
	{
		static::$authenticators[$driver] = $callbadk;
	}

	/**
	 * 注册授权器
	 *
	 * @param string $driver
	 * @param callable $callbadk
	 * @return void
	 */
	public static function registerAuthorizator(string $driver, callable $callbadk)
	{
		static::$authorizators[$driver] = $callbadk;
	}

	/**
	 * 注册资源提供器
	 *
	 * @param string $driver
	 * @param callable $callbadk
	 * @return void
	 */
	public static function registerResourceProvider(string $driver, callable $callbadk)
	{
		static::$resourceProviders[$driver] = $callbadk;
	}

	/**
	 * 创建Gate实例
	 *
	 * @param string|null $name 名称
	 * @return Gate
	 */
	public static function make(?string $name = null): Gate
	{
		if (is_null($name)) {
			if (!isset(static::$config['default'])) {
				throw new \Exception('找不到default配置项');
			}

			$name = static::$config['default'];
		}

		if (!isset(static::$gates[$name])) {
			if (!isset($config['gates'][$name])) {
				throw new \Exception(sprintf('找不到gate[%s]配置', $name));
			}

			$config = static::$config['gates'][$name];

			$userProvider = static::createUserProvider($config['user_provider']);
			$authenticator = static::createAuthenticator($config['authenticator']);
			$authorizator = null;
			if (isset($config['authorizator'])) {
				$authorizator = static::createAuthorizator($config['authorizator']);
				if (!isset($config['resource_provider'])) {
					throw new \Exception(sprintf('找不到gate[%s]的resource_provider配置', $name));
				}

				$resourceProvider =  static::createResourceProvider($config['resource_provider']);
				$authorizator->setResorceProvider($resourceProvider);
			}

			static::$gates[$name] = new Gate($userProvider, $authenticator, $authorizator);
		}

		return static::$gates[$name];
	}

	/**
	 * 调用默认Gate实例方法
	 *
	 * @param strig $name
	 * @param mixed $arguments
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments)
	{
		return static::make()->{$name}(...$arguments);
	}

	/**
	 * 创建用户提供器实例
	 *
	 * @param array $config 配置
	 * @return UserProviderInterface
	 */
	private static function createUserProvider(array $config): UserProviderInterface
	{
		if (!isset(static::$userProviders[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到用户身份提供器驱动[%s]', $config['driver']));
		}

		$userProvider = call_user_func(static::$userProviders[$config['driver']], $config['params'] ?? []);

		if (!$userProvider instanceof UserProviderInterface) {
			throw new \InvalidArgumentException(sprintf('用户身份对象提供器[%s]必须实现UserProviderInterface', $config['driver']));
		}

		return $userProvider;
	}

	/**
	 * 创建认证器
	 *
	 * @param array $config 配置
	 * @return AuthenticatorInterface
	 */
	private static function createAuthenticator(array $config): AuthenticatorInterface
	{
		if (!isset(static::$authenticators[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到认证器驱动[%s]', $config['driver']));
		}

		$authenticator = call_user_func(static::$authenticators[$config['driver']], $config['params'] ?? []);

		if (!$authenticator instanceof AuthenticatorInterface) {
			throw new \InvalidArgumentException(sprintf('认证器[%s]必须实现AuthenticatorInterface', $config['driver']));
		}

		return $authenticator;
	}

	/**
	 * 创建授权器
	 *
	 * @param array $config 配置
	 * @return AuthorizatorInterface
	 */
	private static function createAuthorizator(array $config): AuthorizatorInterface
	{
		if (!isset(static::$authorizators[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到授权器驱动[%s]', $config['driver']));
		}

		$authorizator = call_user_func(static::$authorizators[$config['driver']], $config['params'] ?? []);

		if (!$authorizator instanceof AuthorizatorInterface) {
			throw new \InvalidArgumentException(sprintf('授权器[%s]必须实现AuthorizatorInterface', $config['driver']));
		}

		return $authorizator;
	}

	/**
	 * 创建资源提供器
	 *
	 * @param array $config 配置
	 * @return ResourceProviderInterface
	 */
	private static function createResourceProvider(array $config): ResourceProviderInterface
	{
		if (!isset(static::$resourceProviders[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到授权器驱动[%s]', $config['driver']));
		}

		$resourceProvider = call_user_func(static::$resourceProviders[$config['driver']], $config['params'] ?? []);

		if (!$resourceProvider instanceof ResourceProviderInterface) {
			throw new \InvalidArgumentException(sprintf('资源提供器[%s]必须实现ResourceProviderInterface', $config['driver']));
		}

		return $resourceProvider;
	}
}
