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
use App\TourPckage;
use App\Tenant;
use App\User;
use App\MenuLabel;
use App\Label;
use App\MenuLabelIcon;

class MenuLabelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        $menu_label = MenuLabel::where('tenant_id',auth()->user()->tenant_id)->get();
        $child_count = count($menu_label);
        $menu_label_icon = MenuLabelIcon::get();

        $Master_menu_label = Label::orderBy('label', 'ASC')->get();
        $master_count = count($Master_menu_label);
        $remain_label = $master_count - $child_count;
        return view('setting.edit_menu_labels',compact('menu_label','menu_label_icon','tenant','Master_menu_label','count','remain_label'));
    }
/**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $input = $request->All();
       //dd($input);
        $lname=$request->menu_label;
        $count = 0;
        foreach($lname as $index=>$value) {
            if($value !== null) $count++;
        }
        $name_label = $count;
        $lid=$request->menu_id;
        $liconid=$request->label_icon;

        for ($i=0; $i<$name_label; $i++) {
            MenuLabel::UpdateOrCreate(['id' => $lid[$i]],
                                     [
                                        'label' => $lname[$i],
                                        'button_name' => $lname[$i],
                                        'menu_label_icon_id' => $liconid[$i],
                                        'tenant_id' => auth()->user()->tenant_id,
                                        'user_id' => auth()->user()->id,
                                    ]);
            
        }
        //die;
        $request->session()->flash('status', 'Labels Updated!');
            //return Redirect::to('tour-package');
            return redirect::to('menu-labels/edit');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $label = MenuLabel::where('tenant_id',auth()->user()->tenant_id)->first();
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        $menu_label = Label::orderBy('label', 'ASC')->get();
        $menu_label_icon = MenuLabelIcon::get();
        if($label==null){          
            return view('setting.create_menu_labels',compact('menu_label','menu_label_icon','label','tenant'));
        }
        else{
              return redirect::to('menu-labels/edit');
        }
        
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
            // 'menu_label[]' => 'required|max:11',
            // 'label_icon[]' => 'required'
         ]); 
         $input = Input::all();
        $a=$request->menu_label;
        //dd($a);
        foreach ($a as $key=> $label) {
          if($label){
            $menu_lavels  = new MenuLabel;
            $menu_lavels->label = $input['menu_label'][$key];
            $menu_lavels->button_name = $input['menu_label'][$key];
            $menu_lavels->menu_label_icon_id = $input['label_icon'][$key];
            $user = auth()->user();
            $menu_lavels->tenant_id = $user->tenant_id;
            $menu_lavels->user_id = $user->id;
            $menu_lavels->save();
          }
          else{
            $request->session()->flash('status', 'Please select labels blank data does not added!');
            //return Redirect::to('tour-package');
            return redirect::to('menu-labels/create');
          }
        }           
            $request->session()->flash('status', 'Labels added!');
            //return Redirect::to('tour-package');
            return redirect::to('menu-labels/create');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createLabelIcons()
    {
        $tenant = Tenant::where('tenant_id', Auth()->user()->tenant_id)->select('company_logo')->first();
        return view('setting.menu_label_icons','tenant');
    }

    public function storeLabelIcons(Request $request)
    {
       $data = $request->all();
        $validatedData = $request->validate([
           'name' => 'required|max:255',
           'icon_image'  => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'          
        ]); 
        
        $labelicon  = new MenuLabelIcon;
        $labelicon->name = $request->name;      
        $user = auth()->user();

        $labelicon->tenant_id = $user->tenant_id;
        $labelicon->user_id = $user->id;
        $file = $request->file('icon_image');
        $labels = $request->name;
            $labels1 =explode(' ',$labels);           
            $labels2 =implode('_',$labels1);

            if($request->hasFile('icon_image')){ 
                $image = $request->file('icon_image');
                $extension = $image->getClientOriginalExtension();
                $filename=$labels2.time().'.'.$extension;
                $relPath = 'images/uploads/labelicons/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 777, true);
                }
                //Image::make($image)->resize(50, 50)->save( storage_path('/uploads/' . $filename ) );
                Image::make($image)->save( public_path($relPath . $filename ) );
                $labelicon->icon_image = $filename;                    
             };


        $labelicon->save();
         
        $request->session()->flash('status', 'Icon image created successfully.');
        return Redirect::to('menu-labels-icons');
    }
    
}
