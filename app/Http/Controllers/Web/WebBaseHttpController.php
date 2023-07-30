<?php

namespace App\Http\Controllers\Web;

use YusamHub\AppExt\SymfonyExt\Http\Controllers\BaseHttpController;

abstract class WebBaseHttpController extends BaseHttpController
{
    protected function getTemplateScheme(): string
    {
        return app_ext_config('smarty-ext.templateDefault');
    }

    /**
     * @throws \SmartyException
     */
    protected function view(string $template, array $params = []): string
    {
        $params = array_merge($params, [
            '_request' => [
                'path' => $this->getRequest()->getPathInfo(),
            ]
        ]);
        $smartyExt = app_ext_smarty_global()
            ->smartyExt($this->getTemplateScheme());

        $smartyExt
            ->getSmartyEngine()
            ->setLinkedValue('translate', $this->getTranslate());

        return $smartyExt
            ->view($template, $params);
    }
}