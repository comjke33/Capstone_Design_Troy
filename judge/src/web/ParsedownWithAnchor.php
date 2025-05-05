<?php
class ParsedownWithAnchor extends ParsedownExtra {
    function blockHeader($Line) {
        $block = parent::blockHeader($Line);

        if (isset($block['element']['name']) && isset($block['element']['text'])) {
            $text = $block['element']['text'];

            // 'text'가 어레이인 경우 내부 'text'만 추출
            if (is_array($text) && isset($text['text'])) {
                $text = $text['text'];
            }

            $id = $this->slugify(strip_tags($text));

            if (!isset($block['element']['attributes'])) {
                $block['element']['attributes'] = [];
            }

            $block['element']['attributes']['id'] = $id;
        }

        return $block;
    }

    private function slugify($text) {
        $text = strip_tags($text);
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s-]+/u', '', $text);  // 한글 포함
        $text = preg_replace('/[\s]+/u', '-', $text);             // 공백 → 하이픈
        return $text;
    }
}