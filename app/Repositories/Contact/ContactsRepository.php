<?php

namespace App\Repositories\Contact;

use App\Repositories\AbstractRepository;
use App\Repositories\Contact\Contact;

class ContactsRepository extends AbstractRepository
{
    /**
     * Gets the model.
     *
     * @return  string
     */
    public function getModel()
    {
        return Contact::class;
    }

    public function create($data)
    {
        return parent::create([
            'email' => $data['email'],
            'data' => $data,
        ]);
    }

    public function save($data)
    {
        $contact = $this->query()->where('email', $data['email'])->first();

        if ($contact) {
            return $this->update($contact->id, [
                'data' => $data
            ]);
        }

        return $this->create($data);
    }
}
