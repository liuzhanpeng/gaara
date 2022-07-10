<?php

namespace Gaara;

use Gaara\Accessor\GenericAccessor;
use Gaara\Accessor\PermissionProviderInterface;
use Gaara\Authenticator\SessionAuthenticator;
use Gaara\Authenticator\SessionInterface;
use Gaara\Authenticator\TokenAuthenticator;
use Gaara\CredentialValidator\NoopCredentialValidator;
use Gaara\CredentialValidator\PasswordCredentialValidator;
use Gaara\Event\EventDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Gaara
 * - 注册UserProvider、Authenticator、Accessor
 * - 创建Guard
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Gaara
{
    /**
     * 配置
     *
     * @var array
     */
    protected array $config;

    /**
     * 默认Guard名称
     *
     * @var string
     */
    protected string $defaultName;

    /**
     * Guard实例列表
     *
     * @var array
     */
    private array $guards;

    /**
     * DI容器
     *
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * 事件分发器
     *
     * @var EventDispatcher|null
     */
    protected ?EventDispatcher $eventDispatcher;

    /**
     * UserProvider注册列表
     *
     * @var array
     */
    protected array $userProviderRegistry;

    /**
     * CredentialValidator注册列表
     *
     * @var array
     */
    protected array $credentialValidatorRegistry;

    /**
     * Authenticator注册列表
     *
     * @var array
     */
    protected array $authenticatorRegistry;

    /**
     * Accessor注册列表
     *
     * @var array
     */
    protected array $accessorRegistry;

    /**
     * 构造
     *
     * @param array $config
     */
    public function __construct(
        array $config,
        string $defaultName = 'default',
        bool $registerDefaultDriver = true
    ) {
        $this->config = $config;
        $this->defaultName = $defaultName;
        $this->guards = [];
        $this->eventDispatcher = null;

        if ($registerDefaultDriver) {
            $this->registerDefaultDriver();
        }
    }

    /**
     * 注册内置的Driver
     *
     * @return void
     */
    protected function registerDefaultDriver()
    {
        $this->registerAuthenticator('session', function (?ContainerInterface $container, array $config) {
            $key = $config['key'] ?? '__user__';
            $session = null;
            if (!isset($config['session'])) {
                if (!is_null($container) && $container->has(SessionInterface::class)) {
                    $session = $container->get(SessionInterface::class);
                }
            } elseif (is_callable($config['session'])) {
                $session = call_user_func($config['session'], $container);
            } else {
                $session = $config['session'];
            }

            if (is_null($session) || !$session instanceof SessionInterface) {
                throw new \Exception('SessionAuthenticator无法创建Session对象');
            }

            return new SessionAuthenticator($session, $key);
        });

        $this->registerAuthenticator('token', function (?ContainerInterface $container, array $config) {
            $key = $config['key'] ?? '__token__';
            $timeout = $config['timeout'] ?? 60 * 30;
            $flash = $config['flash'] ?? false;
            if (!isset($config['salt'])) {
                throw new \Exception('TokenAuthenticator必须配置salt');
            }

            $request = null;
            if (!isset($config['request'])) {
                if (!is_null($container) && $container->has(ServerRequestInterface::class)) {
                    $request = $container->get(ServerRequestInterface::class);
                }
            } elseif (is_callable($config['request'])) {
                $request = call_user_func($config['request'], $container);
            } else {
                $request = $config['request'];
            }

            if (is_null($request) && !$request instanceof ServerRequestInterface) {
                throw new \Exception('TokenAuthenticator无法创建Request对象');
            }

            $cache = null;
            if (!isset($config['cache'])) {
                if (!is_null($container) && $container->has(CacheInterface::class)) {
                    $cache = $container->get(CacheInterface::class);
                }
            } elseif (is_callable($config['cache'])) {
                $cache = call_user_func($config['cache'], $container);
            } else {
                $cache = $config['cache'];
            }

            if (is_null($cache) && !$cache instanceof CacheInterface) {
                throw new \Exception('TokenAuthenticator无法创建Cache对象');
            }

            return new TokenAuthenticator($request, $cache, $key, $timeout, $config['salt'], $flash);
        });

        $this->registerCredentialValidator('noop', function (?ContainerInterface $container, array $config) {
            return new NoopCredentialValidator();
        });

        $this->registerCredentialValidator('password', function (?ContainerInterface $container, array $config) {
            $passwordField = $config['field'] ?? 'password';

            return new PasswordCredentialValidator($passwordField);
        });

        $this->registerAccessor('generic', function (?ContainerInterface $container, array $config) {
            $permissionProvider = null;
            if (!isset($config['permission_provider'])) {
                if (!is_null($container) && $container->has(PermissionProviderInterface::class)) {
                    $permissionProvider = $container->get(PermissionProviderInterface::class);
                }
            } elseif (is_callable($config['permission_provider'])) {
                $permissionProvider = call_user_func($config['permission_provider'], $container);
            } else {
                $permissionProvider = $config['permission_provider'];
            }

            if (is_null($permissionProvider) || !$permissionProvider instanceof PermissionProviderInterface) {
                throw new \Exception('GenericAccessor无法创建PermissionProvider对象');
            }

            return new GenericAccessor($permissionProvider);
        });
    }

    /**
     * 设置DI容器
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 返回事件分发器
     *
     * @return EventDispatcher
     */
    protected function eventDispatcher(): EventDispatcher
    {
        if (is_null($this->eventDispatcher)) {
            $this->eventDispatcher = new EventDispatcher($this->container);
        }

        return $this->eventDispatcher;
    }

    /**
     * 注册UserProvider
     *
     * @param string $driver
     * @param callable $callback
     * @return void
     */
    public function registerUserProvider(string $driver, callable $callback)
    {
        $this->userProviderRegistry[$driver] = $callback;
    }

    /**
     * 注册CredentialValidator
     *
     * @param string $driver
     * @param callable $callback
     * @return void
     */
    public function registerCredentialValidator(string $driver, callable $callback)
    {
        $this->credentialValidatorRegistry[$driver] = $callback;
    }

    /**
     * 注册Authenticator
     *
     * @param string $driver
     * @param callable $callback
     * @return void
     */
    public function registerAuthenticator(string $driver, callable $callback)
    {
        $this->authenticatorRegistry[$driver] = $callback;
    }

    /**
     * 注册Accessor
     *
     * @param string $driver
     * @param callable $callback
     * @return void
     */
    public function registerAccessor(string $driver, callable $callback)
    {
        $this->accessorRegistry[$driver] = $callback;
    }

    /**
     * 创建Guard
     *
     * @param string $name
     * @return Guard
     */
    public function make(?string $name = null): Guard
    {
        $name = $name ?? $this->defaultName;
        if (!isset($this->guards[$name])) {
            if (
                !isset($this->config[$name])
                || !is_array($this->config[$name])
                || !isset($this->config[$name]['authenticator'])
                || !isset($this->config[$name]['user_provider'])
            ) {
                throw new \Exception(sprintf('[Gaara配置错误]找不到有效的Guard[%s]配置项', $name));
            }

            $guardConfig = $this->config[$name];

            $userProvider = $this->createUserProvider($guardConfig['user_provider']);
            if (!isset($guardConfig['credential_validator'])) {
                $credentialValidator = new NoopCredentialValidator();
            } else {
                $credentialValidator = $this->createCredentialValidator($guardConfig['credential_validator']);
            }
            $authenticator = $this->createAuthenticator($guardConfig['authenticator']);
            $authenticator->setUserProvider($userProvider);
            $authenticator->setCredentialValidator($credentialValidator);

            $accessor = null;
            if (isset($guardConfig['accessor'])) {
                $accessor = $this->createAccessor($guardConfig['accessor']);
            }

            // 注册事件监听器
            $eventDispatcher = $this->eventDispatcher();
            if (isset($guardConfig['event'])) {
                foreach ($guardConfig['event'] as $eventName => $listeners) {
                    foreach ($listeners as $listener) {
                        $eventName = sprintf('%s.%s', $name, $eventName);
                        $eventDispatcher->addListener($eventName, $listener);
                    }
                }
            }

            $this->guards[$name] = new Guard($name, $authenticator, $accessor, $eventDispatcher);
        }

        return $this->guards[$name];
    }

    /**
     * 调用默认Guard实例方法
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
     * 创建UserProvider
     *
     * @param array $config
     * @return UserProviderInterface
     */
    protected function createUserProvider(array $config): UserProviderInterface
    {
        if (!isset($this->userProviderRegistry[$config['driver']])) {
            throw new \Exception(sprintf('找不到UserProvider驱动[%s]', $config['driver']));
        }

        $userProvider = call_user_func($this->userProviderRegistry[$config['driver']], $this->container, $config);
        if (!$userProvider instanceof UserProviderInterface) {
            throw new \Exception('UserProvider驱动[%s]返回对象未实现UserProviderInterface', $config['driver']);
        }

        return $userProvider;
    }

    /**
     * 创建CredentialValidator
     *
     * @param array $config
     * @return CredentialValidatorInterface
     */
    protected function createCredentialValidator(array $config): CredentialValidatorInterface
    {
        if (!isset($this->credentialValidatorRegistry[$config['driver']])) {
            throw new \Exception(sprintf('找不到CredentialValidator驱动[%s]', $config['driver']));
        }

        $credentialValidator = call_user_func($this->credentialValidatorRegistry[$config['driver']], $this->container, $config);
        if (!$credentialValidator instanceof CredentialValidatorInterface) {
            throw new \Exception('CredentialValidator驱动[%s]返回对象未实现CredentialValidatorInterface', $config['driver']);
        }

        return $credentialValidator;
    }

    /**
     * 创建Authenticator
     *
     * @param array $config
     * @return AuthenticatorInterface
     */
    protected function createAuthenticator(array $config): AuthenticatorInterface
    {
        if (!isset($this->authenticatorRegistry[$config['driver']])) {
            throw new \Exception(sprintf('找不到Authenticator驱动[%s]', $config['driver']));
        }

        $authenticator = call_user_func($this->authenticatorRegistry[$config['driver']], $this->container, $config);
        if (!$authenticator instanceof AuthenticatorInterface) {
            throw new \Exception('Authenticator驱动[%s]返回对象未实现AuthenticatorInterface', $config['driver']);
        }

        return $authenticator;
    }

    /**
     * 创建Accessor
     *
     * @param array $config
     * @return AccessorInterface
     */
    protected function createAccessor(array $config): AccessorInterface
    {
        if (!isset($this->accessorRegistry[$config['driver']])) {
            throw new \Exception(sprintf('找不到Accessor驱动[%s]', $config['driver']));
        }

        $accessor = call_user_func($this->accessorRegistry[$config['driver']], $this->container, $config);
        if (!$accessor instanceof AccessorInterface) {
            throw new \Exception('Accessor驱动[%s]返回对象未实现AccessorInterface', $config['driver']);
        }

        return $accessor;
    }
}
