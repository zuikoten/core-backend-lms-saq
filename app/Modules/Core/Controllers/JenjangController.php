<?php

namespace Modules\Core\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Actions\CreateJenjangAction;
use Modules\Core\Actions\DeleteJenjangAction;
use Modules\Core\Actions\UpdateJenjangAction;
use Modules\Core\Models\Jenjang;
use Modules\Core\Requests\StoreJenjangRequest;
use Modules\Core\Requests\UpdateJenjangRequest;

class JenjangController extends Controller
{
    public function index(): View
    {
        return view('modules.core.jenjang.index', [
            'jenjangList' => Jenjang::query()->withCount('gradeLevels')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('modules.core.jenjang.create');
    }

    public function store(StoreJenjangRequest $request, CreateJenjangAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('jenjang.index')->with('status', 'Jenjang berhasil ditambahkan.');
    }

    public function edit(Jenjang $jenjang): View
    {
        return view('modules.core.jenjang.edit', ['jenjang' => $jenjang]);
    }

    public function update(UpdateJenjangRequest $request, Jenjang $jenjang, UpdateJenjangAction $action): RedirectResponse
    {
        $action->execute($jenjang, $request->validated());

        return redirect()->route('jenjang.index')->with('status', 'Jenjang berhasil diperbarui.');
    }

    public function destroy(Jenjang $jenjang, DeleteJenjangAction $action): RedirectResponse
    {
        $action->execute($jenjang);

        return redirect()->route('jenjang.index')->with('status', 'Jenjang berhasil dihapus.');
    }
}
