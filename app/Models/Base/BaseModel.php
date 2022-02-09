<?php
/**
 * @author Jehan Afwazi Ahmad <jehan@ontelstudio.com>.
 */

namespace App\Models\Base;

use App\Models\AuthToken;
use App\Models\Contract\BaseModelContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;

/**
 * Class BaseModel
 * @package App\Models
 */
abstract class BaseModel extends Model implements BaseModelContract
{

    use ProvidesConvenienceMethods;

    const STATUS_ALL = 99;
    const STATUS_DELETED = -1;
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    protected $fillable = [
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $columnStatus = '';

    protected $columnDefault = ["*"];

    protected $columnSimple = [];

    protected static $autoValidate = true;

    protected $filterNameCfg = null;

    protected $softDeleteCascades = [];

    protected $nestedBelongConfigs = [];

    protected $nestedHasManyConfigs = [];

    protected $showActiveOnly = false;

    protected $loginInfo = null;

    protected $organizationIdField = "organization_id";

    public function __construct()
    {
        parent::__construct();
    }

    public function filterCfg()
    {
        return Config::get(
            empty($this->filterNameCfg)
                ? "filters." . $this->getTable()
                : "filters." . $this->filterNameCfg
        );
    }

    public function getLoginInfo()
    {
        return $this->loginInfo;
    }

    public function setLoginInfo($loginInfo)
    {
        $this->loginInfo = $loginInfo;
        return $this;
    }

    public function scopeGetByIdRef($q, $id)
    {
        return $q->where(self::getKeyName(), "=", $id);
    }

    protected function getExclusiveOrganizationIdField()
    {
        $temp = $this->organizationIdField;
        if (!empty($this->table)) {
            $temp = $this->table . "." . $temp;
        }
        return $temp;
    }

    public function scopeGetInOrgRef($q)
    {
        return $q->where($this->getExclusiveOrganizationIdField(),
            "=", $this->getOrganizationId());
    }

    public function getOrganizationId()
    {
        return AuthToken::info()->organizationId;
    }

    /**
     * get data by id in organization
     * @param $q
     * @param null $id
     * @return mixed
     */
    public function scopeGetByIdInOrgRef($q, $id = null)
    {
        if (!(Schema::hasColumn($this->getTable(), 'organization_id')))
            return $q->where(self::getKeyName(), "=", $id);

        return $q->where(self::getKeyName(), "=", $id)->where("organization_id", "=", AuthToken::info()->organizationId);
    }

    /**
     * @param $q
     * @param bool $status
     * @param array $column
     * @return mixed
     * @internal param int $showed
     * @internal param bool $status
     */
    public function scopeCast($q, $status = true, $column = [])
    {
        if (empty($column)) $column = $this->columnDefault;

        if (empty($this->columnStatus)) {
            return $q->select($column);
        }

        return $this->showActiveOnly ? $q->where($this->columnStatus, "=", $status)->select($column) : $q->select($column);
    }


    /**
     * @param $q
     * @return mixed
     */
    public function scopeNested($q)
    {
        if (!empty($this->nestedBelongConfigs)) {
            $configBelongs = $this->nestedBelongConfigs;
            $result = array_map(function ($k, $v) {
                return [
                    $k => function ($q) use ($v) {
                        $q->addSelect($v);
                    }
                ];
            }, array_keys($configBelongs), array_values($configBelongs));
            $q = $q->with(call_user_func_array("array_merge", $result));
        }

        if (!empty($this->nestedHasManyConfigs)) {

            $configMany = $this->nestedHasManyConfigs;
            $result = array_map(function ($k, $v) {
                return [
                    $k => function ($q) use ($v) {
                        $q->addSelect($v);
                    }
                ];
            }, array_keys($configMany), array_values($configMany));
            $q = $q->with(call_user_func_array("array_merge", $result));
        }

        return $q;
    }

    public function getColumnStatus()
    {
        return $this->columnStatus;
    }

    public function getColumnDefault()
    {
        return $this->columnDefault;
    }

    public function getColumnSimple()
    {
        return $this->columnSimple;
    }

    protected static function setNullWhenEmpty($model)
    {
        foreach ($model->toArray() as $name => $value) {

            if (empty($value)) {
                $model->{$name} = null;
            }

            if (is_float($value)) {
                $model->{$name} = 0;
            }

            if (is_bool($value)) {
                $model->{$name} = (boolean)$value;
            }
        }

        #remove additional attribute
        foreach ($model->appends as $name => $value) {
            unset($model->{$value});
        }
    }

    /**
     * @return boolean
     */
    public function isShowActiveOnly()
    {
        return $this->showActiveOnly;
    }

    /**
     * @param boolean $showActiveOnly
     * @return
     */
    public function setShowActiveOnly($showActiveOnly)
    {
        $this->showActiveOnly = $showActiveOnly;
        return $this->cast();
    }

    public function rules($id = null)
    {
        return [];
    }

    public function populate($request = [], BaseModel $model = null)
    {
        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $query = "")
    {
        return $q;
    }

    public function saveInOrganization()
    {
        $this->{$this->organizationIdField} = $this->getOrganizationId();
        return $this->save();
    }

}
