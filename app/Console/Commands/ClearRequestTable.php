<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Request\Request;
use Carbon\Carbon;



class ClearRequestTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Request Table Data Before 30 Days';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = Carbon::now()->subDays(30);

        $request = Request::where( 'created_at', '<', $date)->delete();

       $this->info('Records cleard ');

    }
}
