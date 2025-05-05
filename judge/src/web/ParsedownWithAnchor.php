<?php
class ParsedownWithAnchor extends ParsedownExtra {
    function blockHeader($Line) {
        $block = parent::blockHeader($Line);
    
        if (isset($block['element']['text'])) {
            $text = $block['element']['text'];
            $id = $this->slugify(strip_tags($text));
            error_log("### Heading: " . $text . " => id: " . $id); // 로그 찍기
    
            if (!isset($block['element']['attributes'])) {
                $block['element']['attributes'] = [];
            }
    
            $block['element']['attributes']['id'] = $id;
        } else {
            error_log("### blockHeader: 'element[text]' 없음");
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