<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use DOMDocument;
use App\Helpers\Anonfiles;
use Http;

class DownloadCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'download
							{link}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';
    
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

		$this->link = $this->argument('link');
		
		$this->info("  Selected URL : {$this->link}");
		
		$this->newLine();
		
		if(!$this->validate())
		{
			$this->showMetaData();
		}
    }
    
    private function validate()
    {
    	$this->status = [];
    
    	$this->task('checking if url is valid', function () {
			$url = filter_var($this->link, FILTER_SANITIZE_URL);
			
			if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
				$this->status[] = true;
				return true;
			} 
			$this->status[] = false;
			return false;
		});
		
		//end process
		if(in_array(false, $this->status)) { return 1; }
		
		$this->task('checking if url belongs to anonfiles.com', function () {
			
			if (strpos($this->link, 'anonfiles.com') !== false) {
				$this->status[] = true;
				return true;
			}
			$this->status[] = false;
			return false;
		});
		
		
		//end process
		return in_array(false, $this->status) ? 1 : 0;
	}
	
	
	public function showMetaData()
	{
		$data = $this->getMetaData();
		
		$headers = ['Properties', 'Values'];

        $data = [
            ['filename', $this->anonfiles->getFileName()],
            ['path', $this->anonfiles->path],
            ['size', $this->anonfiles->getSize()],
            ['last modified', $this->anonfiles->getLastModified()],
        ];

        $this->table($headers, $data);
	}
	
	public function getMetaData()
	{
		$response = Http::acceptJson()->get();
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
