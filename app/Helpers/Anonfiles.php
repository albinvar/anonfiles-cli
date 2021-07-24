<?php

namespace App\Helpers;

use LaravelZero\Framework\Commands\Command;

class Anonfiles extends Command
{
	public function __construct()
	{
		//
	}
	
	public function setFile()
	{
		//
	}
	
	public function setDisk()
	{
		//
	}
	
	public function getDisk()
	{
		//
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