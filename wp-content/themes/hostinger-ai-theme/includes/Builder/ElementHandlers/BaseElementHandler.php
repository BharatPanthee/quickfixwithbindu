<?php

namespace Hostinger\AiTheme\Builder\ElementHandlers;

defined( 'ABSPATH' ) || exit;

use DOMElement;

class BaseElementHandler implements ElementHandler {
    protected string $builder_type;
    protected string $section_type = '';
    protected int $element_index = 0;

    public function __construct(string $builder_type) {
        $this->builder_type = $builder_type;
    }

    public function set_section_context(string $section_type, int $element_index = 0): void {
        $this->section_type = $section_type;
        $this->element_index = $element_index;
    }

    public function handle_gutenberg(DOMElement &$node, array $element_structure): void {

    }

    public function handle_elementor(array &$element, array $element_structure): void {

    }
}
