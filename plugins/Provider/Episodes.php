<?php
namespace PhroznPlugin\Provider;

class Episodes
    extends \Phrozn\Provider\Base
    implements \Phrozn\Provider 
{
    const INPUT_FEED = 'http://zf.rpod.ru/rss_r6e_fe25.xml';

    /**
     * Current season and episode
     */
    private $season;
    private $episode;

    public function get()
    {
        $feed = new \SimpleXMLElement(file_get_contents(self::INPUT_FEED));
        $episodes = array();
        foreach ($feed->channel->item as $episode) {
            $desc = (string)$episode->description;
            $legend = $this->getSeasonEpisodeLegend($episode);
            if ($season = $this->getRequiredSeason()) {
                if ($season != $this->season) {
                    continue;
                }
            }
            $episodes[] = array(
                'id'        => 's' . $this->season . 'e' . $this->episode,
                'playerid'  => md5($episode->title),
                'legend'    =>  $legend, 
                'notes'     => $desc,
                'title'     => $episode->title, 
                'authors'   => 'Виктор Фараздаги', 
                'file'      => (string)$episode->enclosure['url'],
            );
        }
        return $episodes;
    }

    private function getRequiredSeason()
    {
        $config = $this->getConfig();
        if (isset($config['season'])) {
            return (int)$config['season'];
        }
        return null;
    }

    private function getSeasonEpisodeLegend($episode)
    {
        $url = (string)$episode->enclosure['url'];
        $legend = str_replace('zftalk.dev.s', '', basename($url));
        $legend = str_replace('.mp3', '', $legend);
        $legend = explode('e', $legend);
        // dirty hack but it works :))
        $this->season = (int)$legend[0];
        $this->episode = (int)$legend[1];
        $legend = 'Сезон ' . $legend[0] . ' / Выпуск ' . $legend[1] . ' / ';
        return $legend . $episode->pubDate;
    }
}
