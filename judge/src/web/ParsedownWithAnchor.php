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
        $text = preg_replace('/[^\p{L}\p{N}\s-]+/u', '', $text); // 한글, 영문, 숫자, 공백, 하이픈만
        $text = preg_replace('/[\s]+/', '-', $text);             // 공백을 하이픈으로
        return $text;
    }
}