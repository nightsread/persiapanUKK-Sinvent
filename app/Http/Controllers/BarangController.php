<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Kategori; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $rsetBarang = Barang::all();
        $rsetBarang = Barang::with('kategori')->get();
        // return view('v_barang.index', [
        //     'rsetBarang' => $rsetBarang,
        //     'count' => 0
        // ]);

        $search = $request->query('search');
        if ($search) {
            $rsetBarang = Barang::where('merk', 'like', '%' . $search . '%')
                                    ->orWhere('seri', 'like', '%' . $search . '%')
                                    ->orWhere('spesifikasi', 'like', '%' . $search . '%')
                                    ->orWhere('stok', 'like', '%' . $search . '%')
                                    ->orWhere('kategori_id', 'like', '%' . $search . '%')
                                    ->orWhereHas('kategori', function($query) use ($search) {
                                        $query->where('deskripsi', 'like', '%' . $search . '%');
                                    })
                                    ->paginate(10);
        } else {
            $rsetBarang = Barang::paginate(10);
        }

        return view('v_barang.index', compact('rsetBarang'));
    }

    public function create()
    {
        $rsetKategori = Kategori::all();
        return view('v_barang.create', compact('rsetKategori'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merk' => [
                'required',
                'string',
                'max:50',
                Rule::unique('barang')->where(function ($query) use ($request) {
                    return $query->where('seri', $request->seri);
                }),
            ],
            'seri' => 'nullable|string|max:50',
            'spesifikasi' => 'nullable|string',
            'stok' => 'nullable|integer',
            'kategori_id' => 'required|exists:kategori,id',
        ], [
            'merk.required' => 'Item brand must be filled in',
            'merk.unique' => 'Item brand already exists!',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('barang.create')
                ->withErrors($validator)
                ->withInput()
                ->with(['error' => 'Failed to add an item!']);
        }

        Barang::create([
            'merk' => $request->merk,
            'seri' => $request->seri,
            'spesifikasi' => $request->spesifikasi,
            'stok' => $request->stok,
            'kategori_id' => $request->kategori_id,
        ]);

        return redirect()->route('barang.index')->with(['Success' => 'Successfully saved!']);
    }

    public function show(string $id)
    {
        $rsetBarang = Barang::find($id);
        return view('v_barang.show', compact('rsetBarang'));
    }

    public function edit(string $id)
    {
        $rsetBarang = Barang::find($id);
        $rsetKategori = Kategori::all(); 
        return view('v_barang.edit', compact('rsetBarang', 'rsetKategori'));
    }

    public function update(Request $request, Barang $barang)
    {
    $validator = Validator::make($request->all(), [
        'merk' => [
            'required',
            'string',
            'max:50',
            Rule::unique('barang')->ignore($barang->id)->where(function ($query) use ($request) {
                return $query->where('seri', $request->seri);
            }),
        ],
        'seri' => 'nullable|string|max:50',
        'spesifikasi' => 'nullable|string',
        'stok' => 'nullable|integer',
        'kategori_id' => 'required|exists:kategori,id',
    ], [
        'merk.required' => 'Item brand must be filled in',
        'merk.unique' => 'Item brand already exists!',
    ]);

    if ($validator->fails()) {
        return redirect()->route('barang.edit', $barang)
            ->withErrors($validator)
            ->withInput()
            ->with(['error' => 'Failed to update item!']);
    }

    $barang->update($request->all());

    return redirect()->route('barang.index')->with(['success' => 'Updated successfully!']);
    }

    public function destroy($id)
    {
        if (DB::table('barangmasuk')->where('barang_id', $id)->exists() || DB::table('barangkeluar')->where('barang_id', $id)->exists()){ 
            return redirect()->route('barang.index')->with(['Gagal' => 'Data failed to delete!']);
        } else {
            $rseBarang = Barang::find($id);
            $rseBarang->delete();
            return redirect()->route('barang.index')->with(['Success' => 'Data deleted successfully']);
        }
    }
}
