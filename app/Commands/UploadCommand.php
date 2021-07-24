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
    	// create new disk instance.
	    $this->anonfiles->createDisk();


    	$this->checkIfFileExists();
    
	    $this->showFileMetaData();
        
        if($this->confirm('Do you want to upload file?')){
        	$this->upload();
        };
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
			['last modified', date('m/d/Y H:i:s', $this->disk->lastModified($this->argument('filename')))],
	        ['size', $this->diffForHumans($this->disk->size($this->argument('filename')))]
	    ];
    
	    $this->table($headers, $data);
    }
    
    private function upload()
    {
    	$resource = $this->disk->get($this->argument('filename'));
	    
		$stream = Psr7\stream_for($resource);
		
		$request = new Request(
        'POST',
        $api,
        [],
        new Psr7\MultipartStream(
            [
                [
                    'name' => 'file',
                    'contents' => $stream,
                    'filename' => 'tesy',
                ],
            ]
        )
	);
	
	$response = $this->client->send($request);
	$this->showResponse($response);
	
    }
    
    
    public function diffForHumans($bytes, $dec = 2)
    {
	    $factor = floor((strlen($bytes) - 1) / 3);
	    return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
    
    public function showResponse($response)
    {
    	$json = json_decode($response->getBody());
	    
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
