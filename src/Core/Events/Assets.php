<?php

namespace Symbiotic\Core\Events;


/**
 * Class Assets
 * @package Symbiotic\Apps\Events
 *
 *  Будьте внимательны: порядок добавления из плагинов не упорядочен, сначала скрипты приложения, потом плагинов, но между ними нет порядка
 *
 */
abstract class Assets /* extends CachedEvent*/
{

    const LINK_PRELOAD = 'preload';

    const LINK_PREFETCH = 'prefetch';

    const  LINK_PRECONNECT = 'preconnect';

    const  ASSET_DEFER = 'defer';

    const  ASSET_ASYNC = 'async';


    protected array $js = [];

    protected array $css = [];

    protected array $links = [];


    /**
     * @param string $content uri or content (with inline flag)
     * @param bool $inline
     * @param string $type - js template, vbscript...
     * @param string|null $flag see ASSET_... constants
     */
    public function addJs(string $content, bool $inline = false, $type = 'text/javascript', string $flag = null)
    {
        $this->js[] = ['content' => $content, 'inline' => $inline, 'type' => $type, 'flag' => $flag];
    }

    /**
     * @param string $content uri or content (with inline flag)
     * @param false $inline
     * @param false $preload
     */
    public function addCss(string $content, bool $inline = false, bool $preload = false)
    {
        $this->css[] = ['content' => $content, 'inline' => $inline];
        if ($preload) {
            $this->addLink($content, self::LINK_PRELOAD, 'style');
        }
    }

    /**
     * @param string $uri
     * @param string $type
     * @param string $as for preload see types https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types/preload#what_types_of_content_can_be_preloaded
     */
    public function addLink(string $uri, $type = self::LINK_PRELOAD, string $as = '')
    {
        $this->links[] = ['uri' => $uri, 'type' => $type, 'as' => $as];
    }

    public function getData()
    {
        return [
            'links' => $this->links,
            'css' => $this->css,
            'js' => $this->js,
        ];
    }

    public function getHtml()
    {
        $result = '';
        foreach ($this->links as $v) {
            $result .= '<link rel="' . $v['type'] . '" href="' . $v['uri'] . '" ' . (!empty($v['as']) ? 'as="' . $v['as'] . '"' : '') . ' />' . PHP_EOL;
        }

        foreach ($this->css as $v) {
            $result .= empty($v['inline']) ?
                '<link rel="stylesheet" href="' . $v['content'] . '" />' . PHP_EOL
                : '<style type="text/css">' . $v['content'] . '</style>';
        }

        foreach ($this->js as $v) {

            $content = $v['content'];
            $result .= empty($v['inline']) ?
                '<script src="' . $content . '" ' . (!empty($v['flag']) ? $v['flag'] : '') . ' type="' . $v['type'] . '" />' . PHP_EOL
                : '<script type="' . $v['type'] . '">' . $content . '</script>';
        }

        return $result;
    }

}