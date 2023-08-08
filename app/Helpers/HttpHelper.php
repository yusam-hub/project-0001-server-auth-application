<?php

namespace App\Helpers;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpTooManyRequestsAppExtRuntimeException;
use YusamHub\AppExt\Helpers\ExceptionHelper;
use YusamHub\AppExt\Redis\RedisKernel;

class HttpHelper
{
    /**
     * @param Request $request
     * @return string
     */
    public static function getUniqueUserDeviceFromRequest(Request $request): string
    {
        $server = $request->server->all();
        return md5($request->getClientIp() . ($server['HTTP_USER_AGENT']??'') . ($server['HTTP_ACCEPT_LANGUAGE']??''));
    }

    /**
     * @param RedisKernel $redisKernel
     * @param LoggerInterface $logger
     * @param string $unique
     * @param int $ttl
     * @param string $method
     * @return void
     */
    public static function checkTooManyRequestsOrFail(
        RedisKernel $redisKernel,
        LoggerInterface $logger,
        string $unique,
        int $ttl,
        string $method
    ): void
    {
        try {
            $redisExt = $redisKernel->connection();
            $redisKey = md5($unique . $ttl . $method);
            if ($redisExt->has($redisKey)) {
                $timeFinished = $redisExt->get($redisKey);
                throw new HttpTooManyRequestsAppExtRuntimeException(['timeLeftSeconds' => $timeFinished - time()]);
            }
            $redisExt->put($redisKey, time() + $ttl, $ttl);
        } catch (\Throwable $e) {
            if ($e instanceof HttpTooManyRequestsAppExtRuntimeException) {
                throw $e;
            }
            $logger->error($e->getMessage(), ExceptionHelper::e2a($e));
            throw new HttpInternalServerErrorAppExtRuntimeException([
                'method' => 'Some error in method'
            ]);
        }
    }
}