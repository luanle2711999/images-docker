<?php
declare(strict_types=1);
namespace OCA\SNextCloud\Controller;

use OC\Files\Filesystem;
use OC\Files\View;
use OC_User;
use OCP\AppFramework\Controller;

class ThumbnailController extends Controller
{
    protected $image;
    protected $path;
    protected $user;
    protected $useOriginal = false;
    /**
     * @var \OC\Files\View $view
     */
    protected $view;
    public function __construct($imagePath = 'http://localhost:8080/apps/files/?dir=/Photos&openfile=34', $user = null, $square = false)
    {
        if (!Filesystem::isValidPath($imagePath)) {
            return;
        }
        if (is_null($user)) {
            $this->view = Filesystem::getView();
            $this->user = OC_User::getUser();
        } else {
            $this->view = new View('/' . $user . '/files');
            $this->user = $user;
        }
        $this->useOriginal = (substr($imagePath, -4) === '.svg' or substr($imagePath, -5) === '.svgz');
        if ($this->useOriginal) {
            $this->path = $imagePath;
        } else {
            $galleryDir = \OC_User::getHome($this->user) . '/gallery/' . $this->user . '/';
            if (strrpos($imagePath, '.')) {
                $extension = substr($imagePath, strrpos($imagePath, '.') + 1);
                $image = substr($imagePath, 0, strrpos($imagePath, '.'));
            } else {
                $extension = '';
                $image = $imagePath;
            }
            if ($square) {
                $extension = 'square.' . $extension;
            }
            $this->path = $galleryDir . $image . '.' . $extension;
            if (!file_exists($this->path)) {
                $this->create($imagePath, $square);
            }
        }
    }
    private function create($imagePath, $square)
    {
        $galleryDir = \OC_User::getHome($this->user) . '/gallery/' . $this->user . '/';
        $dir = dirname($imagePath);
        $fileInfo = $this->view->getFileInfo($imagePath);
        if (!$fileInfo) {
            return false;
        }
        if (!is_dir($galleryDir . $dir)) {
            mkdir($galleryDir . $dir, 0755, true);
        }
        $this->image = new \OCP\Image();
        // check if file is encrypted
        if ($fileInfo['encrypted'] === true) {
            $fileName = $this->view->toTmpFile($imagePath);
        } else {
            $fileName = $this->view->getLocalFile($imagePath);
        }
        $this->image->loadFromFile($fileName);
        if ($this->image->valid()) {
            $this->image->fixOrientation();
            if ($square) {
                $this->image->centerCrop(200);
            } else {
                $this->image->fitIn($this->image->width(), 200);
                if ($this->image->width() > 600) {
                    // max aspect ratio of 3
                    $this->image->crop(($this->image->width() - 600) / 2, 0, 600, 200);
                }
            }
            $this->image->save($this->path);
        }
    }

    /**
     * @NoCSRFRequired
     */
    public function get()
    {
        if (is_null($this->image)) {
            $this->image = new \OCP\Image($this->path);
        }
        return $this->image;
    }
    // public function show()
    // {
    //     if ($this->useOriginal) {
    //         $fp = @$this->view->fopen($this->path, 'rb');
    //         $mtime = $this->view->filemtime($this->path);
    //         $size = $this->view->filesize($this->path);
    //         $mime = $this->view->getMimetype($this->path);
    //     } else {
    //         $fp = @fopen($this->path, 'rb');
    //         $mtime = filemtime($this->path);
    //         $size = filesize($this->path);
    //         $mime = \OC_Helper::getMimetype($this->path);
    //     }
    //     if ($fp) {
    //         \OCP\Response::enableCaching();
    //         \OCP\Response::setLastModifiedHeader($mtime);
    //         header('Content-Length: ' . $size);
    //         header('Content-Type: ' . $mime);
    //         \OCP\Response::setContentDispositionHeader(basename($this->path), 'attachment');
    //         fpassthru($fp);
    //     } else {
    //         \OC_Response::setStatus(\OC_Response::STATUS_NOT_FOUND);
    //     }
    // }
    public static function removeHook($params)
    {
        $path = $params['path'];
        $user = \OC_User::getUser();
        $galleryDir = \OC_User::getHome($user) . '/gallery/';
        $thumbPath = $galleryDir . $path;
        if (is_dir($thumbPath)) {
            if (file_exists($thumbPath . '.png')) {
                unlink($thumbPath . '.png');
            }
        } else {
            if (file_exists($thumbPath)) {
                unlink($thumbPath);
            }
            if (strrpos($path, '.')) {
                $extension = substr($path, strrpos($path, '.') + 1);
                $image = substr($path, 0, strrpos($path, '.'));
            } else {
                $extension = '';
                $image = $path;
            }
            $squareThumbPath = $galleryDir . $image . '.square.' . $extension;
            if (file_exists($squareThumbPath)) {
                unlink($squareThumbPath);
            }
        }
        $parent = dirname($path);
        if ($parent !== DIRECTORY_SEPARATOR and $parent !== '' and $parent !== $path) {
            self::removeHook(array('path' => $parent));
        }
    }
    public static function writeHook($params)
    {
        self::removeHook($params);
    }
}