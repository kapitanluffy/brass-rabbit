<?php

namespace App\Http\Controllers;

use App\CsvParser\Manager;
use App\Repositories\Contact\ContactsRepository;
use App\Repositories\Template\TemplatesRepository;
use App\TemplateParser\TemplateParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ContactsController extends BaseController
{
    public function load(Request $request, ContactsRepository $repository)
    {
        $contacts = $request->files->get('contacts');
        $csv = Manager::read($contacts->getPathName());

        $rows = $csv->each(function ($row) use ($repository) {
            return $repository->save($row->toArray());
        });

        $data = [
            'headers' => $csv->headers(),
            'rows' => $rows
        ];

        return new JsonResponse($data);
    }

    public function preview(
        TemplatesRepository $templates,
        ContactsRepository $repository,
        TemplateParser $parser,
        $id
    ) {
        $contact = $repository->find($id);
        $templateSet = $templates->get();

        $preview = $parser->preview($contact, $templateSet->all());

        return new JsonResponse($preview);
    }
}
