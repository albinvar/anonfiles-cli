<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Storage;

class UploadCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'upload
							{filename}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Upload files to anonfiles';
    
    protected $disk;
    
    public function __construct()
    {
    	parent::__construct();
    	$this->disk = Storage::build(['driver' => 'local', 'root' => getcwd()]);
    }
    
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    	$this->checkIfFileExists();
    
	    $this->showFileMetaData();
        
        $this->info(getcwd());
    }
    
    
    private function validate()
    {
    	//
    }
    
    private function checkIfFileExists()
    {
    	if(!$this->disk->exists($this->argument('filename')))
	    {
			$this->error("File doesn't exists");
			exit();
		}
    }
    
    private function showFileMetaData()
    {
    	$headers = ['Properties', 'Values'];
    
    	$data = [
	        ['path', $this->argument('filename')],
			['last modified', $this->disk->lastModified($this->argument('filename'))],
	        ['size', $this->disk->size($this->argument('filename'))]
	    ];
    
	    $this->table($headers, $data);
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
