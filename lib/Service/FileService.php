<?php
declare(strict_types=1);
namespace OCA\ThirdApp\Service;

use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\IURLGenerator;
use OCP\IUserSession;
class FileService {

 
    private IURLGenerator $urlGenerator;
    private IUserSession $userSession;
    private IRootFolder $rootFolder;
    public function __construct(IURLGenerator $urlGenerator, IUserSession $userSession, IRootFolder $rootFolder){
        $this->urlGenerator = $urlGenerator;
        $this->userSession = $userSession;
        $this->rootFolder = $rootFolder;
    }

    public function getImageFromFolder(Folder $folder, int $fileId, string $user)
	{
	  $res = [];
	  $targetFolder = ($folder->getById($fileId))[0]; 
	  if($targetFolder instanceof Folder) {
		  $items = $targetFolder->getDirectoryListing();
		  foreach ($items as $item) {
			if ($item instanceof File && $item->getMimePart() == 'image' ) {
			  $thumbnailUrl = $this->urlGenerator->linkToRoute('core.Preview.getPreviewByFileId', ['x' => 480, 'y' => 180, 'fileId' => $item->getId()]);
			  $res[] = [
				"thumbnailUrl" => $thumbnailUrl,
				"exif" => exif_read_data($item->fopen('r+')),
				"fileId" => $item->getId()
			  ];
			}
	  
			// if ($item instanceof Folder) {
			//   $res = array_merge($res, $this->getImageFromFolder($item, $fileId, $user));
			// }
		  }
	}

	  return $res;
	}

    public function getImageByFileId(int $fileId){
		$currentUser = $this->userSession->getUser();
		$userFolder = $this->rootFolder->getUserFolder($currentUser->getUID()); // OC\Files\Node\Folder
		$files = $userFolder->getById((int)$fileId);
		$first = $files[0]->getPath();

		$userPath = $userFolder->getPath();
		$return = [$userPath, $first];
		return new JSONResponse($return);

	}
}