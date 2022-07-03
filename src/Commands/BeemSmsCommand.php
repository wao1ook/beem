<?php

namespace Emanate\BeemSms\Commands;

use Illuminate\Console\Command;

class BeemSmsCommand extends Command
{
    public $signature = 'beem-sms';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
