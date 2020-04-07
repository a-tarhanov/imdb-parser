<?php

namespace App\Jobs;

use App\Models\Film;
use App\Services\ImdbService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParseImdbJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function handle(ImdbService $imdbService)
    {
        $imdbFilm = $imdbService->getFilm($this->id);

        $film = Film::updateOrCreate([
            'imdb_id' => $this->id,
        ], $imdbFilm);

        if ($image = data_get($imdbFilm, 'image')) {
            $film->addMediaFromUrl($image)->toMediaCollection('image');
        }

        return $film;
    }
}
