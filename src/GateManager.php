<?php

namespace Gaara;

use Gaara\Authentication\Authenticator\SessionAuthenticator;
use Gaara\Authentication\Authenticator\SessionInterface;
use Gaara\Authentication\Authenticator\TokenAuthenticator;
use Gaara\Authentication\Authenticator\TokenFetcherInterface;
use Gaara\Authentication\AuthenticatorInterface;
use Gaara\Authentication\CredentialValidator\CallbackCredentialValidator;
use Gaara\Authentication\CredentialValidator\GenericCredentialValidator;
use Gaara\Authentication\CredentialValidator\PasswordHasherInterface;
use Gaara\Authentication\CredentialValidator\UsernamePasswordCredentialValidator;
use Gaara\Authentication\CredentialValidatorInterface;
use Gaara\Authorization\AuthorizatorInterface;
use Gaara\Authentication\UserProviderInterface;
use Gaara\Authorization\Authorizator\GenericAuthorizator;
use Gaara\Authentication\Authorizator\OnceTokenAuthenticator;
use Gaara\Authorization\ResourceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

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
	 * DI容器
	 *
	 * @var ContainerInterface
	 */
	private $container;

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
		$this->registerAuthenticator('session', function (array $params, ?ContainerInterface $container) {
			$sessionKey = $params['session_key'] ?? 'user';
			$mode = $params['mode'] ?? SessionAuthenticator::MODE_ONLY_ID;
			if (!isset($params['session'])) {
				if (is_null($container)) {
					throw new \Exception('未设置DI容器，自动获取Session类失败');
				}
				$session = $container->get(SessionInterface::class);
			} else {
				$session = $params['session'];
				if (is_string($session) && !is_null($container)) {
					$session = $container->get($session);
				}
			}

			if (!$session instanceof SessionInterface) {
				throw new \Exception('SessionAuthenticator认证器配置项session必须是实现SessionInterface的实例');
			}

			return new SessionAuthenticator($sessionKey, $session, $mode);
		});

		$this->registerAuthenticator('token', function (array $params, ?ContainerInterface $container) {
			$tokenKey = $params['token_key'] ?? 'token';
			if (!isset($params['salt'])) {
				throw new \Exception('TokenAuthenticator认证器配置项salt必须提供');
			}
			$salt = $params['salt'];
			$timeout = $params['timeout'] ?? 60 * 30;
			if (!isset($params['token_fetcher'])) {
				if (is_null($container)) {
					throw new \Exception('未设置DI容器，自动获取TokenFetcher类失败');
				}
				$tokenFetcher = $container->get(TokenFetcherInterface::class);
			} else {
				$tokenFetcher = $params['token_fetcher'];
				if (is_string($tokenFetcher) && !is_null($container)) {
					$tokenFetcher = $container->get($tokenFetcher);
				}
			}

			if (!($tokenFetcher instanceof TokenFetcherInterface || is_callable($tokenFetcher))) {
				throw new \Exception('TokenAuthenticator认证器配置项token_fetcher必须是实现TokenFetcherInterface的实例或callable类型');
			}

			if (!isset($params['cache'])) {
				if (is_null($container)) {
					throw new \Exception('未设置DI容器，自动获取Cache类失败');
				}
				$cache = $container->get(CacheInterface::class);
			} else {
				$cache = $params['cache'];
				if (is_string($cache) && !is_null($container)) {
					$cache = $container->get($cache);
				}
			}

			if (!$cache instanceof CacheInterface) {
				throw new \Exception('TokenAuthenticator认证器配置项cache必须是实现CacheInterface的实例');
			}

			return new TokenAuthenticator($tokenKey, $salt, $timeout, $tokenFetcher, $cache);
		});

		$this->registerAuthenticator('once_token', function (array $params, ?ContainerInterface $container) {
			$tokenKey = $params['token_key'] ?? 'token';
			$expire = $params['expire'] ?? 60 * 5;

			if (!isset($params['token_fetcher'])) {
				if (is_null($container)) {
					throw new \Exception('未设置DI容器，自动获取TokenFetcher类失败');
				}
				$tokenFetcher = $container->get(TokenFetcherInterface::class);
			} else {
				$tokenFetcher = $params['token_fetcher'];
				if (is_string($tokenFetcher) && !is_null($container)) {
					$tokenFetcher = $container->get($tokenFetcher);
				}
			}

			if (!($tokenFetcher instanceof TokenFetcherInterface || is_callable($tokenFetcher))) {
				throw new \Exception('TokenAuthenticator认证器配置项token_fetcher必须是实现TokenFetcherInterface的实例或callable类型');
			}

			if (!isset($params['cache'])) {
				if (is_null($container)) {
					throw new \Exception('未设置DI容器，自动获取Cache类失败');
				}
				$cache = $container->get(CacheInterface::class);
			} else {
				$cache = $params['cache'];
				if (is_string($cache) && !is_null($container)) {
					$cache = $container->get($cache);
				}
			}

			if (!$cache instanceof CacheInterface) {
				throw new \Exception('OnceTokenAuthenticator认证器配置项cache必须是实现CacheInterface的实例');
			}

			return new OnceTokenAuthenticator($tokenKey, $expire, $tokenFetcher, $cache);
		});

		$this->registerCredentialValidator('generic', function (array $params, ?ContainerInterface $container) {
			return new GenericCredentialValidator();
		});

		$this->registerCredentialValidator('username_password', function (array $params, ?ContainerInterface $container) {
			$passwordKey = $params['password_key'] ?? 'password';
			if (!isset($params['password_hasher'])) {
				if (is_null($container)) {
					throw new \Exception('未设置DI容器，自动获取PasswordHasher类失败');
				}
				$passwordHasher = $container->get(PasswordHasherInterface::class);
			} else {
				$passwordHasher = $params['password_hasher'];
				if (is_string($passwordHasher) && !is_null($container)) {
					$passwordHasher = $container->get($passwordHasher);
				}
			}

			if ($passwordHasher instanceof PasswordHasherInterface || is_callable($passwordHasher)) {
				return new UsernamePasswordCredentialValidator($passwordKey, $passwordHasher);
			}

			throw new \Exception('配置项password_hasher必须是实现PasswordHasherInterface接口的实例或callable类型');
		});

		$this->registerCredentialValidator('callback', function (array $params, ?ContainerInterface $container) {
			return new CallbackCredentialValidator();
		});

		$this->registerAuthorizator('generic', function (array $params, ?ContainerInterface $container) {
			return new GenericAuthorizator();
		});
	}

	/**
	 * 设置DI容器
	 *
	 * @param ContainerInterface|null $container
	 * @return void
	 */
	public function setContainer(?ContainerInterface $container = null)
	{
		$this->container = $container;
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

			$userProvider = $this->createUserProvider($config['user_provider'], $this->container);
			$authenticator = $this->createAuthenticator($config['authenticator'], $this->container);
			$credentialValidator = $this->createCredentialValidator($config['credential_validator'], $this->container);
			$authorizator = null;
			if (isset($config['authorizator'])) {
				$authorizator = $this->createAuthorizator($config['authorizator'], $this->container);
				if (!isset($config['resource_provider'])) {
					throw new \Exception(sprintf('找不到gate[%s]的resource_provider配置', $name));
				}

				$resourceProvider = $this->createResourceProvider($config['resource_provider'], $this->container);
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
	 * @param ContainerInterface|null $container DI容器
	 * @return UserProviderInterface
	 */
	private function createUserProvider(array $config, ?ContainerInterface $container): UserProviderInterface
	{
		if (!isset($this->userProviders[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到用户身份提供器驱动[%s]', $config['driver']));
		}

		$userProvider = call_user_func($this->userProviders[$config['driver']], $config['params'] ?? [], $container);

		if (!$userProvider instanceof UserProviderInterface) {
			throw new \InvalidArgumentException(sprintf('用户身份对象提供器[%s]必须实现UserProviderInterface', $config['driver']));
		}

		return $userProvider;
	}

	/**
	 * 创建认证器
	 *
	 * @param array $config 配置
	 * @param ContainerInterface|null $container DI容器
	 * @return AuthenticatorInterface
	 */
	private function createAuthenticator(array $config, ?ContainerInterface $container): AuthenticatorInterface
	{
		if (!isset($this->authenticators[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到认证器驱动[%s]', $config['driver']));
		}

		$authenticator = call_user_func($this->authenticators[$config['driver']], $config['params'] ?? [], $container);

		if (!$authenticator instanceof AuthenticatorInterface) {
			throw new \InvalidArgumentException(sprintf('认证器[%s]必须实现AuthenticatorInterface', $config['driver']));
		}

		return $authenticator;
	}

	/**
	 * 创建登录凭证验证器
	 *
	 * @param array $config 配置
	 * @param ContainerInterface|null $container DI容器
	 * @return CredentialValidatorInterface
	 */
	private function createCredentialValidator(array $config, ?ContainerInterface $container): CredentialValidatorInterface
	{
		if (!isset($this->credentialValidators[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到登录凭证验证器驱动[%s]', $config['driver']));
		}

		$credentialValidator = call_user_func($this->credentialValidators[$config['driver']], $config['params'] ?? [], $container);

		if (!$credentialValidator instanceof CredentialValidatorInterface) {
			throw new \InvalidArgumentException(sprintf('登录凭证验证器[%s]必须实现CredentialValidatorInterface', $config['driver']));
		}

		return $credentialValidator;
	}

	/**
	 * 创建授权器
	 *
	 * @param array $config 配置
	 * @param ContainerInterface|null $container DI容器
	 * @return AuthorizatorInterface
	 */
	private function createAuthorizator(array $config, ?ContainerInterface $container): AuthorizatorInterface
	{
		if (!isset($this->authorizators[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到授权器驱动[%s]', $config['driver']));
		}

		$authorizator = call_user_func($this->authorizators[$config['driver']], $config['params'] ?? [], $container);

		if (!$authorizator instanceof AuthorizatorInterface) {
			throw new \InvalidArgumentException(sprintf('授权器[%s]必须实现AuthorizatorInterface', $config['driver']));
		}

		return $authorizator;
	}

	/**
	 * 创建资源提供器
	 *
	 * @param array $config 配置
	 * @param ContainerInterface|null $container DI容器
	 * @return ResourceProviderInterface
	 */
	private function createResourceProvider(array $config, ?ContainerInterface $container): ResourceProviderInterface
	{
		if (!isset($this->resourceProviders[$config['driver']])) {
			throw new \InvalidArgumentException(sprintf('找不到授权器驱动[%s]', $config['driver']));
		}

		$resourceProvider = call_user_func($this->resourceProviders[$config['driver']], $config['params'] ?? [], $container);

		if (!$resourceProvider instanceof ResourceProviderInterface) {
			throw new \InvalidArgumentException(sprintf('资源提供器[%s]必须实现ResourceProviderInterface', $config['driver']));
		}

		return $resourceProvider;
	}
}
