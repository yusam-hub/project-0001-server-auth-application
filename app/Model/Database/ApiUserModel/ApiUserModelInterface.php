<?php

namespace App\Model\Database\ApiUserModel;

use YusamHub\DbExt\Interfaces\GetSetPdoExtKernelInterface;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;
use YusamHub\DbExt\Interfaces\PdoExtModelInterface;

/**
 * @property int $id
 * @property string $apiToken
 * @property string $apiSign
 * @property string $description
 * @property string|null $blockedAt
 * @property string|null $blockedDescription
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static ApiUserModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static ApiUserModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static ApiUserModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static ApiUserModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
interface ApiUserModelInterface
    extends
    GetSetPdoExtKernelInterface,
    PdoExtModelInterface
{
    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_API_TOKEN = 'apiToken';
    const ATTRIBUTE_NAME_API_SIGN = 'apiSign';
    const ATTRIBUTE_NAME_DESCRIPTION = 'description';
    const ATTRIBUTE_NAME_BLOCKED_AT = 'blockedAt';
    const ATTRIBUTE_NAME_BLOCKED_DESCRIPTION = 'blockedDescription';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';
}
