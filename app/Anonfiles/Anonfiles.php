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
    
    public static $proxy = 'socks5h://127.0.0.1:9050';
    
    const MIME_MAP = [
        'video/3gpp2'                                                               => '3g2',
        'video/3gp'                                                                 => '3gp',
        'video/3gpp'                                                                => '3gp',
        'application/x-compressed'                                                  => '7zip',
        'audio/x-acc'                                                               => 'aac',
        'audio/ac3'                                                                 => 'ac3',
        'application/postscript'                                                    => 'ai',
        'audio/x-aiff'                                                              => 'aif',
        'audio/aiff'                                                                => 'aif',
        'audio/x-au'                                                                => 'au',
        'video/x-msvideo'                                                           => 'avi',
        'video/msvideo'                                                             => 'avi',
        'video/avi'                                                                 => 'avi',
        'application/x-troff-msvideo'                                               => 'avi',
        'application/macbinary'                                                     => 'bin',
        'application/mac-binary'                                                    => 'bin',
        'application/x-binary'                                                      => 'bin',
        'application/x-macbinary'                                                   => 'bin',
        'image/bmp'                                                                 => 'bmp',
        'image/x-bmp'                                                               => 'bmp',
        'image/x-bitmap'                                                            => 'bmp',
        'image/x-xbitmap'                                                           => 'bmp',
        'image/x-win-bitmap'                                                        => 'bmp',
        'image/x-windows-bmp'                                                       => 'bmp',
        'image/ms-bmp'                                                              => 'bmp',
        'image/x-ms-bmp'                                                            => 'bmp',
        'application/bmp'                                                           => 'bmp',
        'application/x-bmp'                                                         => 'bmp',
        'application/x-win-bitmap'                                                  => 'bmp',
        'application/cdr'                                                           => 'cdr',
        'application/coreldraw'                                                     => 'cdr',
        'application/x-cdr'                                                         => 'cdr',
        'application/x-coreldraw'                                                   => 'cdr',
        'image/cdr'                                                                 => 'cdr',
        'image/x-cdr'                                                               => 'cdr',
        'zz-application/zz-winassoc-cdr'                                            => 'cdr',
        'application/mac-compactpro'                                                => 'cpt',
        'application/pkix-crl'                                                      => 'crl',
        'application/pkcs-crl'                                                      => 'crl',
        'application/x-x509-ca-cert'                                                => 'crt',
        'application/pkix-cert'                                                     => 'crt',
        'text/css'                                                                  => 'css',
        'text/x-comma-separated-values'                                             => 'csv',
        'text/comma-separated-values'                                               => 'csv',
        'application/vnd.msexcel'                                                   => 'csv',
        'application/x-director'                                                    => 'dcr',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
        'application/x-dvi'                                                         => 'dvi',
        'message/rfc822'                                                            => 'eml',
        'application/x-msdownload'                                                  => 'exe',
        'video/x-f4v'                                                               => 'f4v',
        'audio/x-flac'                                                              => 'flac',
        'video/x-flv'                                                               => 'flv',
        'image/gif'                                                                 => 'gif',
        'application/gpg-keys'                                                      => 'gpg',
        'application/x-gtar'                                                        => 'gtar',
        'application/x-gzip'                                                        => 'gzip',
        'application/mac-binhex40'                                                  => 'hqx',
        'application/mac-binhex'                                                    => 'hqx',
        'application/x-binhex40'                                                    => 'hqx',
        'application/x-mac-binhex40'                                                => 'hqx',
        'text/html'                                                                 => 'html',
        'image/x-icon'                                                              => 'ico',
        'image/x-ico'                                                               => 'ico',
        'image/vnd.microsoft.icon'                                                  => 'ico',
        'text/calendar'                                                             => 'ics',
        'application/java-archive'                                                  => 'jar',
        'application/x-java-application'                                            => 'jar',
        'application/x-jar'                                                         => 'jar',
        'image/jp2'                                                                 => 'jp2',
        'video/mj2'                                                                 => 'jp2',
        'image/jpx'                                                                 => 'jp2',
        'image/jpm'                                                                 => 'jp2',
        'image/jpeg'                                                                => 'jpeg',
        'image/pjpeg'                                                               => 'jpeg',
        'application/x-javascript'                                                  => 'js',
        'application/json'                                                          => 'json',
        'text/json'                                                                 => 'json',
        'application/vnd.google-earth.kml+xml'                                      => 'kml',
        'application/vnd.google-earth.kmz'                                          => 'kmz',
        'text/x-log'                                                                => 'log',
        'audio/x-m4a'                                                               => 'm4a',
        'audio/mp4'                                                                 => 'm4a',
        'application/vnd.mpegurl'                                                   => 'm4u',
        'audio/midi'                                                                => 'mid',
        'application/vnd.mif'                                                       => 'mif',
        'video/quicktime'                                                           => 'mov',
        'video/x-sgi-movie'                                                         => 'movie',
        'audio/mpeg'                                                                => 'mp3',
        'audio/mpg'                                                                 => 'mp3',
        'audio/mpeg3'                                                               => 'mp3',
        'audio/mp3'                                                                 => 'mp3',
        'video/mp4'                                                                 => 'mp4',
        'video/mpeg'                                                                => 'mpeg',
        'application/oda'                                                           => 'oda',
        'audio/ogg'                                                                 => 'ogg',
        'video/ogg'                                                                 => 'ogg',
        'application/ogg'                                                           => 'ogg',
        'font/otf'                                                                  => 'otf',
        'application/x-pkcs10'                                                      => 'p10',
        'application/pkcs10'                                                        => 'p10',
        'application/x-pkcs12'                                                      => 'p12',
        'application/x-pkcs7-signature'                                             => 'p7a',
        'application/pkcs7-mime'                                                    => 'p7c',
        'application/x-pkcs7-mime'                                                  => 'p7c',
        'application/x-pkcs7-certreqresp'                                           => 'p7r',
        'application/pkcs7-signature'                                               => 'p7s',
        'application/pdf'                                                           => 'pdf',
        'application/octet-stream'                                                  => 'pdf',
        'application/x-x509-user-cert'                                              => 'pem',
        'application/x-pem-file'                                                    => 'pem',
        'application/pgp'                                                           => 'pgp',
        'application/x-httpd-php'                                                   => 'php',
        'application/php'                                                           => 'php',
        'application/x-php'                                                         => 'php',
        'text/php'                                                                  => 'php',
        'text/x-php'                                                                => 'php',
        'application/x-httpd-php-source'                                            => 'php',
        'image/png'                                                                 => 'png',
        'image/x-png'                                                               => 'png',
        'application/powerpoint'                                                    => 'ppt',
        'application/vnd.ms-powerpoint'                                             => 'ppt',
        'application/vnd.ms-office'                                                 => 'ppt',
        'application/msword'                                                        => 'doc',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/x-photoshop'                                                   => 'psd',
        'image/vnd.adobe.photoshop'                                                 => 'psd',
        'audio/x-realaudio'                                                         => 'ra',
        'audio/x-pn-realaudio'                                                      => 'ram',
        'application/x-rar'                                                         => 'rar',
        'application/rar'                                                           => 'rar',
        'application/x-rar-compressed'                                              => 'rar',
        'audio/x-pn-realaudio-plugin'                                               => 'rpm',
        'application/x-pkcs7'                                                       => 'rsa',
        'text/rtf'                                                                  => 'rtf',
        'text/richtext'                                                             => 'rtx',
        'video/vnd.rn-realvideo'                                                    => 'rv',
        'application/x-stuffit'                                                     => 'sit',
        'application/smil'                                                          => 'smil',
        'text/srt'                                                                  => 'srt',
        'image/svg+xml'                                                             => 'svg',
        'application/x-shockwave-flash'                                             => 'swf',
        'application/x-tar'                                                         => 'tar',
        'application/x-gzip-compressed'                                             => 'tgz',
        'image/tiff'                                                                => 'tiff',
        'font/ttf'                                                                  => 'ttf',
        'text/plain'                                                                => 'txt',
        'text/x-vcard'                                                              => 'vcf',
        'application/videolan'                                                      => 'vlc',
        'text/vtt'                                                                  => 'vtt',
        'audio/x-wav'                                                               => 'wav',
        'audio/wave'                                                                => 'wav',
        'audio/wav'                                                                 => 'wav',
        'application/wbxml'                                                         => 'wbxml',
        'video/webm'                                                                => 'webm',
        'image/webp'                                                                => 'webp',
        'audio/x-ms-wma'                                                            => 'wma',
        'application/wmlc'                                                          => 'wmlc',
        'video/x-ms-wmv'                                                            => 'wmv',
        'video/x-ms-asf'                                                            => 'wmv',
        'font/woff'                                                                 => 'woff',
        'font/woff2'                                                                => 'woff2',
        'application/xhtml+xml'                                                     => 'xhtml',
        'application/excel'                                                         => 'xl',
        'application/msexcel'                                                       => 'xls',
        'application/x-msexcel'                                                     => 'xls',
        'application/x-ms-excel'                                                    => 'xls',
        'application/x-excel'                                                       => 'xls',
        'application/x-dos_ms_excel'                                                => 'xls',
        'application/xls'                                                           => 'xls',
        'application/x-xls'                                                         => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
        'application/vnd.ms-excel'                                                  => 'xlsx',
        'application/xml'                                                           => 'xml',
        'text/xml'                                                                  => 'xml',
        'text/xsl'                                                                  => 'xsl',
        'application/xspf+xml'                                                      => 'xspf',
        'application/x-compress'                                                    => 'z',
        'application/x-zip'                                                         => 'zip',
        'application/zip'                                                           => 'zip',
        'application/x-zip-compressed'                                              => 'zip',
        'application/s-compressed'                                                  => 'zip',
        'multipart/x-zip'                                                           => 'zip',
        'text/x-scriptzsh'                                                          => 'zsh',
    ];

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
    
    public function checkIfCanConnectToSocksProxy(): bool
    {
    	try {
	        $this->client->request('GET', 'http://checkip.amazonaws.com/', $this->getSettings(true));
			return true;
        } catch(\Exception $e) {
        	return false;
        }
    }
    
    public function getSettings($proxy = false)
    {
    	return ($proxy === true) ? ['proxy' => static::$proxy]
					: [] ;
    }

    public function upload($filename = null, $proxy = false): void
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
				
        if($proxy === true && $this->checkIfCanConnectToSocksProxy($this->getSettings()) === false)
		{
				$this->error('Cannot connect to tor proxy, please start tor on your device.');
				exit();
		}
		
        
        try {
        $resource = $this->disk->get($this->file);

        $stream = Psr7\stream_for($resource);
		
        $request = new Request(
            'POST',
            config('anonfiles.UPLOAD_ENDPOINT'),
            $this->getSettings(),
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

	        $this->response = $this->client->send($request, $this->getSettings());
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
