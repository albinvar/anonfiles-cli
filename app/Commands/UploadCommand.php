<?php

declare(strict_types=1);

namespace App\Commands;

use App\Helpers\Anonfiles;
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

        if ($this->confirm('Do you want to upload file?')) {
            try {
                $this->anonfiles->upload();
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                $this->error($e->getMessage());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }

        $this->showResponse();
    }

    public function showResponse(): void
    {
        $json = $this->anonfiles->getResponse();

        if ($json->status) {
            $this->comment('   File uploaded ✅');
            $this->newline();
            $this->info('link : '. $json->data->file->url->full);
            $this->newline();
        } else {
            $this->error = 'Uploading failed...';
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    private function validate(): void
    {
        if (! $this->anonfiles->checkIfFileExists($this->file)) {
            $this->error("File doesn't exist.");
            exit;
        }

        $this->anonfiles->setFile($this->file);
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
}
