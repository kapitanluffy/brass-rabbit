<?php

namespace App\Repositories\Template;

use App\Repositories\AbstractRepository;
use App\Repositories\Template\Template;

class TemplatesRepository extends AbstractRepository
{
    /**
     * Gets the model.
     *
     * @return  string
     */
    public function getModel()
    {
        return Template::class;
    }
}
