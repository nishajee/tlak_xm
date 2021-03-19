<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Image;
use Auth;
use App\DocumentIcon;
use App\Tenant;
use App\User;
class DocumentIconController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $documeticons = DocumentIcon::get();
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
         return view('documenticon.document_icon',compact('documeticons','tenant'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       //return view('poiicon.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $data = $request->all();
        $validatedData = $request->validate([
           'name' => 'required|max:255',
           'icon_image' => 'required',
           
        ]); 
        
        $poiicon  = new DocumentIcon;
        $poiicon->name = $request->name;      
        $user = auth()->user();
        $poiicon->tenant_id = $user->tenant_id;

        $file = $request->file('icon_image');
        $tourpkg = $request->name;
            $tourpkg1 =explode(' ',$tourpkg);           
            $tourpkg2 =implode('_',$tourpkg1);

            if($request->hasFile('icon_image')){ 
                $image = $request->file('icon_image');
                $extension = $image->getClientOriginalExtension();
                $filename=$tourpkg2.time().'.'.$extension;
                $relPath = 'images/uploads/documenticon/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 777, true);
                }
                //Image::make($image)->resize(50, 50)->save( storage_path('/uploads/' . $filename ) );
                Image::make($image)->save( public_path($relPath . $filename ) );
                $poiicon->icon_image = $filename;                    
             };


        $poiicon->save();
         
        $request->session()->flash('status', 'Document Icon created successfully.');
        return Redirect::to('document-icon');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
