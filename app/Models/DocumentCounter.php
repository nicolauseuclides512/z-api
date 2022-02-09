<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Models;

/**
 * @property int id
 * @property int organization_id
 * @property string document_uri
 * @property string prefix
 * @property current_number
 * @property start_number
 * @property max_number
 */
class DocumentCounter extends MasterModel
{
    /**
     * Fixed format:
     * {prefix}/{year}/{counter}
     */
    const DEFAULT_FORMAT = "%s-%s";

    protected $table = "document_counters";

    protected $fillable = [
        'document_uri',
        'prefix',
        'start_number',
        'current_number',
        'max_number',
    ];

    protected $guarded = [
        'organization_id',
    ];

    public function getFormattedNumberAttribute($v)
    {
        $counter = str_pad($this->current_number, 4, '0', STR_PAD_LEFT);
        $strNumber = sprintf(self::DEFAULT_FORMAT, $this->prefix, $counter);
        return $strNumber;
    }

    public function resetCounter($commit = false)
    {
        $this->current_number = $this->start_number;
        if ($commit) {
            $this->saveInOrganization();
        }
    }

    public function incrementCounter($commit = false)
    {
        if ($this->current_number >= $this->max_number) {
            $this->resetCounter($commit);
        } else {
            $this->current_number += 1;
        }
        if ($commit) {
            $this->saveInOrganization();
        }
    }
}
