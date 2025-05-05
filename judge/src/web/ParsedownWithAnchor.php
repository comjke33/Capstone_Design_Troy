<?php
class ParsedownWithAnchor extends ParsedownExtra
{
    protected function blockHeader($Line)
    {
        $block = parent::blockHeader($Line);

        // 디버깅: 블록 구조 확인
        error_log("HEADER BLOCK: " . print_r($block, true));

        if (isset($block['element']['name']) && preg_match('/^h[1-6]$/', $block['element']['name'])) {
            $text = $this->flattenText($block['element']['text']);
            $id = $this->slugify(strip_tags($text));

            // 디버깅: id 확인
            error_log(">>> GENERATED ID: $id");

            if (!isset($block['element']['attributes'])) {
                $block['element']['attributes'] = [];
            }

            $block['element']['attributes']['id'] = $id;
        }

        return $block;
    }

    private function slugify($text)
    {
        $text = trim($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text); // 문자/숫자/공백만
        $text = preg_replace('/\s+/', '-', $text);             // 공백을 하이픈으로
        return strtolower($text);
    }

    private function flattenText($element)
    {
        // $element가 배열이면 재귀적으로 text 뽑기
        if (is_array($element)) {
            $result = '';
            foreach ($element as $sub) {
                $result .= $this->flattenText($sub);
            }
            return $result;
        } else {
            return (string)$element;
        }
    }
}