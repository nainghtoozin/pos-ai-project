<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('tax.view'), 403);

        $search = $request->get('search');

        $taxes = Tax::when($search, fn($q) =>
            $q->where('name', 'like', "%$search%")
        )
        ->orderBy('id', 'desc')
        ->paginate(10);

        return view('taxes.index', compact('taxes', 'search'));
    }

    public function store(StoreTaxRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('tax.create'), 403);

        Tax::create($request->validated());

        return redirect()->route('taxes.index')->with('success', 'Tax created successfully.');
    }

    public function update(UpdateTaxRequest $request, Tax $tax): RedirectResponse
    {
        abort_unless(auth()->user()->can('tax.edit'), 403);

        $tax->update($request->validated());

        return redirect()->route('taxes.index')->with('success', 'Tax updated successfully.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        abort_unless(auth()->user()->can('tax.delete'), 403);

        $tax->delete();

        return redirect()->route('taxes.index')->with('success', 'Tax deleted successfully.');
    }
}
