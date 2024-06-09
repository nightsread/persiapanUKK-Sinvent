<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        if ($search) {
            $rsetKategori = Kategori::where('deskripsi', 'like', '%' . $search . '%')
                                    ->orWhere('kategori', 'like', '%' . $search . '%')
                                    ->paginate(10);
        } else {
            $rsetKategori = Kategori::paginate(10);
        }

        return view('v_kategori.index', compact('rsetKategori'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Return the create form view
        return view('v_kategori.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'deskripsi' => 'required|string|max:100|unique:kategori,deskripsi',
            'kategori' => 'required|in:M,A,BHP,BTHP'
        ]);        

        // Create a new Kategori record
        Kategori::create([
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori
        ]);


        // Redirect to the index page with a success message
        return redirect()->route('kategori.index')->with('Success', 'Data saved successfully!');

        return redirect()->route('kategori.index')->with(['Gagal' => 'Data already exists!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Fetch the Kategori record with the specified ID
        $rsetKategori = Kategori::find($id);

        // Return the show view with the Kategori data
        return view('v_kategori.show', compact('rsetKategori'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetch the Kategori record with the specified ID
        $rsetKategori = Kategori::find($id);

        // Return the edit form view with the Kategori data
        return view('v_kategori.edit', compact('rsetKategori'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'deskripsi' => 'required|string|max:100',
            'kategori' => 'required|in:M,A,BHP,BTHP',
        ]);        

        // Fetch the Kategori record with the specified ID
        $rsetKategori = Kategori::find($id);

        // Update the Kategori record
        $rsetKategori->update([
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ]);

        // Redirect to the index page with a success message
        return redirect()->route('kategori.index')->with('success', 'Successfully modified');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        if (DB::table('barang')->where('kategori_id', $id)->exists()){ 
            return redirect()->route('kategori.index')->with(['Gagal' => 'Data failed to delete']);
        } else {
            $rseKategori = Kategori::find($id);
            $rseKategori->delete();
            return redirect()->route('kategori.index')->with(['Success' => 'Successfully deleted']);
        }
    }
}