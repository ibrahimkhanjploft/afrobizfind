<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Event;

class EventController extends Controller
{
    //

    public function store(Request $request)
    {

       
    	$eventdata=new Event; 

    	 $imgcnt=count($request->images);
    	
    	 if($imgcnt<=10)
    	 {
    	 	$flyerimage=$request['flyerimage'];
	    	$flyername=strtolower(time().$flyerimage->getClientOriginalName());
	    	$flyerpath = public_path().'/mainflyer/';
	    	$flyerimage->move($flyerpath, $flyername);


	    	foreach ($request->images as $key) {
                $image = $key;
                $filename = strtolower(time().$image->getClientOriginalName());
                $path = public_path().'/events/';
                // $storepath=public_path().'/events/'.$filename;
                $image->move($path, $filename);
                $myimg[] = $filename;
            }

            $storeimg=$eventdata->images= implode(',', $myimg);
             	
	    	$eventdata['eventname']=$request->eventname;
	    	$eventdata['price']=$request->price;
	    	$eventdata['date']=$request->date;
	    	$eventdata['location']=$request->location;
	    	$eventdata['termscondition']=$request->termscondition;
	    	$eventdata['organizer']=$request->organizer;
	    	$eventdata['contactno']=$request->contactno;
	    	$eventdata['flyerimage']=$flyername;
	    	$eventdata['images']=$storeimg;

	    	if($eventdata->save())
	    	{
	    		return response()->json(['result'=>1,'message'=>'Event Added successfully']);
	    	}
	    	else
	    	{
	    		return response()->json(['result'=>0,'error'=>'Something went wrong please try again later.']);	
	    	}
    	 }
    	 else
    	 {
    	 	return response()->json(['errimg'=>'Please select events picture less than or equal 10']);	
    	 }


    	
          
            

    	
    }
}
