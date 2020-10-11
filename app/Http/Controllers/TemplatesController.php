<?php

namespace App\Http\Controllers;

use App\Repositories\Template\TemplatesRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class TemplatesController extends BaseController
{
    public function index(Request $request, TemplatesRepository $repository)
    {
        $items = $repository->get();

        return new JsonResponse($items);
    }

    public function create(Request $request, TemplatesRepository $repository)
    {
        $data = $request->only(['subject', 'message']);
        $template = $repository->create($data);

        return new JsonResponse($template);
    }

    public function update(Request $request, TemplatesRepository $repository, $id)
    {
        $data = $request->only(['subject', 'message']);
        $template = $repository->update($id, $data);

        return new JsonResponse($template);
    }

    public function delete(Request $request, TemplatesRepository $repository, $id)
    {
        $result = $repository->delete($id);

        if ($result) {
            return new Response('', 204);
        }

        return new JsonResponse(['error' => 'Cannot delete item'], 400);
    }
}
