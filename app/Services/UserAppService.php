<?php

namespace App\Services;

use App\Model\Database\AppModel;
use App\Model\Database\AppUserKeyModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;
use YusamHub\Helper\OpenSsl;

class UserAppService
{
    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $appId
     * @param int $userId
     * @param string $deviceUuid
     * @return array
     */
    public static function postAppIdRefresh(
        PdoExtKernelInterface $pdoExtKernel,
        int $appId,
        int $userId,
        string $deviceUuid
    ): array
    {
        $openSsl = (new OpenSsl())->newPrivatePublicKeys();

        $appUserKeyModel = AppUserKeyModel::findModelByAttributes($pdoExtKernel, [
            'appId' => $appId,
            'userId' => $userId,
            'deviceUuid' => $deviceUuid
        ]);
        if (is_null($appUserKeyModel)) {
            $appUserKeyModel = new AppUserKeyModel();
            $appUserKeyModel->setPdoExtKernel($pdoExtKernel);
        }
        $appUserKeyModel->appId = $appId;
        $appUserKeyModel->userId = $userId;
        $appUserKeyModel->deviceUuid = $deviceUuid;
        $appUserKeyModel->publicKey = $openSsl->getPublicKey();
        $appUserKeyModel->keyHash = md5($appUserKeyModel->publicKey);
        $appUserKeyModel->serviceKey = md5($appUserKeyModel->keyHash . microtime());
        $appUserKeyModel->saveOrFail();

        return [
            'type' => 'assertion',
            'publicKeyHash' => $appUserKeyModel->keyHash,
            'privateKey' => $openSsl->getPrivateKey()
        ];
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @param int $appId
     * @return array
     */
    public static function getAppIdKeyList(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId,
        int $appId
    ): array
    {
        $sqlRows = <<<MYSQL
select 
    a.id as appId,
    a.title as appTitle,
    auk.deviceUuid,
    auk.keyHash,
    auk.createdAt,
    auk.modifiedAt
from 
    apps a, apps_users_keys auk
where
    a.id = auk.appId
    and auk.userId = :userId
    and auk.appId = :appId
order by 
    auk.createdAt
MYSQL;
        return $pdoExtKernel->pdoExt()->fetchAll(strtr($sqlRows, [
            ':userId' => $userId,
            ':appId' => $appId,
        ]));
    }

}