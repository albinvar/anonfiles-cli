<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Storage;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Helpers\Anonfiles;

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
	    
		// initialize Anonfiles helper.
    	$this->anonfiles = new Anonfiles();
	    
    }
    
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    	// show logo.
	    $this->anonfiles->logo('Anonfiles', 'comment');

    	// create new disk instance.
	    $this->anonfiles->createDisk();
		
		
		$this->file = $this->argument('filename');
		
		// validate the file before uploading.
		$this->validate();
		
		//
	    $this->showFileMetaData();
        
        if($this->confirm('Do you want to upload file?')){
        	try {
	        	$this->anonfiles->upload();
			} catch(\GuzzleHttp\Exception\ConnectException $e) {
				$this->error($e->getMessage());
			} catch(\Exception $e) {
				$this->error($e->getMessage());
			}
        };
        
        
        $this->showResponse();
    }
    
    
    private function validate()
    {
    	if(!$this->anonfiles->checkIfFileExists($this->file))
	    {
			$this->error("File doesn't exist.");
			exit();
		}
		
		$this->anonfiles->setFile($this->file);
		
    }
    
    private function showFileMetaData()
    {
    	$headers = ['Properties', 'Values'];
    
    	$data = [
		    ['filename', $this->anonfiles->getFileName()],
	        ['path', $this->anonfiles->path],
	        ['size', $this->anonfiles->getSize()],
			['last modified', $this->anonfiles->getLastModified()],
	    ];
    
	    $this->table($headers, $data);
    }
    
    
    
    public function showResponse()
    {
    	$json = $this->anonfiles->getResponse();
	    
		if($json->status)
		{
		    $this->comment('   File uploaded ✅');
			$this->newline();
			$this->info('link : '. $json->data->file->url->full);
			$this->newline();
		} else {
			$this->error = "Uploading failed...";
		}
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
