<?php

/**
 * 快捷视图帮助
 * Class ViewHelper
 */

class ViewHelper
{

    /**
     * 返回视图内容(待加验证)
     * @param string $tpl 模板名称
     * @param array $vars 模板变量
     * @param string|null $node 授权节点
     */
    public static function fetchTemplate(string $tpl='',array $vars = [], ?string $node = null){
        throw new HttpRequestMethodException(view($tpl,$vars,200,function ($html) use($node){
            return preg_replace_callback('/<\/form>/i', function () use ($node) {
//                $token = Library::$sapp->request->buildToken('_token_');
//                return "<input type='hidden' name='_token_' value='{$token}'></form>";
            }, $html);
        }));
    }
//    public static function fetchTemplate(string $tpl = '', array $vars = [], ?string $node = null)
//    {
//        throw new HttpResponseException(view($tpl, $vars, 200, function ($html) use ($node) {
//            return preg_replace_callback('/<\/form>/i', function () use ($node) {
//                $token = Library::$sapp->request->buildToken('_token_');
//                return "<input type='hidden' name='_token_' value='{$token}'></form>";
//            }, $html);
//        }));
//    }

}