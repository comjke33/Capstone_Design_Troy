<?php
class ParsedownWithAnchor extends ParsedownExtra {
    function blockHeader($Line) {
        $block = parent::blockHeader($Line);
        if (isset($block['element']['text'])) {
            $text = $block['element']['text'];
            $id = $this->slugify(strip_tags($text));
            // 기존 속성이 있으면 병합
            if (!isset($block['element']['attributes'])) {
                $block['element']['attributes'] = [];
            }
            $block['element']['attributes']['id'] = $id;
        }
        return $block;
    }

    private function slugify($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9가-힣\s]/u', '', $text);
        $text = preg_replace('/\s+/', '-', $text);
        return $text;
    }
}