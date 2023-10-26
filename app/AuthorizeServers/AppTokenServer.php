<?php

namespace App\AuthorizeServers;

use App\Http\Controllers\Api\BaseApiHttpController;
use App\Model\Database\AppModel;

class AppTokenServer extends \YusamHub\Project0001ClientAuthSdk\Servers\AppTokenServer
{
    protected ?BaseApiHttpController $baseApiHttpController;
    public function setBaseApiHttpController(BaseApiHttpController $baseApiHttpController): void
    {
        $this->baseApiHttpController = $baseApiHttpController;
    }

    protected function getAppId(int $appId, string $serviceKey): ?int
    {
        $appModel = AppModel::findModelByAttributes($this->baseApiHttpController?->getPdoExtKernel(), [
            'id' => $appId,
            'secretKey' => $serviceKey
        ]);
        if (!is_null($appModel)) {
            return $appModel->id;
        }
        return null;
    }

    protected function getAppPublicKey(int $appId): ?string
    {
        $appModel = AppModel::findModel($this->baseApiHttpController->getPdoExtKernel(), $appId);
        if (!is_null($appModel)) {
            return $appModel->publicKey;
        }
        return null;
    }
}