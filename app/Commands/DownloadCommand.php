<?php

declare(strict_types=1);

namespace App\Commands;

use Anonfiles\Anonfiles;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

class DownloadCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'download
							{link}
							{--tor}
							{-p|--path=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';
    
    
    public $anonfiles;
    
    public $link;
    
    public $downloadPath;
    
    public $error;
    
    public $status;
    
    public $parsed;
    
    public $parsedDownloadLink;
    
    public $fileData;
    
    public $downloadLink;
    
    public $downloadFilename;

    public function __construct()
    {
        parent::__construct();

        // initialize Anonfiles helper.
        $this->anonfiles = new Anonfiles();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // show logo.
        $this->anonfiles->logo('Anonfiles', 'comment');

        // create new disk instance.
        $this->anonfiles->createDisk();

        $this->link = $this->argument('link');

        $this->downloadPath = is_null($this->option('path')) ? getcwd() : $this->option('path');

        $this->info("  Selected URL : {$this->link}");

        $this->newLine();

        if (! $this->validate()) {
            $this->showMetaData();
        }
    }

    public function getMetaData()
    {
        $code = $this->getUniqueCode();
        $response = Http::acceptJson()->get("https://api.anonfiles.com/v2/file/{$code}/info");
        return $response->object();
    }

    public function showMetaData()
    {
        $this->fileData = $this->parseUrl()->getMetaData();

        if ($this->fileData->status === false) {
            $this->newLine();
            $this->error('File Not Found..!!!');
            return 1;
        }

        $headers = ['Properties', 'Values'];

        $data = [
            ['filename', $this->fileData->data->file->metadata->name],
            ['url', $this->fileData->data->file->url->full],
            ['size', $this->fileData->data->file->metadata->size->readable],
        ];

        $this->table($headers, $data);

        if ($this->confirm('Are you sure you want to Download this file?', true)) {
            $this->downloadLink = $this->anonfiles->getDownloadLink($this->link);

            $this->parseDownloadLink();

            $status = $this->anonfiles->download($this->downloadLink, $this->downloadPath .'/'. $this->downloadFilename, $this->option('tor'));

            if ($status) {
                $this->newline();
                $this->newline();
                $this->comment(' File downloaded ✅');
                $this->newline();
                return 0;
            }
            $this->error = ' Downloading failed...';
            return 1;
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    private function validate()
    {
        $this->status = [];

        $this->task('checking if url is valid', function () {
            $url = filter_var($this->link, FILTER_SANITIZE_URL);

            if (filter_var($url, FILTER_VALIDATE_URL) !== false && strpos($this->link, 'anonfiles.com') !== false) {
                $this->status[] = true;
                return true;
            }
            $this->status[] = false;
            return false;
        });

        //end process
        return in_array(false, $this->status) ? 1 : 0;
    }

    private function parseUrl()
    {
        $this->parsed = parse_url($this->link);
        $this->parsed['params'] = explode('/', $this->parsed['path']);
        return $this;
    }

    private function parseDownloadLink()
    {
        $this->parsedDownloadLink = parse_url($this->downloadLink);
        $this->parsedDownloadLink['params'] = explode('/', $this->parsedDownloadLink['path']);
        $keyOfLastElement = key(array_slice($this->parsedDownloadLink['params'], -1, 1, true));
        $this->downloadFilename = $this->parsedDownloadLink['params'][$keyOfLastElement];
        return $this;
    }

    private function getUniqueCode()
    {
        return $this->parsed['params'][1] ?? null;
    }
}
