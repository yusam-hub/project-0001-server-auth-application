<?php

namespace App\Services;

use App\Model\Database\AppModel;
use Psr\Log\LoggerInterface;
use YusamHub\AppExt\Redis\RedisCacheUseFresh;
use YusamHub\AppExt\Redis\RedisKernel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;
use YusamHub\Helper\ArrayHelper;
use YusamHub\Helper\OpenSsl;

class AppService
{
    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @return array
     */
    public static function getAppList(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId
    ): array
    {
        $sqlRows = <<<MYSQL
select 
    id,
    title,
    keyHash
from 
    apps
where
    userId = :userId
order by 
    userId, id
MYSQL;
        return $pdoExtKernel->pdoExt()->fetchAll(strtr($sqlRows, [
            ':userId' => $userId
        ]));
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @param string $title
     * @return array
     */
    public static function postAppAdd(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId,
        string $title
    ): array
    {
        $openSsl = (new OpenSsl())->newPrivatePublicKeys();

        $appModel = new AppModel();
        $appModel->setPdoExtKernel($pdoExtKernel);
        $appModel->userId = $userId;
        $appModel->title = $title;
        $appModel->publicKey = $openSsl->getPublicKey();
        $appModel->keyHash = md5($appModel->publicKey);
        $appModel->saveOrFail();

        return [
            'appId' => $appModel->id,
            'keyHash' => $appModel->keyHash,
            'privateKey' => $openSsl->getPrivateKey()
        ];
    }
}