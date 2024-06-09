<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\BarangKeluar;
use Illuminate\Support\Facades\Validator;

class BarangKeluarController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        if ($search) {
            $barangKeluar = BarangKeluar::where('tgl_keluar', 'like', '%' . $search . '%')
                                    ->orWhere('qty_keluar', 'like', '%' . $search . '%')
                                    ->orWhere('barang_id', 'like', '%' . $search . '%')
                                    ->orWhereHas('barang', function($query) use ($search) {
                                        $query->where('merk', 'like', '%' . $search . '%');
                                    })
                                    ->paginate(10);
        } else {
            $barangKeluar = BarangKeluar::paginate(10);
        }

        return view('v_barangkeluar.index', compact('barangKeluar'));
    }

    public function create()
    {
        $rsetBarang = Barang::all();
        return view('v_barangkeluar.create', compact('rsetBarang'));
    }


    public function store(Request $request)
    {
        $barang = Barang::find($request->barang_id);
        $tgl_masuk = $barang->tgl_masuk;

        $validator = Validator::make($request->all(), [
            'tgl_keluar' => 'required|date|after_or_equal:' . $tgl_masuk,
            'qty_keluar' => [
                'required',
                'integer',
                'min:1',
                'max:' . $barang->stok
            ],
            'barang_id' => 'required|exists:barang,id',
        ], [
            'tgl_keluar.after_or_equal' => 'The exit date cannot be before the entry date',
        ]);

        if ($validator->fails()) {
            return redirect()->route('barangkeluar.create')
                ->withErrors($validator)
                ->withInput()
                ->with(['error' => 'Failed to add outgoing items!']);
        }$barang = Barang::find($request->barang_id);
        $tgl_masuk = $barang->tgl_masuk;

        $rules = [
            'qty_keluar' => [
                'required',
                'integer',
                'min:1',
                'max:' . $barang->stok
            ],
            'barang_id' => 'required|exists:barang,id',
        ];

        $messages = [];

        // Hanya terapkan validasi tanggal untuk barang dengan id 1
        if ($request->barang_id == 1) {
            $rules['tgl_keluar'] = 'required|date|after_or_equal:' . $tgl_masuk;
            $messages['tgl_keluar.after_or_equal'] = 'The exit date cannot be before the entry date';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->route('barangkeluar.create')
                ->withErrors($validator)
                ->withInput()
                ->with(['error' => 'Failed to add outgoing items!']);
        }

        BarangKeluar::create([
            'tgl_keluar' => $request->tgl_keluar,
            'qty_keluar' => $request->qty_keluar,
            'barang_id' => $request->barang_id
        ]);

        return redirect()->route('barangkeluar.index')->with(['success' => 'Successfully saved!']);
    }

    public function show(string $id)
    {
        $barangKeluar = BarangKeluar::find($id);

        return view('v_barangkeluar.show', compact('barangKeluar'));
    }

    public function edit(string $id)
    {
        $barangKeluar = BarangKeluar::find($id);
        $rsetBarang = Barang::all();
        return view('v_barangkeluar.edit', compact('barangKeluar', 'rsetBarang'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tgl_keluar' => 'required|date',
            'qty_keluar' => [
                'required',
                'integer',
                'min:1',
                'max:' . Barang::find($request->barang_id)->stok,
            ],
            'barang_id' => 'required|exists:barang,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('barangkeluar.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $barangKeluar = BarangKeluar::find($id);

        $barangKeluar->update([
            'tgl_keluar' => $request->tgl_keluar,
            'qty_keluar' => $request->qty_keluar,
            'barang_id' => $request->barang_id,
        ]);

        return redirect()->route('barangkeluar.index')->with(['success' => 'Successfully modified!']);
    }

    public function destroy($id)
    {
        $barangKeluar = BarangKeluar::find($id);
        $barangKeluar->delete();
        return redirect()->route('barangkeluar.index')->with(['success' => 'Successfully deleted!']);
    }
}

