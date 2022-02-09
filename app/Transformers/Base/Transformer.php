<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers\Base;

use App\Cores\CustomTransformerPaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\TransformerAbstract;

/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */
abstract class Transformer extends TransformerAbstract
{
    const SIMPLE_FIELDS = [];

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $includeFields = [];

    /**
     * Transformer constructor.
     * assign fractal manager
     */
    public function __construct()
    {
        $this->manager = New Manager();
        $this->manager->setSerializer(new CustomDataSerializer());
    }

    /**
     * @param $data
     * @return array|\League\Fractal\Resource\Item
     */
    public function createItem($data)
    {

        $result = $this->item($data, $this);
        $result = $this->manager->createData($result)->toArray();

        return $result;
    }

    /**
     * @param $data
     * @return array|\League\Fractal\Resource\Collection
     */
    public function createCollection($data)
    {

        $result = $this->collection($data, $this);
        $result = $this->manager->createData($result)->toArray();

        return $result;
    }

    /**
     * @param $data
     * @return CustomTransformerPaginator
     */
    public function createCollectionPageable($data)
    {
        $result = $this->collection($data->items(), $this, 'data');

        $result->setPaginator(new IlluminatePaginatorAdapter($data));
        $result = $this->manager->createData($result)->toArray();

        return new CustomTransformerPaginator($data, $result['data']);
    }

    /**
     * @return mixed
     */
    abstract public static function inst();

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function showFields(array $fields)
    {
        if (empty($fields))
            $this->fields = [];
        else
            $this->fields = $fields;

        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function includeRelations(array $attributes)
    {
        $this->manager->parseIncludes($attributes);
        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function excludeRelations(array $attributes)
    {
        $this->manager->parseExcludes($attributes);
        return $this;
    }

    /**
     * filter transform by field
     *
     * @param $data
     * @return array
     */
    protected function filterTransform($data)
    {
        if (empty($this->fields)) {
            return $data;
        }

        return array_intersect_key($data, array_flip((array)$this->fields));
    }

    /**
     * set specific field for every relation
     * e.g: [ 'your_relation_1' => [ your_fields ] ]
     *
     * @param array $multiDimensionalRelationFields
     * @return $this
     */
    public function showIncludeFields(array $multiDimensionalRelationFields)
    {
        if ($multiDimensionalRelationFields) {
            if ($this->defaultIncludes) {
                if (array_intersect(array_keys($multiDimensionalRelationFields), $this->defaultIncludes)) {
                    $this->includeFields = $multiDimensionalRelationFields;
                }
            } elseif ($this->availableIncludes) {
                if (array_intersect(array_keys($multiDimensionalRelationFields), $this->availableIncludes)) {
                    $this->includeFields = $multiDimensionalRelationFields;
                }
            }
        }

        return $this;
    }
}