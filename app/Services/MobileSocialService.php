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

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $socialAbbr
     * @param string $mobilePrefix
     * @param string $num
     * @return MobileSocialModel|null
     */
    public static function findMobileSocialAsSocialExternalId(
        PdoExtKernelInterface $pdoExtKernel,
        string $socialAbbr,
        string $mobilePrefix,
        string $num
    ): ?int
    {
        $sqlRow = <<<MYSQL
select 
    mc.socialExternalId
from 
    mobiles m, country_mobile_prefixes cmp, mobile_socials mc, socials s
where
    m.countryMobilePrefixId = cmp.id
    and mc.mobileId = m.id 
    and mc.socialId = s.id
    and s.abbr = ?
    and cmp.mobilePrefix = ?
    and m.num = ?    
limit 0,1
MYSQL;
        return $pdoExtKernel
            ->pdoExt()
            ->fetchOneColumn(strtr($sqlRow, [

            ]), 'socialExternalId', [
                $socialAbbr,
                $mobilePrefix,
                $num
            ]);
    }
}