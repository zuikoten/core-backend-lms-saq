<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\Jenjang;

class CreateJenjangAction
{
    public function execute(array $data): Jenjang
    {
        return Jenjang::query()->create([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }
}
