<?php
class ParsedownWithAnchor extends ParsedownExtra
{
    protected function blockHeader($Line)
    {
        $block = parent::blockHeader($Line);

        if (
            isset($block['element']) &&
            is_array($block['element'])
        ) {
            // h2 텍스트 가져오기
            $text = '';

            if (isset($block['element']['text']) && is_string($block['element']['text'])) {
                $text = $block['element']['text'];
            } elseif (
                isset($block['element']['handler']['argument']) &&
                is_string($block['element']['handler']['argument'])
            ) {
                $text = $block['element']['handler']['argument'];
            }

            if ($text !== '') {
                $id = $this->slugify(strip_tags($text));

                if (!isset($block['element']['attributes'])) {
                    $block['element']['attributes'] = [];
                }

                $block['element']['attributes']['id'] = $id;
            }
        }

        return $block;
    }

    private function slugify($text)
    {
        $text = trim($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text); // 한글, 숫자, 문자만
        $text = preg_replace('/\s+/', '-', $text);             // 공백 → 하이픈
        return strtolower($text);
    }
}