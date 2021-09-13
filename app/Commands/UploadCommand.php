<?php

declare(strict_types=1);

namespace App\Commands;

use Anonfiles\Anonfiles;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class UploadCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'upload
							{filename}
							{--tor}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Upload files to anonfiles';

    protected $disk;
    
    protected $anonfiles;
    
    protected $file;

    protected $newFilename = null;

    public function __construct()
    {
        parent::__construct();

        // initialize Anonfiles helper.
        $this->anonfiles = new Anonfiles();
    }

    /**
     * Execute the console command.
     */
    public function handle(): mixed
    {
        // show logo.
        $this->anonfiles->logo('Anonfiles', 'comment');

        // create new disk instance.
        $this->anonfiles->createDisk();

        $this->file = $this->argument('filename');

        // validate the file before uploading.
        $this->validate();

        $this->showFileMetaData();

        if ($this->confirm('Do you want to rename file before uploading?')) {
            $this->setNewFileName();
        }

        if ($this->confirm('Do you want to upload file?', true)) {
            try {
                $this->anonfiles->upload($this->newFilename, $this->option('tor'));
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                $this->error($e->getMessage());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else {
            $this->error('aborting...');
            return 0;
        }

        return $this->showResponse();
    }
    
    private function setNewFileName(): void
    {
        $this->newFilename = $this->ask('Enter your new file name');
    }
    
    private function showFileMetaData(): void
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
    
    private function validate(): void
    {
        if (! $this->anonfiles->checkIfFileExists($this->file)) {
            $this->error("File doesn't exist.");
            exit(1);
        }

        $this->anonfiles->setFile($this->file);
    }

    public function showResponse(): void
    {
        $json = $this->anonfiles->getResponse();

        if (! is_null($json) && $json->status) {
            $this->comment('   File uploaded ✅');
            $this->newline();
            $this->info(' link : '. $json->data->file->url->full);
            $this->newline();
            exit(0);
        }
        if (! is_null($json) && ! $json->status) {
            $this->error("({$json->error->code}) {$json->error->message})");
            exit(1);
        }

        $this->error('Uploading failed due to a client-side error...');
        exit(1);
    }
}
