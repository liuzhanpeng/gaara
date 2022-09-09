<?php

namespace Gaara\Authenticator;

use Gaara\Identity;
use Gaara\UserInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * 基于token的认证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class TokenAuthenticator extends AbstractAuthenticator
{
    /**
     * 请求对象
     *
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * 缓存对象
     *
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * 令牌key
     *
     * @var string
     */
    protected string $tokenKey;

    /**
     * 令牌过期时间
     *
     * @var integer
     */
    protected int $timeout;

    /**
     * 加密salt
     *
     * @var string
     */
    protected string $salt;

    /**
     * token是否用完即弃
     *
     * @var boolean
     */
    protected bool $flash;

    /**
     * 支持认证后同时在线
     *
     * @var boolean
     */
    protected bool $concurrence;

    /**
     * 构造
     *
     * @param ServerRequestInterface $request
     * @param CacheInterface $cache
     * @param string $tokenKey
     * @param integer $timeout
     * @param string $salt
     * @param boolean $flash
     * @param boolean $concurrence
     */
    public function __construct(
        ServerRequestInterface $request,
        CacheInterface $cache,
        string $tokenKey,
        int $timeout,
        string $salt,
        bool $flash = false,
        bool $concurrence = false
    ) {
        $this->request = $request;
        $this->cache = $cache;
        $this->tokenKey = $tokenKey;
        $this->timeout = $timeout;
        $this->salt = $salt;
        $this->flash = $flash;
        $this->concurrence = $concurrence;
    }

    /**
     * @inheritDoc
     */
    public function setUser(UserInterface $user): Identity
    {
        $package = $this->generateTokenPackage($user);

        if ($this->concurrence) {
            $this->cache->set($this->getCacheKey($package['token']), $package['id'], $this->timeout);
        } else {
            $this->cache->set($this->getCacheKey($user->id()), $package['token'], $this->timeout);
        }

        return new Identity($user, [
            'token' => base64_encode(json_encode($package)),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated(): bool
    {
        return !is_null($this->getTokenPackage());
    }

    /**
     * @inheritDoc
     */
    public function id()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $package = $this->getTokenPackage();
        if (is_null($package)) {
            return null;
        }

        return $package['id'];
    }

    /**
     * @inheritDoc
     */
    public function user(): ?UserInterface
    {
        $package = $this->getTokenPackage();
        if (is_null($package)) {
            return null;
        }

        $user = $this->userProvider->findById($package['id']);
        if (is_null($user)) {
            return null;
        }

        if ($this->flash === true) {
            if ($this->concurrence) {
                $this->cache->delete($this->getCacheKey($package['token']));
            } else {
                $this->cache->delete($this->getCacheKey($package['id']));
            }
        } else {
            if ($this->concurrence) {
                $this->cache->set($this->getCacheKey($package['token']), $package['id'], $this->timeout);
            } else {
                $this->cache->set($this->getCacheKey($package['id']), $package['token'], $this->timeout);
            }
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function clearUser()
    {
        $package = $this->getTokenPackage();
        if (is_null($package)) {
            return;
        }

        $this->cache->delete($this->getCacheKey($package['id']));
    }

    /**
     * 返回token信息包
     *
     * @return array|null
     */
    protected function getTokenPackage(): ?array
    {
        $token = $this->request->getHeaderLine($this->tokenKey);
        if (empty($token)) {
            $queryParams = $this->request->getQueryParams();
            $token = $queryParams[$this->tokenKey] ?? '';
        }

        if (empty($token)) {
            return null;
        }

        $package = json_decode(base64_decode($token), true);
        if (
            is_null($package)
            || !isset($package['id'])
            || !isset($package['token'])
            || !isset($package['sign'])
        ) {
            return null;
        }

        // 验签
        $oriStr = sprintf('%s%s', $package['id'], $package['token']);
        $sign = hash_hmac('sha256', $oriStr, $this->salt);
        if (strcmp($sign, $package['sign']) !== 0) {
            return null;
        }

        // 对比token
        if ($this->concurrence) {
            $id = $this->cache->get($package['token']);
            if (is_null($id) || strcmp($id, $package['id']) !== 0) {
                return null;
            }
        } else {
            $token = $this->cache->get($this->getCacheKey($package['id']));
            if (is_null($token) || strcmp($token, $package['token']) !== 0) {
                return null;
            }
        }

        return $package;
    }

    /**
     * 生成token信息包
     *
     * @param UserInterface $user
     * @return array
     */
    protected function generateTokenPackage(UserInterface $user): array
    {
        $token = bin2hex(random_bytes(32));
        $oriStr = sprintf('%s%s', $user->id(), $token);

        return [
            'id' => $user->id(),
            'token' => $token,
            'sign' => hash_hmac('sha256', $oriStr, $this->salt),
        ];
    }

    /**
     * 返回缓存key
     *
     * @param mixed $userId
     * @return string
     */
    protected function getCacheKey($userId): string
    {
        return sprintf('%s-%s', $this->tokenKey, $userId);
    }
}
