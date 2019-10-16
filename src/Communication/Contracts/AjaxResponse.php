<?php

namespace Softworx\RocXolid\Communication\Contracts;

/**
 * Represents AJAX response to be handled by the front-end.
 *
 * @author softworx <hello@softworx.digital>
 * @package Softworx\RocXolid
 * @version 1.0.0
 */
interface AjaxResponse
{
    /**
     * Instruct the response receiver to replace an HTML element content.
     *
     * @param string $selector Selector of the HTML element to modify.
     * @param string $content New content to replace HTML element content with.
     * @param bool $selector_is_id Flag whether the selector is an identifier of the HTML element.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function replace(string $selector, string $content, bool $selector_is_id = true): AjaxResponse;

    /**
     * Instruct the response receiver to insert (prepend) a content to an HTML element.
     *
     * @param string $selector Selector of the HTML element to modify.
     * @param string $content New content to insert to the HTML element.
     * @param bool $selector_is_id Flag whether the selector is an identifier of the HTML element.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function insert(string $selector, string $content, bool $selector_is_id = true): AjaxResponse;

    /**
     * Instruct the response receiver to insert (append) a content to an HTML element.
     *
     * @param string $selector Selector of the HTML element to modify.
     * @param string $content New content to append to the HTML element.
     * @param bool $selector_is_id Flag whether the selector is an identifier of the HTML element.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function append(string $selector, string $content, bool $selector_is_id = true): AjaxResponse;

    /**
     * Instruct the response receiver to change the value of an HTML element.
     *
     * @param string $selector Selector of the HTML element to modify.
     * @param string $value New value to set to the HTML element.
     * @param bool $selector_is_id Flag whether the selector is an identifier of the HTML element.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function val(string $selector, string $value, bool $selector_is_id = true): AjaxResponse;

    /**
     * Instruct the response receiver to destroy an HTML element (and his content).
     *
     * @param string $selector Selector of the HTML element to destroy.
     * @param bool $selector_is_id Flag whether the selector is an identifier of the HTML element.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function destroy(string $selector, bool $selector_is_id = true): AjaxResponse;

    /**
     * Instruct the response receiver to delete the content of an HTML element.
     *
     * @param string $selector Selector of the HTML element to empty.
     * @param bool $selector_is_id Flag whether the selector is an identifier of the HTML element.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function empty(string $selector, bool $selector_is_id = true): AjaxResponse;

    /**
     * Instruct the response receiver to populate and open a modal window.
     *
     * @param string $content Modal window content.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function modal(string $content): AjaxResponse;

    /**
     * Instruct the response receiver to open a (existing) modal window.
     *
     * @param string $selector Selector of the modal window HTML element to open.
     * @param bool $selector_is_id Flag whether the selector is an identifier of the HTML element.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function modalOpen(string $selector, bool $selector_is_id = true): AjaxResponse;

    /**
     * Instruct the response receiver to close a modal window.
     *
     * @param string $selector Selector of the modal window HTML element to close.
     * @param bool $selector_is_id Flag whether the selector is an identifier of the HTML element.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function modalClose(string $selector, bool $selector_is_id = true): AjaxResponse;

    /**
     * Instruct the response receiver to redirect to given URL.
     *
     * @param string $url Target URL to redirect to.
     * @return \Softworx\RocXolid\Communication\Contracts\AjaxResponse
     */
    public function redirect(string $url): AjaxResponse;

    /**
     * Return the response in an array containing control keys / selectors and values / element's content.
     *
     * @return array
     */
    public function get(): array;

    /**
     * @todo Form binding...
     */
    //public function bindForm($selector);
}