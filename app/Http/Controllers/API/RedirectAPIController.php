<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateRedirectAPIRequest;
use App\Http\Requests\API\UpdateRedirectAPIRequest;
use App\Models\Redirect;
use App\Repositories\RedirectRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;

use App\Repositories\RedirectLogRepository;

/**
 * Class RedirectAPIController
 */
class RedirectAPIController extends AppBaseController
{
    private RedirectRepository $redirectRepository;

    public function __construct(RedirectRepository $redirectRepo)
    {
        $this->redirectRepository = $redirectRepo;
    }

    /**
     * Display a listing of the Redirects.
     * GET|HEAD /redirects
     */
    public function index(Request $request): JsonResponse
    {
        $redirects = $this->redirectRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($redirects->toArray(), 'Redirects retrieved successfully');
    }

    /**
     * Store a newly created Redirect in storage.
     * POST /redirects
     */
    public function store(CreateRedirectAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $result = $this->redirectRepository->verificarUrl($input['redirect_url']);

        if(!$result['success']) {
            return $this->sendError($result['message'], $result['code']);
        }

        $redirect = $this->redirectRepository->create($input);

        return $this->sendResponse($redirect->toArray(), 'Redirect saved successfully');
    }

    /**
     * Display the specified Redirect.
     * GET|HEAD /redirects/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var Redirect $redirect */
        $redirect = $this->redirectRepository->find($id);

        if (empty($redirect)) {
            return $this->sendError('Redirect not found');
        }

        return $this->sendResponse($redirect->toArray(), 'Redirect retrieved successfully');
    }

    /**
     * Update the specified Redirect in storage.
     * PUT/PATCH /redirects/{id}
     */
    public function update($id, UpdateRedirectAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Redirect $redirect */
        $redirect = $this->redirectRepository->find($id);

        if (empty($redirect)) {
            return $this->sendError('Redirect not found');
        }

        $redirect = $this->redirectRepository->update($input, $id);

        return $this->sendResponse($redirect->toArray(), 'Redirect updated successfully');
    }

    /**
     * Remove the specified Redirect from storage.
     * DELETE /redirects/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var Redirect $redirect */
        $redirect = $this->redirectRepository->find($id);

        if (empty($redirect)) {
            return $this->sendError('Redirect not found');
        }

        $redirect->status_id = 2; // Inativo
        $redirect->save();

        $redirect->delete();

        return $this->sendSuccess('Redirect deleted successfully');
    }

    public function redirect(Request $request) {

        $redirect = $this->redirectRepository->buscarPorCodigo($request->redirect);

        if(empty($redirect)) {
            return view('welcome');
        }

        $requestInfo = app('App\Repositories\RedirectLogRepository')->tratarRequest($request);

        app('App\Repositories\RedirectLogRepository')->create([
            'redirect_id' => $redirect->id,
            'ip_request' => $requestInfo['clientIp'],
            'user_agent_request' => $requestInfo['userAgent'],
            'header_referer_request' => $requestInfo['referer'],
            'query_param_request' => json_encode($requestInfo['queryParams']),
            'access_at' => date('Y-m-d H:i:s'),
        ]);

        $finalUrl = $this->redirectRepository->montarUrlFinal($redirect, $requestInfo['queryParams']);

        return redirect($finalUrl);
    }
}
