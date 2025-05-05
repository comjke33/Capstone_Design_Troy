<?php
class ParsedownWithAnchor extends ParsedownExtra
{
    protected function blockHeader($Line)
{
    $block = parent::blockHeader($Line);

    if (isset($block['element']['name']) && preg_match('/^h[1-6]$/', $block['element']['name'])) {
        $text = $block['element']['text'] ?? '';
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
        error_log("slugify 대상: " . $text);  // 로그 확인
        $text = trim($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $text = preg_replace('/\s+/', '-', $text);
        return strtolower($text);
    }
}