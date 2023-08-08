<?php

namespace App\Services;

use App\Model\Database\MobileSocialModel;
use App\Model\Database\SocialModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

class MobileSocialService
{
    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $socialAbbr
     * @param int $mobileId
     * @param int $socialExternalId
     * @return MobileSocialModel
     * @throws \Exception
     */
    public static function findOrCreateMobileSocial(
        PdoExtKernelInterface $pdoExtKernel,
        string $socialAbbr,
        int $mobileId,
        int $socialExternalId
    ): MobileSocialModel
    {
        $socialModel = SocialModel::findModelByAttributes($pdoExtKernel, [
            'abbr' => $socialAbbr
        ]);
        if (is_null($socialModel)) {
            throw new \Exception(sprintf("Abbr [%s] not found", $socialAbbr));
        }
        $mobileSocialModel = MobileSocialModel::findModelByAttributes($pdoExtKernel, [
            'mobileId' => $mobileId,
            'socialId' => $socialModel->id,
            'socialExternalId' => $socialExternalId
        ]);

        if (is_null($mobileSocialModel)) {
            $mobileSocialModel = new MobileSocialModel();
            $mobileSocialModel->setPdoExtKernel($pdoExtKernel);
            $mobileSocialModel->mobileId = $mobileId;
            $mobileSocialModel->socialId = $socialModel->id;
            $mobileSocialModel->socialExternalId = $socialExternalId;
            $mobileSocialModel->saveOrFail();
        }
        return $mobileSocialModel;
    }
}