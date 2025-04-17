<?php

namespace App\Rest\Controllers;

use App\Models\Product;
use App\Rest\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Rest\Controller as RestController;
use Illuminate\Validation\ValidationException;
use Exception;

class ProductController extends RestController
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        return ProductResource::collection(Product::all());
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'brand' => 'required|string|max:255',
        ]);

        // Create a new product
        $product = Product::create($data);

        // Return the created product as a resource
        return new ProductResource($product);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Return the product as a resource
        return new ProductResource($product);
    }

    /**
     * Update the specified product in storage.
     *
     * @method PATCH
     */
    public function update(Request $request, Product $product)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'brand' => 'required|string|max:255',
        ]);

        // Update the product with validated data
        $product->update($validated);

        // Return the updated product as a resource
        return new ProductResource($product);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy($product)
    {
        // Find the product by ID
        $product = Product::find($product);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Delete the product
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
