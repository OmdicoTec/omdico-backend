<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\category;
use Illuminate\Support\Facades\Validator;
use App\Models\category_characteristics as categoryChar;
use App\Models\characteristics;
use App\Models\product;
use App\Models\product_characteristics;
use Illuminate\Validation\Rule;

class CharacteristicsController extends Controller
{
    /**
     * Create a new feature.
     */
    public function createFeature(Request $request, category $category_id)
    {
        // add new characteristics
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'required|unique:characteristics|string|max:255',
            'input_type' => 'required|max:255',
            // TODO: validate values
            'values' => 'nullable|array',
        ]);
        // if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'is_success' => false,
                'status_code' => 422,
                'data' => [],
            ]);
        }

        try {
            $characteristics = characteristics::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'input_type' => $data['input_type'],
                'is_constant' => $data['input_type'] === 'text' || $data['input_type'] === 'number' ? false : true,
                'values' => json_encode($data['values']),
            ]);

            // connect to special category
            categoryChar::create([
                'category_id' => $category_id->id,
                'characteristic_id' => $characteristics->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "is_success" => false,
                "message" => "خطایی غیر منتظره رخ داده است.",
            ]);
        }


        // dd($category_id);
        return response()->json([
            "is_success" => true,
            'status_code' => 200,
            "data" => $category_id->toArray(),
            "messagea" => [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'input_type' => $data['input_type'],
                'is_constant' => $data['input_type'] === 'text' || $data['input_type'] === 'number' ? false : true,
                'values' => $data['values'],
            ],
            "message" => "با موفقیت ثبت شد."
        ]);
    }

    /**
     * Edit feature.
     */
    public function editFeature(characteristics $id, Request $request)
    {
        // add new characteristics
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'input_type' => 'required|max:255',
            'category' => 'required|integer|min:1',
            // TODO: validate values
            'values' => 'nullable|array',
            'pivot' => 'nullable|array',
            'pivot.category_id' => 'required|integer|min:1',
            'pivot.characteristic_id' => 'required|integer|min:1',
        ]);
        // if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'is_success' => false,
                'status_code' => 422,
                'data' => [],
            ]);
        }

        try {
            // update characteristics
            $id->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'input_type' => $data['input_type'],
                'is_constant' => $data['input_type'] === 'text' || $data['input_type'] === 'number' ? false : true,
                'values' => json_encode($data['values']),
            ]);

            $categoryChar = categoryChar::where('category_id', $data['pivot']['category_id'])->where('characteristic_id', $data['pivot']['characteristic_id'])->first();
            if ($categoryChar){
                $categoryChar->update([
                    'category_id' => $data['category']
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                "is_success" => false,
                "message" => "خطایی غیر منتظره رخ داده است.",
            ]);
        }

        return response()->json([
            "is_success" => true,
            'status_code' => 200,
            'message' => 'با موفقیت بروز رسانی شد',
            "data" => $data,
        ]);
        // TODO: find categoryChar where category_id and characteristic_id (SELF) and update
        // TODO: if change category_id, must remove product_characteristics where category_id pivote

    }
    /**
     * Remove feature.
     */

    public function deleteFeature(characteristics $id)
    {
        try {
            // update characteristics
            $id->delete();

        } catch (\Exception $e) {
            return response()->json([
                "is_success" => false,
                "message" => "خطایی غیر منتظره رخ داده است.",
            ]);
        }
        return response()->json([
            "is_success" => true,
            'status_code' => 200,
            'message' => 'با موفقیت حذف شد',
            "data" => null,
        ]);
    }
    /**
     * Get List of categories with the features
     */
    public function getCategoryFeatures()
    {
        $categoryWithCharacteristics = category::with('characteristics')->get();

        // get category_characteristics
        return response()->json([
            "is_success" => true,
            'status_code' => 200,
            'messagea' => 'دسته بندی به همراه فیچر',
            "data" => $categoryWithCharacteristics->toArray(),
        ]);
    }

    /**
     * Get the generator configuration
     */
    public function getGeneratorConfig(Request $request, $title)
    {
        // $category = category::with(['category_characteristics.characteristics'])->where('title', $title)->first();

        // Find the category by name
        $category = category::where('title', $title)->first();
        $filters = categoryChar::with('characteristics')->where('category_id', $category->id)->get();
        if (!$category) {
            // Handle the case where the category is not found
            abort(404, 'Category not found');
        }

        // get category_characteristics
        return response()->json([
            "is_success" => true,
            'status_code' => 200,
            "data" => $filters->toArray(),
        ]);
    }

    /**
     * Get the prouct getGeneratorSearch
     */
    public function getGeneratorSearch(Request $request, $title)
    {

        $data = $request->all();
        $validator = Validator::make($data, [
            'province_id' => 'required|numeric',
            'filter' => 'required|array',
            'filter.*.characteristic_id' => 'required|numeric',
            'filter.*.default_value' => 'required|numeric',
            'filter.*.operator' => [
                'nullable',
                Rule::in(['>=']), // Add any other allowed operators
            ],
        ]);

        // if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'is_success' => false,
                'status_code' => 422,
                'data' => [],
            ]);
        }
        $filters = $data['filter'];

        $products = product::query();
        $kVA_id = '';
        // foreach ($filters as $filter) {
        //     $combinedValue = $filter['characteristic_id'] . $filter['default_value'];

        //     $operator = isset($filter['operator']) ? $filter['operator'] : '=';
        //     $kVA_id = isset($filter['operator']) ? $filter['characteristic_id'] : '';
        //     $products->whereHas('product_characteristics', function ($query) use ($combinedValue, $operator) {
        //         $query->whereRaw('CONCAT(characteristic_id, default_value) ' . $operator . ' ?', [$combinedValue]);
        //     });
        // }
        foreach ($filters as $filter) {
            $operator = isset($filter['operator']) ? $filter['operator'] : '=';
            $kVA_id = isset($filter['operator']) ? $filter['characteristic_id'] : '';
            if ($operator == '=') {
                $products->whereHas('product_characteristics', function ($query) use ($filter, $operator) {
                    $query->where('characteristic_id', $filter['characteristic_id'])
                        ->where('default_value', $filter['default_value']);
                });
            } else {
                $products->whereHas('product_characteristics', function ($query) use ($filter, $operator) {
                    $query->where('characteristic_id', $filter['characteristic_id'])
                        ->where('default_value', $operator, (int) $filter['default_value']);
                });
            }
        }
        // Get the filtered products
        if ($data['province_id'] != '0') {
            $filtered_products = $products
                ->where('province_id', $data['province_id'])
                ->with(['product_characteristics' => function ($query) use ($kVA_id) {
                    $query->where('characteristic_id', $kVA_id);
                }])->select('id', 'title', 'category_id', 'media', 'price', 'province_id')->get();
        } else {
            $filtered_products = $products
                ->with(['product_characteristics' => function ($query) use ($kVA_id) {
                    $query->where('characteristic_id', $kVA_id);
                }])->select('id', 'title', 'category_id', 'media', 'price', 'province_id')->get();
        }

        return response()->json([
            "is_success" => true,
            "status_code" => 200,
            "data" => $filtered_products->toArray(),
            "message" => "لیست محصولات مرتبط با درخواست شما"
        ]);
    }

    /**
     * Get all generator prev
     */
    public function store(string $title, int $id)
    {
        // id is kVA_id
        // Find the category by name
        $category = category::where('title', $title)->with(['products' => function ($query) use ($id) {
            $query->select('id', 'title', 'category_id', 'media', 'price', 'province_id')
                ->with(['product_characteristics' => function ($query) use ($id) {
                    $query->where('characteristic_id', $id);
                }]);
        }])->get();
        if ($category) {
            $products = $category->first()->products->toArray();
            return response()->json([
                'is_success' => true,
                'status_code' => 200,
                'data' => $products,
                'message' => 'لیست تمامی محصولات در دسته انتخابی'
            ]);
        } else {
            return response()->json([
                "is_success" => false,
                "status_code" => 400,
                "data" => null,
                "message" => "هیچ محصولی یافت نشد در دسته انتخابی"
            ]);
        }
    }
    public function test_2()
    {
        $characteristicFilters = [
            ['characteristic_id' => '3', 'default_value' => '1'],
            ['characteristic_id' => '4', 'default_value' => '0'],
            // Add more filters as needed
        ];

        $productIds = product_characteristics::query();

        foreach ($characteristicFilters as $filter) {
            $productIds->whereIn('product_id', function ($query) use ($filter) {
                $query->select('product_id')
                    ->from('product_characteristics')
                    ->where('characteristic_id', $filter['characteristic_id'])
                    ->where('default_value', $filter['default_value']);
            });
        }

        $productIds = $productIds->pluck('product_id')->unique()->toArray();

        $products = product::whereIn('id', $productIds)->get();

        // dd(product::with(['product_characteristics'])->get()->toArray());
        // dd(product::with('product_characteristics')->get());
    }
    public function test_ok()
    {
        $filters = [
            ['characteristic_id' => '3', 'default_value' => '1'],
            ['characteristic_id' => '4', 'default_value' => '0'],
            ['characteristic_id' => '6', 'default_value' => '1'],
            // Add more filters as needed
        ];

        $products = product::query();

        foreach ($filters as $filter) {
            $products->whereHas('product_characteristics', function ($query) use ($filter) {
                $query->where('characteristic_id', $filter['characteristic_id'])
                    ->where('default_value', $filter['default_value']);
            });
        }

        // it have and exists multiple filters
        $filtered_products = $products->toSql();
        dd($filtered_products, $products->get()->toArray());
    }

    public function test()
    {
        $filters = [
            ['characteristic_id' => '3', 'default_value' => '1'],
            ['characteristic_id' => '6', 'default_value' => '1'],
            // Add more filters as needed
        ];

        $products = product::query();

        foreach ($filters as $filter) {
            $combinedValue = $filter['characteristic_id'] . $filter['default_value'];

            $products->whereHas('product_characteristics', function ($query) use ($combinedValue) {
                $query->whereRaw('CONCAT(characteristic_id, default_value) = ?', [$combinedValue]);
            });
        }
        // its have concatenated filters
        $filtered_products = $products->toSql();
        dd($filtered_products, $products->get()->toArray());
    }

    public function search()
    {
        $filters = [
            ['characteristic_id' => '3', 'default_value' => '1'],
            ['characteristic_id' => '4', 'default_value' => '0'],
            ['characteristic_id' => '7', 'operator' => '>=', 'default_value' => '100'],
        ];

        $products = product::query();

        foreach ($filters as $filter) {
            $combinedValue = $filter['characteristic_id'] . $filter['default_value'];

            $operator = isset($filter['operator']) ? $filter['operator'] : '=';
            $products->whereHas('product_characteristics', function ($query) use ($combinedValue, $operator) {
                $query->whereRaw('CONCAT(characteristic_id, default_value) ' . $operator . ' ?', [$combinedValue]);
            });
        }

        // Get the filtered products
        // it's get all info for all product
        $filtered_products = $products->with('product_characteristics.characteristics')->get();

        // Display the SQL query for debugging
        $filteredProductsQuery = $products->toSql();
        dd($filteredProductsQuery, $filtered_products->toArray());
    }
}
