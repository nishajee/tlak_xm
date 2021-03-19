<ul class="kt-nav kt-nav--bold kt-nav--md-space kt-nav--v4" id="itinerary-tabs">
   @if(isset($disableDeparture))
  <li class="kt-nav__item active">
        <a class="kt-nav__link not-active0" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('editTour',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Departure" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">1. Basic Details</span>
        </a>
    </li>
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('edit_inclusion',request()->route('id'))}}">
            <span class="kt-nav__link-text 
            <?php 
            if( in_array( "Inclusion" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">2. Inclusions/Exclusions <?php 
            if( in_array( "Inclusion" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
      
    </li>
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active1" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('add_location',request()->route('id'))}}">  <span class="kt-nav__link-text
            <?php 
            if( in_array( "Location" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">3. Locations <?php 
            if( in_array( "Location" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
    
      
    </li>
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active2" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('add_itineary',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Itinerary" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">4. Itinerary <?php 
            if( in_array( "Itinerary" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
     
    </li>
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active3" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('add_people',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "People" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">5. Travelers <?php 
            if( in_array( "People" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
      
    
    </li>
    
    <li class="kt-nav__item">
     
        <a class="kt-nav__link not-active4" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('add_flight',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Flight" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">6. Flight Info <?php 
            if( in_array( "Flight" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
      
     
    </li>

    <li class="kt-nav__item">
     
        <a class="kt-nav__link not-active5" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('add_hotel',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Hotel" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">7. Hotel Info <?php 
            if( in_array( "Hotel" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
      
     
      
    </li>

    <li class="kt-nav__item">
        <a class="kt-nav__link not-active9" href="{{route('termandconditions_index',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Notification" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">8. Terms & Conditions</span>
        </a>
    </li>
    
    <li class="kt-nav__item">
        <a class="kt-nav__link not-active9" href="{{route('notification',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Notification" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">9. Notifications/Alerts</span>
        </a>
    </li>
    
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active6" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('document_creation',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Document & Creation" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">10. Tour Documents <?php 
            if( in_array( "Document & Creation" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
    
    
    </li>

   
    <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active7" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('createCommunication',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Communication" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">11. Operations Team <?php 
            if( in_array( "Communication" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
    
    </li>
     <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active11" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('edit_dep_upcoming',request()->route('id'))}}">
            <span class="kt-nav__link-text">12. Upcoming Tours</span>
        </a>
    
    </li>
    <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active13" style="pointer-events: none; opacity: 0.3; cursor: no-drop" href="{{route('api_dep_setting',request()->route('id'))}}">
            <span class="kt-nav__link-text">13. Departure Settings</span>
        </a>
    
    </li>
    <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active12" href="{{route('appFeedback',request()->route('id'))}}">
            <span class="kt-nav__link-text">14. Feedback</span>
        </a>
    
    </li>
    
  @elseif(request()->route('id'))
  <li class="kt-nav__item active">
        <a class="kt-nav__link not-active0" href="{{route('editTour',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Departure" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">1. Basic Details</span>
        </a>
    </li>
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active" href="{{route('edit_inclusion',request()->route('id'))}}">
            <span class="kt-nav__link-text 
            <?php 
            if( in_array( "Inclusion" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">2. Inclusions/Exclusions <?php 
            if( in_array( "Inclusion" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
      
    </li>
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active1" href="{{route('add_location',request()->route('id'))}}">  <span class="kt-nav__link-text
            <?php 
            if( in_array( "Location" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">3. Locations <?php 
            if( in_array( "Location" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
    
      
    </li>
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active2" href="{{route('add_itineary',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Itinerary" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">4. Itinerary <?php 
            if( in_array( "Itinerary" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
     
    </li>
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active3" href="{{route('add_people',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "People" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">5. Travelers <?php 
            if( in_array( "People" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
      
    
    </li>
    
    <li class="kt-nav__item">
     
        <a class="kt-nav__link not-active4" href="{{route('add_flight',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Flight" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">6. Flight Info <?php 
            if( in_array( "Flight" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
      
     
    </li>

    <li class="kt-nav__item">
     
        <a class="kt-nav__link not-active5" href="{{route('add_hotel',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Hotel" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">7. Hotel Info <?php 
            if( in_array( "Hotel" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
      
     
      
    </li>

    <li class="kt-nav__item">
        <a class="kt-nav__link not-active9" href="{{route('termandconditions_index',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Notification" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">8. Terms & Conditions</span>
        </a>
    </li>
    
    <li class="kt-nav__item">
        <a class="kt-nav__link not-active9" href="{{route('notification',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Notification" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">9. Notifications/Alerts</span>
        </a>
    </li>
    
    <li class="kt-nav__item">
      
        <a class="kt-nav__link not-active6" href="{{route('document_creation',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Document & Creation" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">10. Tour Documents <?php 
            if( in_array( "Document & Creation" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
    
    
    </li>

   
    <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active7" href="{{route('createCommunication',request()->route('id'))}}">
            <span class="kt-nav__link-text
            <?php 
            if( in_array( "Communication" ,$penandcomitem ) ) 
            echo 'kt-font-danger kt-font-bold'; 
            else echo 'kt-font-success kt-font-bold';
            ?>
            ">11. Operations Team <?php 
            if( in_array( "Communication" ,$penandcomitem ) ) 
            echo'<i style="font-size: 70%; font-weight: 600;">(Incomplete)</i>';?></span>
        </a>
    
    </li>
    <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active11" href="{{route('edit_dep_upcoming',request()->route('id'))}}">
            <span class="kt-nav__link-text">12. Upcoming Tours</span>
        </a>
    
    </li>
   <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active13" href="{{route('api_dep_setting',request()->route('id'))}}">
            <span class="kt-nav__link-text">13. Departure Settings</span>
        </a>
    
    </li>
    <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active12" href="{{route('appFeedback',request()->route('id'))}}">
            <span class="kt-nav__link-text">14. Feedback</span>
        </a>
    
    </li>
    @else
    <li class="kt-nav__item active">
        <a class="kt-nav__link not-active0" href="{{route('createTour')}}">
            <span class="kt-nav__link-text kt-font-success kt-font-bold">1. Basic Details</span>
        </a>
    </li>
    <li class="kt-nav__item">
      
      <a class="kt-nav__link not-active rrmenu">
            <span class="kt-nav__link-text">2. Inclusions/Exclusions</span>
        </a>
    
    </li>
    <li class="kt-nav__item">
      
      <a class="kt-nav__link not-active1 rrmenu" >
            <span class="kt-nav__link-text">3. Locations</span>
        </a>
      
    </li>
    <li class="kt-nav__item">
      
      
      <a class="kt-nav__link not-active2 rrmenu">
            <span class="kt-nav__link-text">4. Itinerary</span>
        </a>
      
    </li>
    <li class="kt-nav__item">
      
      
      <a class="kt-nav__link not-active3 rrmenu">
            <span class="kt-nav__link-text">5. Travelers</span>
        </a>
      
    </li>
    
    <li class="kt-nav__item">
     
      
      <a class="kt-nav__link not-active4 rrmenu" >
            <span class="kt-nav__link-text">6. Flight Info</span>
        </a>
      
    </li>
    <li class="kt-nav__item">
        <a class="kt-nav__link not-active5 rrmenu">
            <span class="kt-nav__link-text">7. Hotel Info</span>
        </a>
    </li>
    
    <li class="kt-nav__item">
        <a class="kt-nav__link not-active9 rrmenu">
            <span class="kt-nav__link-text">9. Terms & Conditions</span>
        </a>
    </li>

    <li class="kt-nav__item">
        <a class="kt-nav__link not-active9 rrmenu">
            <span class="kt-nav__link-text">9. Notifications/Alerts</span>
        </a>
    </li>
    
    <li class="kt-nav__item">
    <a class="kt-nav__link not-active6 rrmenu">
            <span class="kt-nav__link-text">10. Tour Documents</span>
        </a>
      
    </li>
    <li class="kt-nav__item">
       
    
        <a class="kt-nav__link not-active7 rrmenu">
            <span class="kt-nav__link-text">11. Operations Team</span>
        </a>
    
    </li>
    <li class="kt-nav__item">
       
    
        <a class="kt-nav__link not-active11 rrmenu">
            <span class="kt-nav__link-text">12. Upcoming Tours</span>
        </a>
    
    </li>
    <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active13 rrmenu">
            <span class="kt-nav__link-text">13. Departure Settings</span>
        </a>
    
    </li>
    <li class="kt-nav__item">
       
        <a class="kt-nav__link not-active12 rrmenu">
            <span class="kt-nav__link-text">14. Feedback</span>
        </a>
    
    </li>
@endif
</ul>

<style type="text/css">


    .kt-nav .kt-nav__item.kt-nav__item--active > .kt-nav__link .kt-nav__link-icon, .kt-nav .kt-nav__item.kt-nav__item--active > .kt-nav__link .kt-nav__link-text, .kt-nav .kt-nav__item.kt-nav__item--active > .kt-nav__link .kt-nav__link-arrow, .kt-nav .kt-nav__item.active > .kt-nav__link .kt-nav__link-icon, .kt-nav .kt-nav__item.active > .kt-nav__link .kt-nav__link-text, .kt-nav .kt-nav__item.active > .kt-nav__link .kt-nav__link-arrow, .kt-nav .kt-nav__item:hover:not(.kt-nav__item--disabled):not(.kt-nav__item--sub) > .kt-nav__link .kt-nav__link-icon, .kt-nav .kt-nav__item:hover:not(.kt-nav__item--disabled):not(.kt-nav__item--sub) > .kt-nav__link .kt-nav__link-text, .kt-nav .kt-nav__item:hover:not(.kt-nav__item--disabled):not(.kt-nav__item--sub) > .kt-nav__link .kt-nav__link-arrow{
      color: #9492a1;
    }
    .kt-nav .kt-nav__item.kt-nav__item--active > .kt-nav__link, .kt-nav .kt-nav__item.active > .kt-nav__link, .kt-nav .kt-nav__item:hover:not(.kt-nav__item--disabled):not(.kt-nav__item--sub) > .kt-nav__link {
    background-color: #fff;
    -webkit-transition: all 0.3s;
    transition: all 0.3s;
}
.kt-nav.kt-nav--v4 .kt-nav__item:hover {
    -webkit-transition: all 0.3s;
    transition: all 0.3s;
    background-color: #fff;
}
.rrmenu {
    cursor: no-drop !important;
}
</style>
