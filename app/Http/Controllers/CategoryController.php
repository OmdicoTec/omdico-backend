<?php

namespace App\Http\Controllers;

use App\Models\category;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user_id = $request->user()->id;
        $validator = Validator::make($request->all(), [
            'is_actived' => 'required|boolean', // *
            'is_root' => 'nullable|boolean', // *
            'parent_id' => 'nullable|integer',
            'title' => 'required|string|max:128', // *
            'other_title' => 'nullable|string',
            'details' => 'nullable|string',
            'slug' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        // check if user has already filled the form or not
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'اطلاعات وارد شده صحیح نمی باشد.',
                    'is_success' => false,
                    'is_already_filled' => false,
                    'status_code' => 400,
                    'errors' => $validator->errors()->all(),
                ]
            );
        }

        // check is parent_id is valid or not
        if ($request->parent_id) {
            $parent = category::find($request->parent_id);
            if (!$parent) {
                // Hint! i can check it with $parent->exists() or $parent instanceof category
                return response()->json(
                    [
                        'message' => 'دسته بندی والد معتبر نمی باشد.',
                        'is_success' => false,
                        'is_already_filled' => false,
                        'status_code' => 400,
                    ]
                );
            }
        }
        // insert new record
        try {
            $category = category::create([
                'user_id' => $user_id,
                'is_actived' => $request->is_actived,
                'is_root' => $request->is_root, // true
                'parent_id' => ($request->parent_id == (null || 0) ? null : $request->parent_id),
                'title' => $request->title,
                'other_title' => $request->other_title,
                'details' => $request->details,
                'slug' => $request->slug !== '' ? str_replace(' ', '_', Str::lower($request->slug)) : Str::lower(Str::slug($request->title, '_')),
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'خطایی در ثبت اطلاعات رخ داده است.',
                    'is_success' => false,
                    'is_already_filled' => false,
                    'status_code' => 400,
                ]
            );
        }

        // return response
        return response()->json(
            [
                'message' => 'اطلاعات با موفقیت ثبت شد.',
                'is_success' => true,
                'is_already_filled' => false,
                'status_code' => 200,
                'data' => $category,
            ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(category $category)
    {
        // find category parent without children, parent_id is null
        $parents = $category->where('parent_id', null)->get();

        // for each parent, get childrenRecursive
        foreach ($parents as $parent) {
            $parent->childrenRecursive;
        }

        return response()->json(
            [
                'message' => 'اطلاعات با موفقیت دریافت شد.',
                'is_success' => true,
                'status_code' => 200,
                'data' => $category->all()->toArray(),
            ]
        );
    }

    public function recursive(category $category)
    {
        $parents = $category->where('parent_id', null)->get();

        // for each parent, get childrenRecursive
        foreach ($parents as $parent) {
            $parent->childrenRecursive;
        }

        return response()->json(
            [
                'message' => 'اطلاعات با موفقیت دریافت شد.',
                'is_success' => true,
                'status_code' => 200,
                'data' => $parents->toArray(),
            ]
        );
    }

    /**
     * Display the specified category for user
     * Only confirmed categories
     */
    public function index()
    {
        // find category parent without children, parent_id is null and is_actived is true
        $category = category::where('is_actived', 1)->get();

        return response()->json(
            [
                'message' => 'اطلاعات با موفقیت دریافت شد.',
                'is_success' => true,
                'status_code' => 200,
                'data' => $category->toArray(),
            ]
        );
    }
    /**
     * Show all categories
     */
    public function showAll(category $category)
    {
        // find category parent without children, parent_id is null
        $data = $category->where('parent_id', null)->get();

        // for each parent, get childrenRecursive
        foreach ($data as $parent) {
            $parent->childrenRecursive;
        }

        $store = array();
        foreach ($data->toArray() as $key => $value) {
            $store[$key] = [(string) $value['id'] . '_' => Arr::except($value, ['children_recursive'])] + $this->loopi($value['children_recursive']);  // i remove array_merge, Because it's cahnge keys of array to numeric
        }
        return response()->json(
            [
                'message' => 'اطلاعات با موفقیت دریافت شد.',
                'is_success' => true,
                'status_code' => 200,
                'data' => $store,
            ]
        );
    }
    public function loopi($data)
    {
        $data2 = array();
        foreach ($data as $key => $value) {
            // if haven't children_recursive
            if ($value['children_recursive'] == null) {
                $data2[(string) $value['id'] . '_'] = Arr::except($value, ['children_recursive']);
            } else {
                $data2[(string) $value['id'] . '_'] = Arr::except($value, ['children_recursive']);
                $data2 = $data2 + $this->loopi($value['children_recursive']); // i remove array_merge, Because it's cahnge keys of array to numeric
            }
        }
        return $data2;
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(category $id, Request $request)
    {
        // check request data
        $validator = Validator::make($request->all(), [
            'is_actived' => 'required|boolean', // *
            'is_root' => 'nullable|boolean', // *
            'parent_id' => 'nullable|integer',
            'title' => 'required|string|max:127', // *
            'seotitle' => 'nullable|string|max:255', // *
            'details' => 'nullable|string',
            'slug' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        // check if user has already filled the form or not
        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'اطلاعات وارد شده صحیح نمی باشد.',
                    'is_success' => false,
                    'is_already_filled' => false,
                    'status_code' => 400,
                    'errors' => $validator->errors()->all(),
                ]
            );
        }

        // update record
        try {
            $id->update([
                'is_actived' => $request->is_actived,
                'is_root' => $request->is_root, // true
                'parent_id' => ($request->parent_id == (null || 0) ? null : $request->parent_id),
                'title' => $request->title,
                'other_title' => $request->seotitle,
                'details' => $request->details,
                'slug' => $request->slug !== '' ? str_replace(' ', '_', Str::lower($request->slug)) : Str::lower(Str::slug($request->title, '_')),
                'description' => $request->description,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'خطایی در ثبت اطلاعات رخ داده است.',
                    'is_success' => false,
                    'is_already_filled' => false,
                    'status_code' => 400,
                    'data' => []
                ]
            );
        }
        return response()->json(
            [
                'message' => 'اطلاعات با موفقیت ثبت شد.',
                'is_success' => true,
                'is_already_filled' => false,
                'status_code' => 200,
                'data' => $id->toArray(),
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(category $id, Request $request)
    {
        // first check we have Uncategorized category or not, if not, create it and if yes, get it's id
        $user_id = $request->user()->id;
        $uncategorized = category::where('title', 'بی‌دسته')->first();
        if (!$uncategorized) {
            $uncategorized = category::create([
                'user_id' => $user_id,
                'is_actived' => false,
                'is_root' => false,
                'parent_id' => null,
                'title' => 'بی‌دسته',
                'other_title' => null,
                'details' => 'این دسته برای حفظ سایر وارث هست',
            ]);
        }
        // $data = $category->where('parent_id', null)->get();

        // for each parent, get childrenRecursive
        $data = [];
        try {
            if ($id->id != $uncategorized->id) {
                $data = $id->childrenRecursive;
                foreach ($data as $parent) {
                    $parent->update(['parent_id' => $uncategorized->id]);
                }
                $id->delete();
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'خطایی در حذف اطلاعات رخ داده است.',
                    'is_success' => false,
                    'is_already_filled' => false,
                    'status_code' => 400,
                    'data' => $data->toArray(),
                ]
            );
        }

        return response()->json(
            [
                'message' => 'اطلاعات با موفقیت حذف شد.',
                'is_success' => true,
                'is_already_filled' => false,
                'status_code' => 200,
                'data' => $id->toArray(),
            ]
        );
    }
}
