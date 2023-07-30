<?php

return [
    'publicSwaggerUiDir' => app_ext()->getPublicDir('/swagger-ui'),
    'publicSwaggerUiUri' => '/swagger-ui',
    'apiBaseUri' => '/api',
    'tokenKeyName' => 'X-Token',
    'signKeyName' => 'X-Sign',
    'debugTokens' => [
        'testing' => 0 //по токену находим ID
    ],
    'debugSigns' => [
        0 => 'testing', //по ID находим ключ подписи для ID
    ],
    'tokenHandle' => function(
        \YusamHub\AppExt\Traits\Interfaces\GetSetHttpControllerInterface $httpController,
        \Symfony\Component\HttpFoundation\Request $request
    )
    {
        if ($request->getRequestUri() === '/') {
            return null;
        }

        $debugTokens = (array) app_ext_config('api.debugTokens');

        if (empty($debugTokens)) {
            return null;
        }

        $tokenValue = (string) $request->headers->get(app_ext_config('api.tokenKeyName'));

        if (in_array($tokenValue, array_keys($debugTokens))) {
            return intval($debugTokens[$tokenValue]);
        }

        $apiUserModel = \App\Model\Database\ApiUserModel\ApiUserModel::findModelByAttributes($httpController->getPdoExtKernel(), ['apiToken' => $tokenValue]);
        if (!is_null($apiUserModel)) {
            return $apiUserModel;
        }

        throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
            'detail' => 'Invalid token value'
        ]);
    },
    'signHandle' => function(
        \YusamHub\AppExt\Traits\Interfaces\GetSetHttpControllerInterface $httpController,
        \Symfony\Component\HttpFoundation\Request $request,
        int $apiAuthorizedId,
        ?\App\Model\Database\ApiUserModel\ApiUserModel $apiUserModel
    )
    {
        $signValue = (string) $request->headers->get(app_ext_config('api.signKeyName'));

        if (is_null($apiUserModel)) {

            $debugSigns = (array) app_ext_config('api.debugSigns');

            if (isset($debugSigns[$apiAuthorizedId]) && $signValue !== $debugSigns[$apiAuthorizedId])
            {
                throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                    'detail' => 'Invalid debug sign value',
                ]);
            }

            return;
        }

        if ($signValue !== $apiUserModel->apiSign)
        {
            throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                'detail' => 'Invalid sign value',
            ]);
        }
    },
    //'infoTitle' => 'Api %s Server',
    //'infoVersion' => '1.0.0',
];