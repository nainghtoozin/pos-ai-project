<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('unit.view'), 403);
        
        $search = $request->get('search');
        $units = Unit::when($search, fn($q) => $q->where('name', 'like', "%$search%")->orWhere('short_name', 'like', "%$search%"))
            ->orderBy('id', 'desc')
            ->paginate(10);
            
        return view('units.index', compact('units', 'search'));
    }

    public function store(StoreUnitRequest $request)
    {
        abort_unless(auth()->user()->can('unit.create'), 403);
        
        Unit::create($request->validated());
        
        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        abort_unless(auth()->user()->can('unit.edit'), 403);
        
        $unit->update($request->validated());
        
        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        abort_unless(auth()->user()->can('unit.delete'), 403);
        
        $unit->delete();
        
        return redirect()->route('units.index')->with('success', 'Unit deleted successfully.');
    }
}
