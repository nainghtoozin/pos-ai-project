<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('payment_method.view'), 403);

        $search = $request->get('search');
        $type = $request->get('type');

        $paymentMethods = PaymentMethod::when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderBy('name')
            ->paginate(25);

        return view('payment_methods.index', compact('paymentMethods', 'search', 'type'));
    }

    public function store(StorePaymentMethodRequest $request)
    {
        abort_unless(auth()->user()->can('payment_method.create'), 403);

        PaymentMethod::create($request->validated());

        return redirect()->route('payment_methods.index')->with('success', 'Payment method created successfully.');
    }

    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        abort_unless(auth()->user()->can('payment_method.edit'), 403);

        $paymentMethod->update($request->validated());

        return redirect()->route('payment_methods.index')->with('success', 'Payment method updated successfully.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        abort_unless(auth()->user()->can('payment_method.delete'), 403);

        $paymentMethod->delete();

        return redirect()->route('payment_methods.index')->with('success', 'Payment method deleted successfully.');
    }
}
