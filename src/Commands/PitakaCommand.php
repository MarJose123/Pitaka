<?php

namespace MarJose123\Pitaka\Commands;

use Illuminate\Console\Command;

class PitakaCommand extends Command
{
    public $signature = 'pitaka';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
