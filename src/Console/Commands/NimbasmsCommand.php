<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Console\Commands;

use Illuminate\Console\Command;

class NimbasmsCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'nimbasms:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package nimbasms.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Nimbasms placeholder command executed.');

        return self::SUCCESS;
    }
}
