<?php

declare(strict_types=1);

namespace App\Anonfiles;

use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use Laminas\Text\Figlet\Figlet;
use LaravelZero\Framework\Commands\Command;
use Storage;

class Anonfiles extends Command
{
    public $disk;

    public $newFilename = null;
    
    public $response;

    public function __construct()
    {
        $this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    public function logo($name = 'Anonfiles CLI', $type = null, $font = null): void
    {
        $name = is_null($name) ? config('logo.name') : $name;
        $font = is_null($font) ? config('logo.font') : $font;
        $figlet = new Figlet();
        $logo = $figlet->setFont($font)->render($name);
        switch ($type) {
            case 'info':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     $this->info($logo);

                break;
case 'error':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     $this->error($logo);

    break;
case 'comment':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     $this->comment($logo);

    break;
case 'question':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     $this->question($logo);

    break;
default:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     $this->line($logo);

    break;

        }
    }

    public function clear(): mixed
    {
        return system('clear');
    }

    public function setFile($file): void
    {
        $this->file = $file;
        $this->path = $this->disk->path($file);
        $this->getMetaData();
    }

    public function setFileExtenstion(): void
    {
        if (! is_null($this->newFilename)) {
            $this->newFilename .= '.' . pathinfo($this->path, PATHINFO_EXTENSION);
        }
    }

    public function createDisk()
    {
        try {
            $this->disk = Storage::build(['driver' => 'local', 'root' => getcwd()]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getFilename()
    {
        if (! is_null($this->newFilename)) {
            return $this->newFilename;
        }
        return basename($this->path);
    }

    public function validate(): void
    {
    }

    public function getSize()
    {
        return $this->diffForHumans($this->fileSize);
    }

    public function getLastModified()
    {
        return date('m/d/Y H:i:s', $this->fileLastModified);
    }

    public function checkIfFileExists($pathToFile)
    {
        if ($this->disk->exists($pathToFile)) {
            return true;
        }

        return false;
    }

    public function diffForHumans($bytes, $dec = 2)
    {
        $size = config('anonfiles.STORAGE_UNITS');

        $factor = floor((strlen(strval($bytes)) - 1) / 3);
        return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    public function upload($filename = null): void
    {
        $this->newFilename = $filename;

        $this->setFileExtenstion();

        $this->client = new Client(['http_error' => false, 'progress' => function (
            $downloadTotal,
            $downloadedBytes,
            $uploadTotal,
            $uploadedBytes
        ): void {
            $res = curl_getinfo($downloadTotal);
            echo "\033[5D";
            $msg = $this->diffForHumans($res['size_upload']) . ' / ' . $this->diffForHumans($res['upload_content_length']);
            echo "     📂  Progress : {$msg} \r";
        },
        ]);
        
        try {
        $resource = $this->disk->get($this->file);

        $stream = Psr7\stream_for($resource);

        $request = new Request(
            'POST',
            config('anonfiles.UPLOAD_ENDPOINT'),
            [],
            new Psr7\MultipartStream(
                [
                    [
                        'name' => 'file',
                        'contents' => $stream,
                        'filename' => $this->getFilename(),
                    ],
                ]
            )
        );

	        $this->response = $this->client->send($request);
        } catch(\GuzzleHttp\Exception\RequestException $e) {
        	
        }
    }

    public function download($link = null, $pathToFile = null): mixed
    {
        $this->setFileExtenstion();

        try {
            $this->client = new Client(['http_error' => false, 'progress' => function (
                $downloadTotal,
                $downloadedBytes,
                $uploadTotal,
                $uploadedBytes
            ): void {
                echo "\033[5D";
                $msg = $this->diffForHumans($uploadTotal) . ' / ' . $this->diffForHumans($downloadedBytes);
                echo "      📥  Progress : {$msg} \r";
            },
            ]);
            $resource = fopen($pathToFile, 'w');

            $stream = Psr7\stream_for($resource);

            $this->response = $this->client->request('GET', $link, ['save_to' => $stream]);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function getResponse()
    {
    	return  isset($this->response) ? json_decode($this->response->getBody()->getContents()) : null;
    }

    public function getDownloadLink($link = null)
    {
        $dom = new DOMDocument();
        $dom->loadHTML(file_get_contents($link));
        return $dom->getElementById('download-url')->getAttribute('href');
    }

    private function getMetadata(): void
    {
        $this->fileLastModified = $this->disk->lastModified($this->file);
        $this->fileSize = $this->disk->size($this->file);
    }
}
