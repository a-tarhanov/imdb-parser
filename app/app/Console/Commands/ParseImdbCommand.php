<?php

namespace App\Console\Commands;

use App\Jobs\ParseImdbJob;
use Illuminate\Console\Command;

class ParseImdbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:imdb {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grabbing information from IMDB';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->option('id');

        if (!$id) {
            return $this->error('Option `id` is empty!');
        }

        $this->info("Start parsing film #{$id}");

        try {
            ParseImdbJob::dispatchNow($id);
        } catch (\Exception $exception) {

        }

        $this->info("Success parsing film #{$id}");
    }
}
