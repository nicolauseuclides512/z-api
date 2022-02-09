<?php

namespace App\Jobs\Item;

use App\Jobs\Job;
use App\Models\Item;
use Illuminate\Support\Facades\Log;

class ImportMassItemJob extends Job
{
    private $file;
    private $authInfo;

    public function __construct($file, $authInfo)
    {
        $this->file = $file;
        $this->authInfo = $authInfo;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        try {
            Log::info('IMPORT ITEM MASS TO ORG ' . $this->authInfo->organizationName . " | PIC " . $this->authInfo->email . " STARTING");
            if (Item::inst()->importMass($this->file, (array)$this->authInfo))
                return true;

            return false;
        } catch (\Exception $e) {
            Log::error('import mass item job => ' . $e->getMessage());
            throw $e;
        }
    }
}
