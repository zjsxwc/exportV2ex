<?php

//这里输入你的V站昵称
$nickName = "zjsxwc";




include_once "phpquery/phpQuery/phpQuery.php";

function dump($n) {
    if ("DOMElement" === get_class($n)) {
        /** @var DOMNamedNodeMap $attributes */
        $attributes = $n->attributes;

        $cssClass = $attributes->getNamedItem("class")->value;
        if ($cssClass === "cell") {
            $styleAttr = $attributes->getNamedItem("style");
            $style = null;
            if ($styleAttr) {
                $style = $styleAttr->value;
            }
            if ($style) {
                $content = trim($n->textContent);
                $pos = strpos($content,"\n");
                $dotPos = strpos($content,"...");
                $line = trim(substr($content, 0, $pos));
                $n = intval(trim(substr($line, $dotPos + 3)));
                if ($n) {
                    return ["pageNum", $n];
                }
            } else {
                $content = trim($n->textContent);
                if ($content) {
                    return [$cssClass, $content];
                }
            }
        }
        if (in_array($cssClass, ["dock_area", "inner"])) {
            $content = trim($n->textContent);
            return [$cssClass, $content];
        }
    }
    return [null, null];
}

$url = "https://www.v2ex.com/member/{$nickName}/replies?p=";

$p = 1;
$result = "";
$pageNum = 1;
while ($p <= $pageNum) {
    $html = file_get_contents($url . strval($p));

    $doc = phpQuery::newDocument($html);

    $h = null;
    foreach (pq(".header") as $e) {
        $h = $e;
        break;
    }

    while (1) {
        if (is_null($h)) {
            break;
        }
        list($class, $content) = dump($h);

        if ($class) {
            if ($class === "pageNum") {
                if ($pageNum === 1) {
                    $pageNum = $content;
                }
            } else {
                if ($class === "dock_area") {
                    $result .= "\n\n" . $content;
                } else {
                    $result .= "\n" . $content;
                }
            }
        }

        $h = $h->nextSibling;
    }

    $p++;
}

$date = date("Ymd", time());

file_put_contents(__DIR__ . "/{$date}v2ex{$nickName}.txt", $result);

