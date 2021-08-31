<?php

namespace Gaara\Authentication\Authenticator;

/**
 * 用户令牌获取器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface TokenFetcherInterface
{
	/**
	 * 返回用户令牌
	 *
	 * @param string $tokenKey
	 * @return string|null
	 */
	function token(string $tokenKey): ?string;
}
