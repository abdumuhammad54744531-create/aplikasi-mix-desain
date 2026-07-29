<?php

namespace App\Http\Controllers;

use App\Models\ReferenceHeader;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferenceHeader::query();
        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('standard_number', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"));
        }
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        return view('references', [
            'standards' => $query->orderBy('category')->orderBy('standard_number')->get(),
            'categories' => ReferenceHeader::orderBy('category')->distinct()->pluck('category'),
            'total' => ReferenceHeader::count(),
        ]);
    }
}
