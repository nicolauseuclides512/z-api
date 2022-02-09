<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Models\BaseTraits;

use App\Models\AuthToken;
use App\Models\Base\BaseModel;
use Illuminate\Support\Facades\Validator;


trait ObserveModelTrait
{
    private static function hasConditionalRule($model)
    {
        return method_exists($model, 'conditionalRules');
    }

    public static function bootObservable()
    {
        static::saving(function (BaseModel $model) {
            $model->created_by = AuthToken::$info->email ?? 'anonymous';

            if ($model::$autoValidate) {
                $validation = self::makeValidator($model);

                #cheking validation
                if ($validation->fails()) {
                    $model->errors = $validation->errors()->all();
                    return false;
                } else {
                    return true;
                }
            }
        });

        static::updating(function (BaseModel $model) {
            $model->created_by = AuthToken::$info->email ?? 'anonymous';
            $model->updated_by = AuthToken::$info->email ?? 'anonymous';

        });

        static::deleting(function (BaseModel $model) {
            $model->deleted_by = AuthToken::$info->email ?? 'anonymous';

            if (!empty($model->softDeleteCascades)) {
                foreach ($model->softDeleteCascades as $relation) {
                    foreach ($model->{$relation} as $item) {
                        $item->delete();
                    }
                }
            }

        });


    }

    private static function makeValidator(&$model)
    {
        $validation = Validator::make($model->attributes,
            $model->rules($model->{$model->primaryKey}));

        if (self::hasConditionalRule($model)) {
            $model->conditionalRules($validation);
        }
        return $validation;
    }
}
