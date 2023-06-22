<?php
namespace Modules\BackupNetworking\Actions;
use CControllerResponseData;
use CController;
class MyBackup extends CController
{
    public function init(): void {$this->disableCsrfValidation();}
    protected function checkInput(): bool {return true;}
    protected function checkPermissions(): bool {return true;}
    protected function doAction(): void
    {

	$directorio = '/home/user/backup-config';
	$archivos = scandir($directorio);

	$response = new CControllerResponseData(['tabla' => $archivos]);
	$this->setResponse($response);
	
    }
}
