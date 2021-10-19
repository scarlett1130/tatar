<?php

class badWordsC
{
    public function check($text)
    {
        $badwords    = 'travian|tatar|war|com|net|org|info|.name|.biz|.me|.tv|.tel|.mobi|.asia|.uk|.eu|.us|.in|.tk|.cc|.ws|.bz|.mn|.co|.tw|.vn|.es|.pw|.club|.ca|.cn|.email|.photography|.photos|.tips|.solutions|.center|.gallery|.kitchen|.land|.technology|.today|.academy|.computer|.shoes|.careers|.domains|.coffee|.link|.guru|.estate|.company|.bike|.clothing|.holdings|.plumbing|.singles|.ventures|.camera|.equipment|.graphics|.lighting|.construction|.contractors|.directory|.diamonds|.enterprises|.voyage|.recipes|.gift|.site|.ly|.gq|.cf|.ga|.ml|.tk';
        $badwords   .= '|سيرفر|جولة|ملوك|ملؤك|حربالتتار|تمافتتاح|بدونمسافات|سجل|لحق|ترافيان|كتبفيقوقل|كتببقوقل';
        $badwords    = explode('|', $badwords);

        $goodwords   = 'twar.us|youtube.com';
        $goodwords  .= '|تيوار|جولةتيوار|تي وار|twar';
        $goodwords   = explode('|', $goodwords);


        $text        = str_replace($goodwords, '', $text);
        $text        = trim(preg_replace('/\s\s+/', '', $text));
        $text        = preg_replace('/\P{L}+/u', '', $text);

        foreach ($badwords as $word)
        {
            if (strpos($text, $word) !== false || strpos($text, strtoupper($word)) !== false)
            {
                return false;
            }
        }

        $text = preg_replace("/[a-zA-Z0-9]/", '', $text);
        $text = preg_replace(array('/أ/', '/ا/', '/ض/', '/ص/', '/ث/', '/ق/', '/ف/', '/غ/', '/ع/', '/ه/', '/خ/', '/ح/', '/ج/', '/د/', '/ش/', '/س/', '/ي/', '/ب/', '/ل/', '/ت/', '/ن/', '/م/', '/ك/', '/ط/', '/ئ/', '/ء/', '/ؤ/', '/ر/', '/ى/', '/ة/', '/و/', '/ز/', '/ظ/', '/ذ/', '/ـ/'), '', $text);



        if($text != '')
        {
            return false;
        }

        return true;
    }
}
?>