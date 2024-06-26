<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $fillable = [
        "title",
        "sub_title",
        "thumbnail_path",
        "url",
        'title_ar',
        'sub_title_ar',
        'cover_path',
    ];

    public $table = "locations";

    /**
     * The roles that belong to the Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function events()
    {
        return $this->hasMany(Event::class, "location_id");
    }
    public function restaurants()
    {
        return $this->hasMany(Restaurant::class, "location_id");
    }
    public function getLocationDetailsWithEvents()
    {
        $locationDetails = [
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'title_ar' => $this->title_ar,
            'sub_title_ar' => $this->sub_title_ar,
            'thumbnail_path' => $this->thumbnail_path,
            'cover_path' => $this->cover_path,
            'url' => $this->url,
        ];

        // Get all events for the location
        $events = $this->events()->with(['event_categories'])->get();
        $restaurants = $this->restaurants()->get();

        // Initialize an array to store categories with events
        $categoriesWithEvents = [];

        // Loop through events and group them by category
        foreach ($events as $event) {
            foreach ($event->event_categories as $category) {
                if (!isset($categoriesWithEvents[$category->id])) {
                    $categoriesWithEvents[$category->id] = [
                        'name' => $category->title,
                        'name_ar' => $category->title_ar,
                        'svg_icon' => $category->svg_icon,
                        'events' => [],
                    ];
                }
                $categoriesWithEvents[$category->id]['events'][] = $event;
            }
        }

        // Format the result
        $locationDetails['categories'] = array_values($categoriesWithEvents);
        $locationDetails['services'] = $restaurants;

        return $locationDetails;
    }
}
