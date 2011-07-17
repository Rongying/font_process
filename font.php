<?php
define("FONT_FILE", "SimHei_new.ttf");
function nochaoscode($encode, $str) 
{ 
    $str = iconv($encode, "UTF-16BE", $str);    
    for ($i = 0; $i < strlen($str); $i++,$i++) 
    {   
        $output = ''; 
        $code = ord($str{$i}) * 256 + ord($str{$i + 1});    
        if ($code < 256) {    
            $output .= "uni00" . dechex($code);    
        } else if ($code != 65279) {    
            $output .= "uni".dechex($code);    
        }
        //$output = "uni" . dechex($code);    
    }    
    return $output;    
}

function getAllFiles($dir, & $files)
{
    $dir = new DirectoryIterator($dir);
    foreach ($dir as $fileinfo) {
        if(!$fileinfo->isDot()&&!$fileinfo->isFile())
        {
            getallfilelink($fileinfo->getPathname(), $files);
        }

        if(!$fileinfo->isDot()&&$fileinfo->isFile()){
            $file = $fileinfo->getFilename();
            $fileName = "";
            list($fileName, $sub) = explode(".", $file);
            $files[] = $fileName;
        }
    }
}

$content = file_get_contents("test.htm");
$remove = array (
    "'<(meta|base)[^>]*>'si",
    "'<frame[^>]*>.*?</frame[^>]*>'si",
    "'<iframe[^>]*>.*?</iframe[^>]*>'si",
    "'<script[^>]*>.*?</script[^>]*>'si",
    "'<object[^>]*>.*?</object[^>]*>'si",
    "'<embed[^>]*>.*?</embed[^>]*>'si",
    "'<applet[^>]*>.*?</applet[^>]*>'si",
    "'&nbsp;'",
    "'\s'si"
);
$replace = array (
    "/&#130;/","/&#131;/","/&#132;/","/&#133;/","/&#134;/","/&#135;/","/&#136;/","/&#137;/","/&#138;/",
    "/&#139;/","/&#140;/","/&#145;/","/&#146;/","/&#147;/","/&#148;/","/&#149;/","/&#150;/","/&#151;/",
    "/&#152;/","/&#153;/","/&#154;/","/&#155;/","/&#156;/","/&#159;/","/&#160;/","/&#161;/","/&#162;/",
    "/&#163;/","/&#164;/","/&#165;/","/&#166;/","/&#167;/","/&#168;/","/&#169;/","/&#170;/","/&#171;/",
    "/&#172;/","/&#173;/","/&#174;/","/&#175;/","/&#176;/","/&#177;/","/&#178;/","/&#179;/","/&#182;/",
    "/&#183;/","/&#184;/","/&#185;/","/&#186;/","/&#187;/","/&#188;/","/&#189;/","/&#190;/","/&#191;/"
);
$content = preg_replace($remove, '', $content);
$content = preg_replace($replace, '', $content);
$content = strip_tags($content);
$content = htmlspecialchars_decode($content);
preg_match_all('/./us', $content, $match);
$match[0] = array_unique($match[0]);
$unicode = array();
foreach ($match[0] as $char) {
    $unicode[] = nochaoscode("utf8", $char);
}
print_r($unicode);
exec("./generate_font.pe -font " . FONT_FILE . " " . implode(" ", $unicode));

//generate css files
$fontface  = "";
$class = "";
getAllFiles("chars/", $files);
foreach ($files as $file)
{
    $content = base64_encode(file_get_contents("chars/{$file}.ttf"));
    $fontface .= <<<FONT
@font-face {
    font-family: "{$file}";
    src: url("data:font/opentype;base64,{$content}");
}
FONT;

    $class .= <<<STYLE
.{$file} {
    font-family: {$file};
}
STYLE;
}

file_put_contents("font-face.css", $fontface);
file_put_contents("style-class.css", $class);
