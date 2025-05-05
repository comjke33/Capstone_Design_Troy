<?php
class ParsedownWithAnchor extends ParsedownExtra
{
    protected function blockHeader($Line)
    {
        $block = parent::blockHeader($Line);

        // 'text' 키가 없으면 id를 붙일 수 없음 → 조건 추가
        if (
            isset($block['element']) &&
            is_array($block['element']) &&
            isset($block['element']['text']) &&
            is_string($block['element']['text'])
        ) {
            $text = $block['element']['text'];
            $id = $this->slugify(strip_tags($text));

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
        // 한글/영문/숫자/공백만 허용
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        // 공백을 하이픈으로 치환
        $text = preg_replace('/\s+/', '-', $text);
        return strtolower($text);
    }
}