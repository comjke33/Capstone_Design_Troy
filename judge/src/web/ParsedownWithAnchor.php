<?php
class ParsedownWithAnchor extends ParsedownExtra
{
    protected function blockHeader($Line)
    {
        $block = parent::blockHeader($Line);
        if (isset($block['element']) && isset($block['element']['text'])) {
            $text = $block['element']['text'];
            $id = $this->slugify(strip_tags($text));
            $block['element']['attributes']['id'] = $id;
        }
        return $block;
    }

    private function slugify($text)
    {
        // 한글 slug 허용 가능 — id는 그대로 생성되나 tocbot이 무시할 수도 있음
        $text = trim($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text); // 문자/숫자/공백만
        $text = preg_replace('/\s+/', '-', $text);             // 공백을 하이픈으로
        return strtolower($text);
    }
}