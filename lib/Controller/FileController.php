<?php
declare(strict_types=1);

namespace OCA\ThirdApp\Controller;

use Exception;
use OC\Files\Filesystem;
use OC\Files\View;
use OC_Util;
use OCA\SNextCloud\Service\FileService;
use OCA\ThirdApp\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\IUserSession;



class FileController extends Controller {
	private FileService $service;
	private IUserSession $userSession;
	private IRootFolder $rootFolder;
	private ?string $userId;
	private $storage;
	use Errors;

	public function __construct(IUserSession $userSession, IRootFolder $rootFolder, IRequest $request, IRootFolder $storage, 
								FileService $service,
								?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
		$this->userSession = $userSession;
		$this->rootFolder = $rootFolder;
		$this->storage = $storage;
	}

    /**
     * @NoCSRFRequired
     */
	public function getFileContent(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->getFileContent($id);
		});
	}
    
    /**
     * @NoCSRFRequired
     */

    public function readFileContent(): DataResponse {
		return $this->handleNotFound(function ()  {
			return $this->service->readFileContent($this->userId);
		});
	}


	/**
     * @NoCSRFRequired
     */
	public function getCurrentUserFolder(string $path, string $content) {
        $user = $this->userSession->getUser();

        if ($user === null) {
            return null;
        }

        // the "user folder" corresponds to the root of the user visible files
        return $this->rootFolder->getUserFolder($user->getUID());
    }

	/**
     * @NoCSRFRequired
     */
	public function readFile(){
		OC_Util::setupFS();
		$view = \OC\Files\Filesystem::getView();

		$path = $view->getPath(34);

		echo $path;
		echo $view;
		echo "dfjhsdfksdhk fsdjdfsdf";
		$file_handle = $view->fopen($path, "r");
		return $file_handle;
	}

	/**
     * @NoCSRFRequired
     */
	public function getTestFile()
    {
// 		OC_Util::setupFS();
// 		$view = \OC\Files\Filesystem::getView();

// 		$path = $view->getPath(29);

// 		// echo $path;
// 		echo $this->userId;
//         $userFolder = $this->storage->getUserFolder($this->userId); //gives you Folder object
//         $filePath = $userFolder->get("/Photos/Frog.jpg"); // gives you string
// echo 'filepath';
// 		try {
//             $file = $userFolder->getById(29);
// 			echo $file;
//             if ($file instanceof \OCP\Files\File) {
//                 return $file->getContent();
//             } else {
//                 throw new Exception('Can not read from folder');
//             }
//         } catch(\OCP\Files\NotFoundException $e) {
//             throw new Exception('File does not exist');
//         }
// 		echo $userFolder;
//         return new DataResponse($filePath);
return "fdadksjfhdsk";
    }

}
