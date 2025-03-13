<?php

namespace App\Http\Controllers;

use App\Models\product;
use App\Models\category;
use App\Models\product_characteristics;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // list of all products with pagination and order by created_at desc & with category_id $id ->makeHidden('details')
        // $slug = ['electric_shutters', 'security_door', 'aluminum_window', 'aluminum_door'];
        // $products = category::whereIn('slug', $slug)
        $categories = category::where('is_root', true)
            ->selectSafePrev()
            ->get();
        foreach ($categories as $key => $category) {
            $products = product::where('category_id', $category->id)
                ->selectSafePrev()
                ->where('is_actived', true)
                ->where('is_approved', true)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()->toArray();
            $categories[$key]['shop_category_products'] = $products;
        }

        return response()->json([
            'message' => 'لیست محصولات',
            'is_success' => true,
            'status_code' => 200,
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $user_id, $is_actived = false, $is_approved = false, $is_rejected = false)
    {

        // try and catch to create product
        try {
            $product = new product();
            $product->user_id = $user_id;
            $product->is_actived = $is_actived;
            $product->is_approved = $is_approved;
            $product->is_rejected = $is_rejected;
            $product->title = $request->title;
            $product->category_id = $request->category_id;
            $product->category = $request->category;
            $product->price = $request->price;
            $product->time = $request->time;
            $product->features = json_encode($request->features);
            $product->limited = $request->limited;
            $product->quantity = $request->quantity;
            $product->media = json_encode($request->media);
            $product->details = $request->details;
            $product->province_id = $request->province_id;
            $product->save();
            // check in $request filters is set or not
            if ($request->has('filters') && count($request->filters) > 0) {
                // get from filter => category_id and from filter->characteristics => characteristics.id and characteristics.key
                foreach ($request->filters as $filter) {
                    product_characteristics::create([
                        'product_id' => $product->id,
                        'category_id' => $filter['category_id'],
                        'characteristic_id' => $filter['characteristic_id'],
                        'default_value' => $filter['characteristics']['key'],
                    ]);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'مشکلی در ایجاد محصول به وجود آمده است',
                'is_success' => false,
                'status_code' => 500,
                'data' => $th->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'محصول با موفقیت ایجاد شد',
            'is_success' => true,
            'status_code' => 201,
            'data' => $product,
        ]);
    }

    /**
     * List of all products
     * For: admin
     */
    public function listAllProducts()
    {
        // list of all products with pagination and order by created_at desc & Do not include details column
        $products = product::select('products.id', 'products.title', 'products.media', 'products.is_actived', 'products.is_approved', 'products.is_rejected', 'products.created_at', 'users.name', 'users.family')
            ->join('users', 'products.user_id', '=', 'users.id')
            ->orderBy('products.created_at', 'desc')
            ->paginate(10);
        return response()->json([
            'message' => 'لیست محصولات',
            'is_success' => true,
            'status_code' => 200,
            'data' => $products,
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(product $product)
    {
        // show product with id and get category title with category_id
        $categoryTitle = $product->makeHidden(['updated_at', 'created_at', 'new_data', 'comment', 'is_rejected', 'commission', 'quantity', 'depo'])->category()->first();
        $keywords = $product->keywords;
        $keywords = collect($keywords)->pluck('title')->toArray();
        $seometa = $product->productmetaseos;
        // add category title to product
        $product->category = $categoryTitle->title;
        $product->category_slug = $categoryTitle->slug;

        return response()->json([
            'message' => 'اطلاعات محصول',
            'is_success' => true,
            'status_code' => 200,
            'data' => $product->load('product_characteristics.characteristics')->toArray(),
            'keywords' => implode(', ', $keywords),
            'seometa' => $seometa
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product $product)
    {
        try {
            $product->delete();
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('message.not_success'),
                'is_success' => false,
                'status_code' => 404,
            ]);
        }
        return response()->json([
            'message' => __('message.deleted'),
            'is_success' => true,
            'status_code' => 204,
        ]);
    }

    /**
     * Get products from category for web
     */
    public function categoryProducts(string $slug)
    {
        // use App\Models\product;
        // use App\Models\category;

        try{
            $category = category::where('slug', $slug)->selectSafePrev()->with('keywords')->firstOrFail();
            $products = product::where('category_id', $category->id)->selectSafePrev()
            ->where('is_actived', true)
            ->where('is_approved', true)
            ->latest('created_at')
            ->paginate(50)->toArray();

        }catch(\Exception $e){
            return response()->json(
                [
                    'message' => 'لیست محصول یافت نشد',
                    'is_success' => false,
                    'status_code' => 400,
                    'data' => []
                ]
            );
        }
        return response()->json(
            [
                'message' => 'لیست محصول',
                'is_success' => true,
                'status_code' => 200,
                'category' => $category,
                'products' => $products
            ]
        );

    }
}
