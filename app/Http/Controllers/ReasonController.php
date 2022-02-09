<?php
namespace App\Http\Controllers;

use App\Cores\Jsonable;
use App\Domain\Contracts\ReasonContract;
use App\Domain\Data\ReasonData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;

class ReasonController extends Controller
{
    use Jsonable;

    private $reasonService;

    public function __construct(ReasonContract $reasonService)
    {
        $this->reasonService = $reasonService;
    }

    public function create()
    {
        try {
            $categories = config('reasons.categories');

            return $this->json(Response::HTTP_OK,
                'Create reason resource loaded', [
                    'categories' => $categories,
                ]);
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = ReasonData::new($request);
            $result = $this->reasonService->create($data);

            return $this->json(
                Response::HTTP_CREATED,
                trans('messages.reason_added'), $result);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function fetch(Request $request)
    {
        try {
            $category = $request->get('category_code');
            $result = $this->reasonService->getByCategory($category);
            return $this->json(Response::HTTP_OK,
                'Reason fetched',
                $result
            );

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function detail($id)
    {
        try {
            $result = $this->reasonService->detail($id);

            return $this->json(Response::HTTP_OK,
                'Reason data loaded',
                $result
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $data = ReasonData::update($id, $request);
            $result = $this->reasonService->update($data);

            return $this->json(
                Response::HTTP_CREATED,
                'Reason data updated', $result);

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }

    public function delete($id)
    {
        try {
            $this->reasonService->delete($id);

            return $this->json(Response::HTTP_OK,
                trans('messages.reason_deleted')
            );
        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }
}
