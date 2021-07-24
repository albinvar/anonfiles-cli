<?php

namespace App\Helpers;

use LaravelZero\Framework\Commands\Command;
use Storage;

class Anonfiles extends Command
{
	
	public $disk;
	
	public function __construct()
	{
		$this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
	}
	
	public function logo($name = "Anonfiles CLI", $type = null, $font = null): void
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
	
	public function setFile()
	{
		//
	}
	
	public function createDisk()
	{
		try {
			$this->disk = Storage::build(['driver' => 'local', 'root' => getcwd()]);
			return true;
		} catch(\Exception $e) {
			return false;
		}
	}
	
	public function getFilename()
	{
		//
	}
	
	public function validate()
	{
		//
	}
	
	public function getMetadata()
	{
		//
	}
	
	public function checkIfFileExists()
	{
		//
	}
	
	public function diffForHumans($bytes, $dec = 2)
    {
	    $factor = floor((strlen($bytes) - 1) / 3);
	    return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
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
    
    
    public function getResponse()
    {
    	//
    }
}