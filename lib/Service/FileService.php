<?php
declare(strict_types=1);
namespace OCA\SNextCloud\Service;

use ErrorException;
use Imagick;
use OCP\Files\IRootFolder;
use OCP\Files\IAppData;
class FileService {

    private IRootFolder $storage;
    private IAppData $appData;
    public function __construct(IRootFolder $storage, IAppData $appData){
        $this->storage = $storage;
        $this->appData = $appData;
    }

    public function getFileContent($id) {

        $userFolder = $this->storage->getUserFolder('luanlc');

        // check if file exists and read from it if possible
        try {
            $file = $userFolder->getById($id);
            if ($file instanceof \OCP\Files\File) {
                return $file->getContent();
            } else {
                throw new ErrorException('Can not read from folder');
            }
        } catch(\OCP\Files\NotFoundException $e) {
            throw new ErrorException('File does not exist');
        }
    }
    public function readFileContent($userId){
        // $user = $this->storage->getDirectoryListing();
        $appData = $this->appData->getDirectoryListing();
        return $appData;
        // $image = new Imagick( 
        //     // 'https://media.geeksforgeeks.org/wp-content/uploads/20200123100652/geeksforgeeks12.jpg'
        //     // 'http://localhost:8080/remote.php/dav/files/luanlc/Photos/Toucan.jpg'
        //     // 'http://localhost:8080/core/preview?fileId=36&x=1920&y=1080&a=true'
        //     'https://www.shutterstock.com/image-photo/mountains-under-mist-morning-amazing-260nw-1725825019.jpg'
        // ); 
        //     // Add comment to the image  
        //     $image->commentImage("GeeksforGeeks"); 
              
        //     // Save the file to local image
        //     $image->writeImage('abc.jpg');
              
        //     // Open a the same file
        //     $fp = fopen('./abc.jpg', 'rb');
              
        //     // Read the exif headers
        //     $headers = exif_read_data($fp, 'COMMENT', true, true);
              
        //     // // Print the headers
        //     // echo 'EXIF Headers:' . '<br>';
              
        //     // print("<pre>".print_r($headers['COMMENT'], true)."</pre>");

        //     return $headers;

        // $url = 'https://media.geeksforgeeks.org/wp-content/uploads/20200123100652/geeksforgeeks12.jpg';
        // $image = file_get_contents($url);
        // $img = new Imagick();

        // $img -> readImageBlob($image);

        // return $img;
    }
}