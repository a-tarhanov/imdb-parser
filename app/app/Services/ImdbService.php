<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use PHPHtmlParser\Dom;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ImdbService
{
    public $client;

    public function __construct()
    {
        $this->client = Http::withOptions([
            'base_uri' => config('services.imdb.base_uri')
        ]);
    }

    public function getFilm($id)
    {
        if (!$id) {
            throw new UnprocessableEntityHttpException('Film id is empty');
        }

        $response = $this->client->get("/title/{$id}");
        $status = $response->status();

        if ($status === Response::HTTP_NOT_FOUND) {
            throw new NotFoundHttpException('Film not found');
        }

        if ($status !== Response::HTTP_OK) {
            throw new HttpException('Error. Try later');
        }

        $dom = new Dom();
        $dom->load($response->body());

        return [
            'title' => $this->findTitle($dom),
            'image' => $this->findImage($dom),
            'release_date' => $this->findReleaseDate($dom) ?
                Carbon::createFromFormat('d M Y', $this->findReleaseDate($dom))->format('Y-m-d') :
                null,
            'rating' => $this->findRating($dom),
            'category' => $this->findCategory($dom),
            'director' => $this->findDirector($dom),
        ];
    }

    private function findTitle(Dom $dom)
    {
        $titleDom = $dom->find('.title_block .originalTitle');

        return $titleDom->count() ? $titleDom->text : null;
    }

    private function findImage(Dom $dom)
    {
        $imageDom = $dom->find('.poster a img');

        return $imageDom->count() ? data_get($imageDom->getAttributes(), 'src') : null;
    }

    private function findReleaseDate(Dom $dom)
    {
        $date = null;

        $dom->find('#titleDetails .txt-block')->each(function ($block) use (&$date) {
            $labelDom = $block->find('h4');

            if (!$labelDom->count()) {
                return;
            }

            if ($labelDom->text === 'Release Date:') {
                preg_match('/^(\d{1,2}\s\w+\s\d{4}).*/', trim($block->text), $match);
                $date = $match[1] ?? null;
            }
        });

        return $date;
    }

    private function findRating(Dom $dom)
    {
        $ratingDom = $dom->find('.title_block .ratingValue strong span');

        return $ratingDom->count() ? $ratingDom->text : null;
    }

    private function findCategory(Dom $dom)
    {
        $category = null;

        $dom->find('#titleStoryLine .see-more')->each(function ($block) use (&$category) {
            $labelDom = $block->find('h4');

            if (!$labelDom->count()) {
                return;
            }

            if ($labelDom->text === 'Genres:') {
                $block->find('a')->each(function($genre) use (&$category) {
                    $category[] = trim($genre->text);
                });
            }
        });

        return $category;
    }

    private function findDirector(Dom $dom)
    {
        $director = null;

        $dom->find('.plot_summary .credit_summary_item')->each(function ($block) use (&$director) {
            $labelDom = $block->find('h4');

            if (!$labelDom->count()) {
                return;
            }

            if ($labelDom->text === 'Director:') {
                $directorLink = $block->find('a');
                $director = $directorLink->count() ? $directorLink->text : $block->text;
            }
        });

        return $director;
    }
}
