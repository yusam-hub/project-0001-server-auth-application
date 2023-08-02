<?php

namespace App\Helpers;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpTooManyRequestsAppExtRuntimeException;
use YusamHub\AppExt\Traits\Interfaces\GetSetLoggerInterface;

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
     * @param LoggerInterface $logger
     * @param string $unique
     * @param int $ttl
     * @param string $method
     * @return void
     */
    public static function checkTooManyRequestsOrFail(
        LoggerInterface $logger,
        string $unique,
        int $ttl,
        string $method
    ): void
    {
        try {
            $redisExt = app_ext_redis_global()->redisExt();
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
            $logger->error($e->getMessage(), [
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);
            throw new HttpInternalServerErrorAppExtRuntimeException([
                'method' => 'Some error in method'
            ]);
        }
    }
}