<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourPckage extends Model
{
    public function itineraries()
    {
        return $this->hasMany('App\Itinerary');
    }
    public function users()
    {
      return $this->hasMany('App\TourPckage');
	}
	public function upcomming_tour_packages()
    {
        return $this->belongsToMany('App\UpcommingTourPackage','tour_pckage_upcomming_tour_packages')->withTimestamps();
    }

    public static function completedAndPendingItem($id)
    {
        $pending_item = array();
        $total_inclusion = InclusionTourPckage::where('tour_package_id', $id)->count();
        $total_location =  LocationPointOfInterest::where('tour_package_id', $id)->count();
        $total_itineary = Itinerary::where('tour_package_id', $id)->count();
        $total_days = TourPckage::where('id', $id)->value('total_days');
        $total_people = People::where('tour_package_id', $id)->count();
        $total_flight = Flight::where('tour_package_id', $id)->count();
        $total_hotel = Hotel::where('tour_package_id', $id)->count();
        $total_document = PdfItinerary::where('tour_package_id', $id)->count();
        $total_dep_manager = DepartureManager::where('tour_package_id', $id)->count();
        $total_dep_guide = DepartureGuide::where('tour_package_id', $id)->count();
        $total_dep_communication = Communication::where('tour_package_id', $id)->count();
        $total_dep_placard = Placard::where('tour_package_id', $id)->count();
        
        if($total_inclusion == '0'){
            array_push($pending_item, 'Inclusion');
        }
        if($total_location == '0'){
            array_push($pending_item, 'Location');
        }
        if($total_itineary < $total_days){
            array_push($pending_item, 'Itinerary');
        }
        if($total_people == '0'){
            array_push($pending_item, 'People');
        }
        if($total_flight == '0'){
            array_push($pending_item, 'Flight');
        }
        if($total_hotel == '0'){
            array_push($pending_item, 'Hotel');
        }
        if ($total_document == '0'){
            array_push($pending_item, 'Document & Creation');
        }

        if ($total_dep_manager == '0' && $total_dep_guide == '0' && $total_dep_communication == '0' && $total_dep_placard == '0'){
            array_push($pending_item, 'Communication');
        }
        return $pending_item;
    }

    // public static function completeStatus($id)
    // {
    //     $pending = TourPckage::completedAndPendingItem($id);
    //     $pending_module = count($pending);
    //     if($pending_module < 1){
    //         TourPckage::where('id', $id)->where('status', '0')->update(['status' => 1]);
    //     }
    //     return true;
    // }  // Closed 19-May-2020
}
