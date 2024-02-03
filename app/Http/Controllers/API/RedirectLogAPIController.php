<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateRedirectLogAPIRequest;
use App\Http\Requests\API\UpdateRedirectLogAPIRequest;
use App\Models\RedirectLog;
use App\Repositories\RedirectLogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;

/**
 * Class RedirectLogAPIController
 */
class RedirectLogAPIController extends AppBaseController
{
    private RedirectLogRepository $redirectLogRepository;

    public function __construct(RedirectLogRepository $redirectLogRepo)
    {
        $this->redirectLogRepository = $redirectLogRepo;
    }

    /**
     * Display a listing of the RedirectLogs.
     * GET|HEAD /redirect-logs
     */
    public function index(Request $request): JsonResponse
    {
        $redirectLogs = $this->redirectLogRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($redirectLogs->toArray(), 'Redirect Logs retrieved successfully');
    }

    /**
     * Store a newly created RedirectLog in storage.
     * POST /redirect-logs
     */
    public function store(CreateRedirectLogAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $redirectLog = $this->redirectLogRepository->create($input);

        return $this->sendResponse($redirectLog->toArray(), 'Redirect Log saved successfully');
    }

    /**
     * Display the specified RedirectLog.
     * GET|HEAD /redirect-logs/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var RedirectLog $redirectLog */
        $redirectLog = $this->redirectLogRepository->find($id);

        if (empty($redirectLog)) {
            return $this->sendError('Redirect Log not found');
        }

        return $this->sendResponse($redirectLog->toArray(), 'Redirect Log retrieved successfully');
    }

    /**
     * Update the specified RedirectLog in storage.
     * PUT/PATCH /redirect-logs/{id}
     */
    public function update($id, UpdateRedirectLogAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var RedirectLog $redirectLog */
        $redirectLog = $this->redirectLogRepository->find($id);

        if (empty($redirectLog)) {
            return $this->sendError('Redirect Log not found');
        }

        $redirectLog = $this->redirectLogRepository->update($input, $id);

        return $this->sendResponse($redirectLog->toArray(), 'RedirectLog updated successfully');
    }

    /**
     * Remove the specified RedirectLog from storage.
     * DELETE /redirect-logs/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var RedirectLog $redirectLog */
        $redirectLog = $this->redirectLogRepository->find($id);

        if (empty($redirectLog)) {
            return $this->sendError('Redirect Log not found');
        }

        $redirectLog->delete();

        return $this->sendSuccess('Redirect Log deleted successfully');
    }
}
