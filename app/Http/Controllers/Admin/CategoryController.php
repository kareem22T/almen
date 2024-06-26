<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\SaveImageTrait;
use App\DeleteImageTrait;
use App\Models\Category;
use App\Models\Product;
use App\Models\Gallery;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use HandleResponseTrait, SaveImageTrait, DeleteImageTrait;

    public function index() {
        return view('Admin.categories.index');
    }

    public function get() {
        $categories = Category::all();

        return $this->handleResponse(
            true,
            "",
            [],
            [
                $categories
            ],
            []
        );
    }

    public function add() {
        return view("Admin.categories.create");
    }

    public function edit($id) {
        $category = Category::latest()->find($id);

        if ($category)
            return view("Admin.categories.edit")->with(compact("category"));

        return $this->handleResponse(
            false,
            "Category not exits",
            ["Categry id not valid"],
            [],
            []
        );
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            "title" => ["required", "max:100"],
            "description" => ["required"],
            "title_ar" => ["required", "max:100"],
            "description_ar" => ["required"],
            'svg_icon' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "name.required" => "ادخل اسم القسم",
            "name.max" => "يجب الا يتعدى اسم القسم 100 حرف",
            "description.required" => "ادخل وصف القسم",
            "thumbnail.required" => "الصورة المصغرة للقسم مطلوبة",
            "cover.required" => "الكوفر للقسم مطلوبة",
            "thumbnail.image" => "من فضلك ارفع صورة صالحة",
            "thumbnail.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
            "thumbnail.max" => "يجب الا يتعدى حجم الصورة 2 ميجا",
            "cover.image" => "من فضلك ارفع كوفر صالحة",
            "cover.mimes" => "يجب ان تكون كوفر بين هذه الصيغ (jpeg, png, jpg, gif)",
            "cover.max" => "يجب الا يتعدى حجم الكوفر 2 ميجا",
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        $image = $this->saveImg($request->thumbnail, 'images/uploads/Categories', "thum" . time());

        $cover = $this->saveImg($request->cover, 'images/uploads/Categories', "cover" . time());
        $svg_icon = $this->saveImg($request->svg_icon, 'images/uploads/Categories', "svg_icon" . time());

        $category = Category::create([
            "title" => $request->title,
            "description" => $request->description,
            "svg_icon" => '/images/uploads/Categories/' . $svg_icon,
            "title_ar" => $request->title_ar,
            "description_ar" => $request->description_ar,
            "thumbnail_path" => '/images/uploads/Categories/' . $image,
            "cover_path" => '/images/uploads/Categories/' . $cover,
        ]);

        if ($category)
            return $this->handleResponse(
                true,
                "تم اضافة القسم بنجاح",
                [],
                [],
                []
            );

    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            "id" => ["required"],
            "title" => ["required", "max:100"],
            "description" => ["required"],
            "title_ar" => ["required", "max:100"],
            "description_ar" => ["required"],
            'cover' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'svg_icon' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "name.required" => "ادخل اسم القسم",
            "name.max" => "يجب الا يتعدى اسم القسم 100 حرف",
            "description.required" => "ادخل وصف القسم",
            "thumbnail.required" => "الصورة المصغرة للقسم مطلوبة",
            "cover_path.required" => "الكوفر للقسم مطلوبة",
            "thumbnail.image" => "من فضلك ارفع صورة صالحة",
            "thumbnail.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
            "thumbnail.max" => "يجب الا يتعدى حجم الصورة 2 ميجا",
            "cover.image" => "من فضلك ارفع كوفر صالحة",
            "cover.mimes" => "يجب ان تكون كوفر بين هذه الصيغ (jpeg, png, jpg, gif)",
            "cover.max" => "يجب الا يتعدى حجم الكوفر 2 ميجا",
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        $category = Category::find($request->id);

        if ($request->thumbnail) {
            $this->deleteFile(base_path($category->thumbnail_path));
            $image = $this->saveImg($request->thumbnail, 'images/uploads/Categories', "thum" . time());
            $category->thumbnail_path= '/images/uploads/Categories/' . $image;
        }

        if ($request->cover) {
            $this->deleteFile(base_path($category->cover_path));
            $image = $this->saveImg($request->cover, 'images/uploads/Categories', "cover" . time());
            $category->cover_path= '/images/uploads/Categories/' . $image;
        }

        if ($request->svg_icon) {
            $this->deleteFile(base_path($category->svg_icon));
            $image = $this->saveImg($request->svg_icon, 'images/uploads/Categories', "svg_icon" . time());
            $category->svg_icon= '/images/uploads/Categories/' .  $image;
        }

        $category->title = $request->title;
        $category->description = $request->description;
        $category->title_ar = $request->title_ar;
        $category->description_ar = $request->description_ar;
        $category->save();

        if ($category)
            return $this->handleResponse(
                true,
                "تم تحديث القسم بنجاح",
                [],
                [],
                []
            );

    }

    public function deleteIndex($id) {
        $category = Category::find($id);

        if ($category)
            return view("Admin.categories.delete")->with(compact("category"));

        return $this->handleResponse(
            false,
            "Category not exits",
            ["Categry id not valid"],
            [],
            []
        );
    }

    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            "id" => ["required"],
        ], [
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        $category = Category::find($request->id);

        $this->deleteFile(base_path($category->thumbnail_path));
        $this->deleteFile(base_path($category->cover_path));

        $category->delete();

        if ($category)
            return $this->handleResponse(
                true,
                "تم حذف القسم بنجاح",
                [],
                [],
                []
            );

    }

}
