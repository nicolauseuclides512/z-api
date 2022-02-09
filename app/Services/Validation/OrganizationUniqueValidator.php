<?php
namespace App\Services\Validation;

use Illuminate\Support\Facades\DB;
use App\Models\AuthToken;

class OrganizationUniqueValidator
{
    /**
     * format: org_unique:table_name(required),column_name(default same as attribute),
     * ignored_id(default=0),ignored_id_column(default=id)
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (!isset($parameters[0])) {
            throw \Exception('First parameter must be correct table name.',
                500);
        }

        $tableName = $parameters[0];
        $organizationId = $this->getOrganizationId();

        if (!isset($parameters[1])) {
            $colName = $attribute;
        } else {
            $colName = $parameters[1];
        }
        if (!isset($parameters[2])) {
            $ignoreId = 0;
        } else {
            $ignoreId = $parameters[2];
        }
        if (!isset($parameters[3])) {
            $ignoreColumn = 'id';
        } else {
            $ignoreColumn = $parameters[3];
        }

        $query = DB::table($tableName)
            ->where('organization_id', $organizationId)
            ->where($colName, $value);

        if ($ignoreId != 0) {
            $query = $query->where($ignoreColumn, '<>', $ignoreId);
        }

        return !$query->exists();
    }

    private function getOrganizationId()
    {
        return AuthToken::info()->organizationId;
    }

}
