<?php
declare(strict_types=1);

namespace OCA\ThirdApp\Controller;

use OCA\ThirdApp\Service\FileService;
use OCA\ThirdApp\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;

use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\IUserSession;



class FileController extends Controller {
	private FileService $service;
	private IUserSession $userSession;
	private ?string $userId;
	private IRootFolder $iRootFolder;
	use Errors;

	public function __construct(  IRequest $request,
								FileService $service,
								?string $userId,  IRootFolder $iRootFolder,) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
		$this->iRootFolder = $iRootFolder;
	}

	/**
     * @NoCSRFRequired
     */
	public function index()
	{
	  $res = $this->service->getImageFromFolder($this->iRootFolder->getUserFolder($this->userId));

	  return new DataResponse($res);
	
	}

	/**
     * @NoCSRFRequired
     */
	public function getImageByFileId(int $fileId){
		return $this->service->getImageByFileId($fileId);

	}

}
