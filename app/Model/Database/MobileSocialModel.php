<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $mobileId
 * @property int $socialId
 * @property int $socialExternalId
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static MobileSocialModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileSocialModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileSocialModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static MobileSocialModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class MobileSocialModel extends BasePdoExtModel
{
    const CURRENT_CONNECTION_NAME =  DB_CONNECTION_DEFAULT;
    const CURRENT_TABLE_NAME = TABLE_MOBILE_SOCIALS;

    protected ?string $connectionName = self::CURRENT_CONNECTION_NAME;
    protected string $tableName = self::CURRENT_TABLE_NAME;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_MOBILE_ID = 'mobileId';
    const ATTRIBUTE_NAME_SOCIAL_ID = 'socialId';
    const ATTRIBUTE_NAME_SOCIAL_EXTERNAL_ID = 'socialExternalId';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
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
    mobiles m, country_mobile_prefixes cmp, :current_table_name mc, socials s
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
                ':current_table_name' => self::CURRENT_TABLE_NAME,
            ]), 'socialExternalId', [
                $socialAbbr,
                $mobilePrefix,
                $num
            ]);
    }

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
