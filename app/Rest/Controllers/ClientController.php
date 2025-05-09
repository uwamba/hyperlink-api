<?php

namespace App\Rest\Controllers;
use App\Rest\Controller as RestController;

use App\Models\Client;
use App\Rest\Resources\ClientResource;
use Illuminate\Http\Request;


class ClientController extends RestController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Return a collection of clients, transformed by the ClientResource
        //return ClientResource::collection(Client::all());
        return ClientResource::collection(Client::all());
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // This method is generally used for displaying a form to create a new resource.
        // It's not used in an API context, so we'll leave it empty.
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $client = Client::create($data);
        return new ClientResource($client);


    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        // Return the client, transformed by the ClientResource
        //return new ClientResource($client);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        // This method is generally used for displaying a form to edit a resource.
        // It's not used in an API context, so we'll leave it empty.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        // Validate the request data
        $validated = $request->validate([
           'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            // Add other necessary fields and validation rules
        ]);

        // Update the client with the validated data
        $client->update($validated);

        // Return the updated client, transformed by the ClientResource
        return new ClientResource($client);
    }

    /**
     * Remove the specified resource from storage.
     */

     public function destroy($id)
     {
         $invoice = Client::findOrFail($id);
         $invoice->delete();
 
         return response()->json(['message' => 'Client deleted successfully'], 200);
     }
 
    
}
