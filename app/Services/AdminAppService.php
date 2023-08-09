<?php

namespace App\Services;

use App\Model\Database\AppModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;
use YusamHub\Helper\OpenSsl;

class AdminAppService
{
    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @return int
     */
    public static function getAppCount(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId
    ): int
    {
        $sqlRows = <<<MYSQL
select 
    count(id) as countRows
from 
    apps
where
    userId = :userId
MYSQL;
        return (int) $pdoExtKernel->pdoExt()->fetchOneColumn(strtr($sqlRows, [
            ':userId' => $userId
        ]),'countRows');
    }

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
    id as appId,
    title,
    keyHash,
    createdAt,
    modifiedAt
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
        $appModel->serviceKey = md5($appModel->keyHash . microtime());
        $appModel->saveOrFail();

        return [
            'appId' => $appModel->id,
            'keyHash' => $appModel->keyHash,
            'privateKey' => $openSsl->getPrivateKey(),
        ];
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @param int $appId
     * @return array
     */
    public static function getAppId(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId,
        int $appId
    ): array
    {
        $sqlRows = <<<MYSQL
select 
    id as appId,
    title,
    keyHash,
    createdAt,
    modifiedAt
from 
    apps
where
    userId = :userId
    and id = :appId
limit 0,1
MYSQL;
        return $pdoExtKernel->pdoExt()->fetchAll(strtr($sqlRows, [
            ':userId' => $userId,
            ':appId' => $appId,
        ]));
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @param int $appId
     * @param string $title
     * @return array
     */
    public static function putAppIdChangeTitle(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId,
        int $appId,
        string $title
    ): array
    {
        $appModel = AppModel::findModelByAttributesOrFail($pdoExtKernel, [
            'id' => $appId,
            'userId' => $userId
        ]);
        $appModel->title = $title;
        $appModel->saveOrFail();

        return [
        ];
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @param int $appId
     * @return array
     */
    public static function putAppIdChangeKeys(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId,
        int $appId
    ): array
    {
        $openSsl = (new OpenSsl())->newPrivatePublicKeys();

        $appModel = AppModel::findModelByAttributesOrFail($pdoExtKernel, [
            'id' => $appId,
            'userId' => $userId
        ]);
        $appModel->publicKey = $openSsl->getPublicKey();
        $appModel->keyHash = md5($appModel->publicKey);
        $appModel->saveOrFail();

        return [
            'keyHash' => $appModel->keyHash,
            'privateKey' => $openSsl->getPrivateKey()
        ];
    }
}