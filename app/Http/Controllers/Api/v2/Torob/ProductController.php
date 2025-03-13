<?php

namespace App\Http\Controllers\Api\v2\Torob;

use App\Http\Controllers\Controller;
use App\Models\product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = product::select('id', 'title', 'price', 'is_actived', 'is_approved')
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(100)->toArray();


        $transformedProducts = [];

        foreach ($products['data'] as $product) {
            $transformedProducts[] = [
                'product_id'   => 'om-' . (string) $product['id'],
                'page_url'     => 'https://omdico.ir/fa/om-' . $product['id'] . '/' . urlencode(Str::replace(' ', '_', Str::lower($product['title']))),
                'price'        => $product['price'],
                'availability' => $product['is_actived'] ? 'instock' : 'outofstock',
            ];
        }
        $products['data'] = $transformedProducts;

        return $products;
    }

    public function products()
    {
        $products = product::select('id', 'title', 'is_actived', 'is_approved', 'updated_at', 'media')
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(1000)->toArray();


        $transformedProducts = [];

        foreach ($products['data'] as $product) {
            $title = $product['title'] . ' - کد om-' . $product['id'];
            $transformedProducts[] = [
                'loc'     => 'https://omdico.ir/fa/om-' . $product['id'] . '/' . urlencode(Str::replace(' ', '_', Str::lower($product['title']))),
                'lastmod' => $product['updated_at'],
                'changefreq' => 'monthly',
                'priority' => '0.5',
                'images' => array_map(function ($item) use ($title) {
                    return [
                        'loc' => $item->photoUrl,
                        'title' => $title,
                    ];
                }, $product['media']->image),
                'alternatives' => [
                    [
                        'hreflang' => 'fa-ir',
                        'href' => 'https://omdico.ir/fa/om-' . $product['id'] . '/' . urlencode(Str::replace(' ', '_', Str::lower($product['title'])))
                    ]
                ],
            ];
        }
        $products['data'] = $transformedProducts;

        return response()->json($products['data']);
    }

    public function fakerAction()
    {
        $faker = \Faker\Factory::create('fa_IR');
        $data = [];
        for ($i = 0; $i < 2000; $i++) {
          $data[] = [
        'loc' => 'https://omdico.ir/fa/om-' . $i . '/' . $faker->slug,
        'lastmod' => $faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
        'changefreq' => 'monthly',
        'priority' => '0.8',
          ];
        }
        return response()->json($data);
    }
}
