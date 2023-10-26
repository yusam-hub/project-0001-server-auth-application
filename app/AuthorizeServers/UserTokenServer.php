<?php

namespace App\AuthorizeServers;

use App\Http\Controllers\Api\BaseApiHttpController;
use App\Model\Database\UserModel;

class UserTokenServer extends \YusamHub\Project0001ClientAuthSdk\Servers\UserTokenServer
{
    protected ?BaseApiHttpController $baseApiHttpController;
    public function setBaseApiHttpController(BaseApiHttpController $baseApiHttpController): void
    {
        $this->baseApiHttpController = $baseApiHttpController;
    }

    protected function getUserId(int $userId, string $serviceKey): ?int
    {
        $userModel = UserModel::findModelByAttributes($this->baseApiHttpController->getPdoExtKernel(), [
            'id' => $userId,
            'secretKey' => $serviceKey
        ]);
        if (!is_null($userModel)) {
            return $userModel->id;
        }
        return null;
    }

    protected function getUserPublicKey(int $userId): ?string
    {
        $userModel = UserModel::findModel($this->baseApiHttpController->getPdoExtKernel(), $userId);
        if (!is_null($userModel)) {
            return $userModel->publicKey;
        }
        return null;
    }
}