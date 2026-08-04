<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\Jenjang;

class UpdateJenjangAction
{
    public function execute(Jenjang $jenjang, array $data): Jenjang
    {
        $jenjang->update([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return $jenjang;
    }
}
