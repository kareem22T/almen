<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\Models\Location;

class LocationsController extends Controller
{
    use HandleResponseTrait;

    public function get() {
        $locations = Location::with(["events" => function ($q) {
            $q->select("id", "title", "sub_title", "cover", "thumbnail", "landscape", "portrait", "url", "date_from", "date_to");
        }, "restaurants"])->latest()->get();

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],

                $locations
            ,
            [
                "يبدا مسار الصورة من بعد الدومين مباشرا"
            ]
        );
    }

    public function search(Request $request) {
        $search = $request->search ? $request->search : '';
        $locations = Location::with(["events" => function ($q) {
            $q->select("id", "title", "sub_title", "cover", "thumbnail", "landscape", "portrait", "url", "date_from", "date_to");
        }, "restaurants"])->latest()->where('title', 'like', '%' . $search . '%')
        ->orWhere('sub_title', 'like', '%' . $search . '%')->get();

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],

                $locations
            ,
            [
                "search" => "البحث بالعنوان او العنوان الفرعي"
            ]
        );
    }

    public function getLocation(Request $request) {
        $location = Location::with("restaurants")->find($request->id); // Get a location instance

        if ($location) {

            $details = $location->getLocationDetailsWithEvents(); // Get the location details with events grouped by categories
            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],

                $details
                ,
                [
                    "search" => "البحث بالعنوان او العنوان الفرعي"
                    ]
                );
        }

    }
}
