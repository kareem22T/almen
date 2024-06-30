<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\Models\Event;
use App\Models\Topevent;
use App\Models\Ad;
use Carbon\Carbon;

class EventController extends Controller
{
    use HandleResponseTrait;

    public function get() {
        $events = Event::latest()
                       ->select("id", "title", "sub_title", "title_ar", "sub_title_ar", "cover", "thumbnail", "landscape", "portrait", "url", "date_from", "date_to", "location_id")
                       ->with(['relatedEvents' => function($query) {
                            $query->select("id", "title", "sub_title", "title_ar", "sub_title_ar", "cover", "thumbnail", "landscape", "portrait", "url", "date_from", "date_to", "location_id")
                                  ->where('date_to', '>=', now()); // Ensure active events only
                        }, "location"])
                       ->get();

        foreach ($events as $event) {
            $event->date_from_formatted = Carbon::parse($event->date_from)->format('h:i A');
            $event->date_to_formatted = Carbon::parse($event->date_to)->format('h:i A');
            foreach ($event->relatedEvents as $relatedEvent) {
                $relatedEvent->date_from_formatted = Carbon::parse($relatedEvent->date_from)->format('h:i A');
                $relatedEvent->date_to_formatted = Carbon::parse($relatedEvent->date_to)->format('h:i A');
            }
        }

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            $events,
            [
                "يبدا مسار الصورة من بعد الدومين مباشرا"
            ]
        );
    }

    public function event(Request $request) {
        $event = Event::latest()
                      ->select("id", "title", "sub_title", "title_ar", "sub_title_ar", "cover", "thumbnail", "landscape", "portrait", "url", "date_from", "date_to", "location_id")
                      ->with(['relatedEvents' => function($query) {
                            $query->select("id", "title", "sub_title", "title_ar", "sub_title_ar", "cover", "thumbnail", "landscape", "portrait", "url", "date_from", "date_to", "location_id")
                                  ->where('date_to', '>=', now()); // Ensure active events only
                        }, "location"])
                      ->find($request->id);

        if ($event) {
            $event->date_from_formatted = Carbon::parse($event->date_from)->format('h:i A');
            $event->date_to_formatted = Carbon::parse($event->date_to)->format('h:i A');
            foreach ($event->relatedEvents as $relatedEvent) {
                $relatedEvent->date_from_formatted = Carbon::parse($relatedEvent->date_from)->format('h:i A');
                $relatedEvent->date_to_formatted = Carbon::parse($relatedEvent->date_to)->format('h:i A');
            }
        }

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            $event,
            [
                "يبدا مسار الصورة من بعد الدومين مباشرا"
            ]
        );
    }

    public function search(Request $request) {
        $search = $request->search ? $request->search : '';
        $events = Event::latest()
                       ->select("id", "title", "sub_title", "title_ar", "sub_title_ar", "cover", "thumbnail", "landscape", "portrait", "url", "date_from", "date_to", "location_id")
                       ->where('title', 'like', '%' . $search . '%')
                       ->orWhere('sub_title', 'like', '%' . $search . '%')
                       ->orWhere('sub_title_ar', 'like', '%' . $search . '%')
                       ->orWhere('title_ar', 'like', '%' . $search . '%')
                       ->with(['relatedEvents' => function($query) {
                           $query->select("id", "title", "sub_title", "cover", "thumbnail", "landscape", "portrait", "url", "date_from", "date_to", "location_id")
                                 ->where('id', '!=', $query->getModel()->id);
                       }, "location"])
                       ->get();

        foreach ($events as $event) {
            $event->date_from_formatted = Carbon::parse($event->date_from)->format('h:i A');
            $event->date_to_formatted = Carbon::parse($event->date_to)->format('h:i A');
            foreach ($event->relatedEvents as $relatedEvent) {
                $relatedEvent->date_from_formatted = Carbon::parse($relatedEvent->date_from)->format('h:i A');
                $relatedEvent->date_to_formatted = Carbon::parse($relatedEvent->date_to)->format('h:i A');
            }
        }

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            $events,
            [
                "search" => "البحث بالعنوان او العنوان الفرعي"
            ]
        );
    }

    public function getTop() {
        $events = Topevent::orderBy("sort", "asc")->get();

        if ($events->count() > 0) {
            foreach ($events as $item) {
                $itemObj = $item->type == 1 ? Event::with(["location", 'relatedEvents' => function($query) {
                    $query->select("id", "title", "sub_title", "title_ar", "sub_title_ar", "cover", "thumbnail", "landscape", "portrait", "url", "date_from", "date_to", "location_id")
                          ->where('date_to', '>=', now()); // Ensure active events only
                    }])->find($item->item_id) : Ad::find($item->item_id);
                if ($itemObj) {
                    $itemObj->type = $item->type == 1 ? "Event" : "Ad";
                    $itemObj->date_from_formatted = $itemObj->date_from ? Carbon::parse($itemObj->date_from)->format('h:i A') : null;
                    $itemObj->date_to_formatted = $itemObj->date_to ? Carbon::parse($itemObj->date_to)->format('h:i A') : null;
                    if ($itemObj->type == "Event") {
                        foreach ($itemObj->relatedEvents as $relatedEvent) {
                            $relatedEvent->date_from_formatted = Carbon::parse($relatedEvent->date_from)->format('h:i A');
                            $relatedEvent->date_to_formatted = Carbon::parse($relatedEvent->date_to)->format('h:i A');
                        }
                    }
                    $item->item = $itemObj;
                }
            }
        }

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            $events,
            [
                "type" => [
                    1 => "Event",
                    2 => "Ad"
                ]
            ]
        );
    }
}
