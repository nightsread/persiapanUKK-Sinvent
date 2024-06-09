<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Support\Facades\Validator;

class BarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        if ($search) {
            $barangMasuk = BarangMasuk::where('tgl_masuk', 'like', '%' . $search . '%')
                                    ->orWhere('qty_masuk', 'like', '%' . $search . '%')
                                    ->orWhere('barang_id', 'like', '%' . $search . '%')
                                    ->orWhereHas('barang', function($query) use ($search) {
                                        $query->where('merk', 'like', '%' . $search . '%');
                                    })
                                    ->paginate(10);
        } else {
            $barangMasuk = BarangMasuk::paginate(10);
        }

        return view('v_barangmasuk.index', compact('barangMasuk'));
    }

    public function create()
    {
        $rsetBarang = Barang::all();
        return view('v_barangmasuk.create', compact('rsetBarang'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tgl_masuk' => 'required|date',
            'qty_masuk' => 'required|integer',
            'barang_id' => 'required|exists:barang,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('barangmasuk.create')
                ->withErrors($validator)
                ->withInput();
        }

        BarangMasuk::create([
            'tgl_masuk' => $request->tgl_masuk,
            'qty_masuk' => $request->qty_masuk,
            'barang_id' => $request->barang_id,
        ]);

        return redirect()->route('barangmasuk.index')->with(['success' => 'Successfully saved!']);
    }

    public function show(string $id)
    {
        $barangMasuk = BarangMasuk::find($id);

        return view('v_barangmasuk.show', compact('barangMasuk'));
    }

    public function edit(string $id)
    {
        $barangMasuk = BarangMasuk::find($id);
        $rsetBarang = Barang::all();
        return view('v_barangmasuk.edit', compact('barangMasuk', 'rsetBarang'));
    }
    

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tgl_masuk' => 'required|date',
            'qty_masuk' => 'required|integer',
            'barang_id' => 'required|exists:barang,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('barangmasuk.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $barangMasuk = BarangMasuk::find($id);

        $barangMasuk->update([
            'tgl_masuk' => $request->tgl_masuk,
            'qty_masuk' => $request->qty_masuk,
            'barang_id' => $request->barang_id,
        ]);

        return redirect()->route('barangmasuk.index')->with(['success' => 'Successfully modified!']);
    }

    public function destroy($id)
    {
        $barangMasuk = BarangMasuk::find($id);
        $barang = Barang::find($barangMasuk->barang_id);

        if ($barang->stok - $barangMasuk->qty_masuk < 0) {
            return redirect()->route('barangmasuk.index')->with(['error' => 'Delete cannot be done!']);
        } else {
            $barang->stok -= $barangMasuk->qty_masuk;
            $barang->save();
            $barangMasuk->delete();
            return redirect()->route('barangmasuk.index')->with(['success' => 'Successfully deleted!']);
        }
    }
}