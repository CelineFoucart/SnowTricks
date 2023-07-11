<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VideoExtension extends AbstractExtension
{
    private const TITLE = 'YouTube video player';
    private const ALLOW = 'accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';

    public function getFunctions()
    {
        return [
            new TwigFunction('video_iframe', [$this, 'getVideoIframe'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generate an youtube iframe.
     *
     * @return string the iframe generated
     */
    public function getVideoIframe(string $videoPath): string
    {
        $url = parse_url($videoPath);
        $videoId = null;

        if (isset($url['query'])) {
            $explode = explode('=', $url['query']);
            $videoId = $explode[1];
        } else {
            $explode = explode('/', $url['path']);
            $videoId = $explode[array_key_last($explode)];
        }

        $videoIframePath = "https://www.youtube.com/embed/{$videoId}";
        $title = self::TITLE;
        $allow = self::ALLOW;

        return "<iframe src=\"{$videoIframePath}\" title=\"{$title}\" frameborder=\"0\" allow=\"{$allow}\" allowfullscreen></iframe>";
    }
}
