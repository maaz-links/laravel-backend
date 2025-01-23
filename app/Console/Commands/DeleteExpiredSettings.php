<?php

namespace App\Console\Commands;

use App\Models\FilesSettings;
use Illuminate\Console\Command;

class DeleteExpiredSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $Setting = FilesSettings::where('expiry_date', '<', time())->get();
        foreach ($Setting as $s) {
            $this->info('Deleting' . $s->id);
            $this->info(date('Y-m-d h:i:s', $s->expiry_date) . " < " . date('Y-m-d h:i:s'));
            $s->delete();
        }
    }
}
