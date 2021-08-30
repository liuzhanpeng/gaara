<?php

namespace Gaara;

use Gaara\Authentication\AuthenticatorInterface;
use Gaara\Authentication\CredentialValidator\CallbackCredentialValidator;
use Gaara\Authentication\CredentialValidator\GenericCredentialValidator;
use Gaara\Authentication\CredentialValidator\PasswordHasherInterface;
use Gaara\Authentication\CredentialValidator\UsernamePasswordCredentialValidator;
use Gaara\Authentication\CredentialValidatorInterface;
use Gaara\Authorization\AuthorizatorInterface;
use Gaara\Authentication\UserProviderInterface;
use Gaara\Authorization\Authorizator\GenericAuthorizator;
use Gaara\Authorization\ResourceProviderInterface;

/**
 * Gate管理器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class GateManager
{
	/**
	 * Gate实例列表
	 *
	 * @var array
	 */
	private $gates = [];

	/**
	 * 配置
	 *
	 * @var array
	 */
	private $config = [];

	/**
	 * 创建用户提供器的callback列表
	 *
	 * @var array
	 */
	private $userProviders = [];

	/**
	 * 创建认证器的callback列表
	 *
	 * @var array
	 */
	private $authenticators = [];

	/**
	 * 创建登录凭证验证器的callback列表
	 *
	 * @var array
	 */
	private $credentialValidators = [];

	/**
	 * 创建授权器的callback列表
	 *
	 * @var array
	 */
	private $authorizators = [];

	/**
	 * 创建资源提供器的callback列表
	 *
	 * @var array
	 */
	private $resourceProviders = [];

	/**
	 * 初始化
	 *
	 * @param array $config 配置
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * 初始化
	 * 注册默认的驱动
	 *
	 * @return void
	 */
	public function init()
	{
		$this->registerCredentialValidator('generic', function (array $params) {
			return new GenericCredentialValidator();
		});

		$this->registerCredentialValidator('username_password', function (array $params) {
			$passwordKey = $params['password_key'] ?? 'password';
			if (!isset($params['password_hasher']) || !$params['password_hasher'] instanceof PasswordHasherInterface) {
				throw new \Exception('配置项password_hasher必须是实现PasswordHasherInterface接口的实例');
			}
			return new UsernamePasswordCredentialValidator($passwordKey, $params['passwordHasher']);
		});

		$this->registerCredentialValidator('callback', function (array $params) {
			return new CallbackCredentialValidator();
		});

		$this->registerAuthorizator('generic', function (array $params) {
			return new GenericAuthorizator();
		});
	}

	/**
	 * 注册用户提供器
	 *
	 * @param string $driver
	 * @param callable $callbadk
	 * @return void
	 */
	public function registerUserProvider(string $driver, callable $callbadk)
	{
		$this->userProviders[$driver] = $callbadk;
	}

	/**
	 * 注册认证器
	 *
	 * @param string $driver
	 * @param callable $callbadk
	 * @return void
	 */
	public function registerAuthenticator(string $driver, callable $callbadk)
	{
		$this->authenticators[$driver] = $callbadk;
	}

	/**
	 * 注册登录凭证验证器
	 *
	 * @param string $driver
	 * @param callable $callbadk
	 * @return void
	 */
	public function registerCredentialValidator(string $driver, callable $callbadk)
	{
		$this->credentialValidators[$driver] = $callbadk;
	}

	/**
	 * 注册授权器
	 *
	 * @param string $driver
	 * @param callable $callbadk
	 * @return void
	 */
	public function registerAuthorizator(string $driver, callable $callbadk)
	{
		$this->authorizators[$driver] = $callbadk;
	}

	/**
	 * 注册资源提供器
	 *
	 * @param string $driver
	 * @param callable $callbadk
	 * @return void
	 */
	public function registerResourceProvider(string $driver, callable $callbadk)
	{
		$this->resourceProviders[$driver] = $callbadk;
	}

	/**
	 * 创建Gate实例
	 *
	 * @param string|null $name 名称
	 * @return Gate
	 */
	public function make(?string $name = null): Gate
	{
		if (is_null($name)) {
			if (!isset($this->config['default'])) {
				throw new \Exception('找不到default配置项');
			}

			$name = $this->config['default'];
		}

		if (!isset($this->gates[$name])) {
			if (!isset($this->config['gates'][$name])) {
				throw new \Exception(sprintf('找不到gate[%s]配置', $name));
			}

			$config = $this->config['gates'][$name];

			$userProvider = $this->createUserProvider($config['user_provider']);
			$authenticator = $this->createAuthenticator($config['authenticator']);
			$credentialValidator = $this->createCredentialValidator($config['credential_validator']);
			$authorizator = null;
			if (isset($config['authorizator'])) {
				$authorizator = $this->createAuthorizator($config['authorizator']);
				if (!isset($config['resource_provider'])) {
					throw new \Exception(sprintf('找不到gate[%s]的resource_provider配置', $name));
				}

				$resourceProvider = $this->createResourceProvider($config['resource_provider']);
				$authorizator->setResorceProvider($resourceProvider);
			}

			$this->gates[$name] = new Gate($userProvider, $authenticator, $credentialValidator, $authorizator);
		}

		return $this->gates[$name];
	}

	/**
	 * 调用默认Gate实例方法
	 *
	 * @param strig $name
	 * @param mixed $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		return $this->make()->{$name}(...$arguments);
	}

	/**
	 * 创建用户提供器实例
	 *
	 * @param array $config 配置
	 * @return UserProviderInterface
	 */
	private function createUserProvider(array $config): UserProviderInterface
	{
		if (!isset($this->userProviders[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到用户身份提供器驱动[%s]', $config['driver']));
		}

		$userProvider = call_user_func($this->userProviders[$config['driver']], $config['params'] ?? []);

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
	private function createAuthenticator(array $config): AuthenticatorInterface
	{
		if (!isset($this->authenticators[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到认证器驱动[%s]', $config['driver']));
		}

		$authenticator = call_user_func($this->authenticators[$config['driver']], $config['params'] ?? []);

		if (!$authenticator instanceof AuthenticatorInterface) {
			throw new \InvalidArgumentException(sprintf('认证器[%s]必须实现AuthenticatorInterface', $config['driver']));
		}

		return $authenticator;
	}

	/**
	 * 创建登录凭证验证器
	 *
	 * @param array $config 配置
	 * @return AuthenticatorInterface
	 */
	private function createCredentialValidator(array $config): CredentialValidatorInterface
	{
		if (!isset($this->credentialValidators[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到登录凭证验证器驱动[%s]', $config['driver']));
		}

		$credentialValidator = call_user_func($this->credentialValidators[$config['driver']], $config['params'] ?? []);

		if (!$credentialValidator instanceof CredentialValidatorInterface) {
			throw new \InvalidArgumentException(sprintf('登录凭证验证器[%s]必须实现CredentialValidatorInterface', $config['driver']));
		}

		return $credentialValidator;
	}

	/**
	 * 创建授权器
	 *
	 * @param array $config 配置
	 * @return AuthorizatorInterface
	 */
	private function createAuthorizator(array $config): AuthorizatorInterface
	{
		if (!isset($this->authorizators[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到授权器驱动[%s]', $config['driver']));
		}

		$authorizator = call_user_func($this->authorizators[$config['driver']], $config['params'] ?? []);

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
	private function createResourceProvider(array $config): ResourceProviderInterface
	{
		if (!isset($this->resourceProviders[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到授权器驱动[%s]', $config['driver']));
		}

		$resourceProvider = call_user_func($this->resourceProviders[$config['driver']], $config['params'] ?? []);

		if (!$resourceProvider instanceof ResourceProviderInterface) {
			throw new \InvalidArgumentException(sprintf('资源提供器[%s]必须实现ResourceProviderInterface', $config['driver']));
		}

		return $resourceProvider;
	}
}
