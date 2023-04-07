<?php

namespace App\Http\Controllers;

use App\Services\InternetServiceProvider\Mpt;
use App\Services\InternetServiceProvider\Ooredoo;
use Illuminate\Http\Request;

class InternetServiceProviderController extends Controller
{
    public function getMptInvoiceAmount(Request $request)
    {
        $mpt = new Mpt();
        return $this->getInvoiceAmount($request, $mpt);
    }
    
    public function getOoredooInvoiceAmount(Request $request)
    {
        $ooredoo = new Ooredoo();
        return $this->getInvoiceAmount($request, $ooredoo);
    }

    public function getInvoiceAmount(Request $request, $operator) {
        $operator->setMonth($request->get('month') ?: 1);
        $amount = $operator->calculateTotalAmount();

        return response()->json([
            'data' => $amount
        ]);
    }

}
