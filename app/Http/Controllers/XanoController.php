<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class XanoController extends Controller
{
    public function getData()
    {
        $response = Http::get('https://xpjg-p6rt-dhkq.s2.xano.io/api:A2fAYu3x/contacts');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Failed to fetch data'], 500);
    }
}
