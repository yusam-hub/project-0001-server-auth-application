<?php

namespace App\Services;

use App\Model\Database\AppUserKeyModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

class AppService
{
    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $appId
     * @param int $userId
     * @param string $deviceUuid
     * @return array
     */
    public static function getUserKey(
        PdoExtKernelInterface $pdoExtKernel,
        int $appId,
        int $userId,
        string $deviceUuid
    ): array
    {
        $appUserKeyModel = AppUserKeyModel::findModelByAttributesOrFail($pdoExtKernel, [
            'appId' => $appId,
            'userId' => $userId,
            'deviceUuid' => $deviceUuid
        ]);
        return [
            'keyHash' => $appUserKeyModel->keyHash,
            'publicKey' => $appUserKeyModel->publicKey
        ];
    }
}