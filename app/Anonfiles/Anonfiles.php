<?php

declare(strict_types=1);

namespace Anonfiles;

use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Storage;
use Laminas\Text\Figlet\Figlet;
use LaravelZero\Framework\Commands\Command;

class Anonfiles extends Command
{

    public $file;

    public $path;

    public $fileSize;

    public $fileLastModified;

    public $newFilename = null;

    public static $proxy = 'socks5h://127.0.0.1:9050';
    protected $disk;

    protected $client;

    protected $response;

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

    public function checkIfCanConnectToSocksProxy(): bool
    {
        try {
            $this->client->request('GET', 'http://checkip.amazonaws.com/', $this->getSettings(true));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getSettings($proxy = false)
    {
        return $proxy === true ? ['proxy' => static::$proxy]
                    : [];
    }

    public function upload($filename = null, $proxy = false): void
	{
	    $this->newFilename = $filename;

	    $this->client = new Client(['http_errors' => false, 'progress' => function (
	        $downloadTotal,
	        $downloadedBytes,
	        $uploadTotal,
	        $uploadedBytes
		): void {
		        echo "\033[5D";
		        $msg = $this->diffForHumans($uploadedBytes) . ' / ' . $this->diffForHumans($uploadTotal);
		        echo "     📂  Progress : {$msg} \r";
		    },
		]);

    if ($proxy === true && $this->checkIfCanConnectToSocksProxy() === false) {
        $this->error('Cannot connect to tor proxy, please start tor on your device.');
        exit(1);
    }

    try {
        $resource = $this->disk->readStream($this->file);

        $stream = \GuzzleHttp\Psr7\Utils::streamFor($resource);

        $request = new \GuzzleHttp\Psr7\Request(
            'POST',
            config('anonfiles.UPLOAD_ENDPOINT'),
            $this->getSettings($proxy),
            new \GuzzleHttp\Psr7\MultipartStream(
            [
                [
                    'name' => 'file',
                    'contents' => $stream,
                    'filename' => $this->getFilename(),
                ],
            ]
        )
        );

        $this->response = $this->client->send($request, $this->getSettings($proxy));
    } catch (\GuzzleHttp\Exception\RequestException $e) {
    }
}


public function download($link = null, $pathToFile = null, $proxy = false): mixed
{
    try {
        $this->client = new Client(['http_errors' => false, 'progress' => function (
            $downloadTotal,
            $downloadedBytes,
            $uploadTotal,
            $uploadedBytes
        ) use ($pathToFile): void {
            $fileSize = filesize($pathToFile);
            $msg = $this->diffForHumans($downloadedBytes) . ' / ' . $this->diffForHumans($downloadTotal);
            echo "      📥  Progress : {$msg} \r";
        },
        ]);

        if ($proxy === true && $this->checkIfCanConnectToSocksProxy() === false) {
            $this->error('Cannot connect to tor proxy, please start tor on your device.');
            exit(1);
        }

        $resource = fopen($pathToFile, 'w');

        $array = ['sink' => $resource];
        $array += $this->getSettings($proxy);

        $this->response = $this->client->request('GET', $link, $array);
        fclose($resource);
    } catch (\Exception $e) {
        return false;
    }
    return true;
}




    public function getResponse()
    {
        return isset($this->response) ? json_decode($this->response->getBody()->getContents()) : null;
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
