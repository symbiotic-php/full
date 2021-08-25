<?php

namespace Dissonance\Core\View;

use Dissonance\Core\Support\RenderableInterface;

class Section {

    /**
     * All of the captured sections.
     *
     * @var array
     */
    public $sections = array();

    /**
     * The last section on which injection was started.
     *
     * @var array
     */
    public $last = array();

    /**
     * Start injecting content into a section.
     *
     * <code>
     *		// Start injecting into the "header" section
     *		Section::start('header');
     *
     *		// Inject a raw string into the "header" section without buffering
     *		Section::start('header', '<title>Dissonance php</title>');
     * </code>
     *
     * @param  string          $section
     * @param  string|\Closure  $content
     * @return void
     */
    public function start($section, $content = null)
    {
        if ($content === null)  {
            ob_start() and $this->last[] = $section;
        } else {
            $this->extend($section, $content);
        }
    }

    /**
     * Inject inline content into a section.
     *
     * This is helpful for injecting simple strings such as page titles.
     *
     * <code>
     *		// Inject inline content into the "header" section
     *		Section::inject('header', '<title>Laravel</title>');
     * </code>
     *
     * @param  string  $section
     * @param  string  $content
     * @return void
     */
    public function inject($section, $content)
    {
        $this->start($section, $content);
    }

    /**
     * Stop injecting content into a section and return its contents.
     *
     * @return string
     */
    public function yield_section()
    {
        return $this->yield($this->stop());
    }

    /**
     * Stop injecting content into a section.
     *
     * @return string
     */
    public function stop()
    {
        $this->extend($last = array_pop($this->last), ob_get_clean());

        return $last;
    }

    /**
     * Extend the content in a given section.
     *
     * @param  string  $section
     * @param  string  $content
     * @return void
     */
    protected function extend($section, $content)
    {
        if (isset($this->sections[$section])) {
            $this->sections[$section] =
                ($content instanceof View) ?
                    function()use($content) {$content->render();}
                    :  str_replace('@parent', $content, $this->sections[$section]);
        } else {
            $this->sections[$section] = $content;
        }
    }

    /**
     * Append content to a given section.
     *
     * @param  string  $section
     * @param  string  $content
     * @return void
     */
    public function append($section, $content)
    {
        if (isset($this->sections[$section])) {
            $this->sections[$section] .= $content;
        } else {
            $this->sections[$section] = $content;
        }
    }

    /**
     * Get the string contents of a section.
     *
     * @param  string  $section
     * @return string
     */
    public function yield($section)
    {
        if(isset($this->sections[$section])) {
            $section = $this->sections[$section];
            if(is_callable($section)) {
                $section();
            } elseif ($section instanceof RenderableInterface) {
                $section->render();
            } else {
                echo $section;
            }
        }
    }

}